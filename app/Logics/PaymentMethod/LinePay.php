<?php

namespace App\Logics\PaymentMethod;

use App\Models\Order;
use GuzzleHttp\Client;
use App\Logics\PaymentMethod\PaymentInterface;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\MessageBag;

class LinePay implements PaymentInterface {

    // TODO: 使用setting表 來取得
    CONST CHANNEL_ID = '1654043040';
    CONST CHANNEL_SECRET = '36ee4c05767275d40e1ae0bd89692412';
    CONST BASE_URL_SANDBOX = 'https://sandbox-api-pay.line.me';

    CONST REDIS_LINEPAY_CONFIRM_LIST = 'REDIS_LINEPAY_CONFIRM_LIST';  //用來確認之前未被授權的訂單是否已經授權
    CONST REDIS_LINEPAY_TRANSACTIONID_LIST = 'REDIS_LINEPAY_TRANSACTIONID_LIST';
    CONST REDIS_LINEPAY_CONFIRM_FAIL_COUNT_LIST = 'REDIS_LINEPAY_CONFIRM_FAIL_COUNT_LIST'; //用來記錄請款失敗次數 5次就將該訂單取消或退款

    protected $nonce;
    protected $client;
    protected $signature;
    protected $headers;

    public function __construct()
    {
        $this->nonce = sprintf('%d-%s', hrtime(TRUE), uniqid());

        $this->client = new Client([
            'base_uri' => self::BASE_URL_SANDBOX
        ]);

    }

    public function paymentRequest(Order $order)
    {
        $uri = '/v3/payments/request';

        $orderStocks = $order->stocks;

        $products = [];

        foreach ($orderStocks as $stock) {

            $products[] = [
                'name' => sprintf('%s (%s)', $stock->product->name, $stock->attribute),
                'quantity' => $stock->pivot->quantity,
                'price' => $stock->price,
            ];

        }

        //運費
        if ($order->shipping_fee) {

            $products[] = [
                'name' => '運費',
                'quantity' => 1,
                'price' => $order->shipping_fee,
            ];

        }

        //優惠 (要用負數)
        if ($order->coupon_discount) {

            $products[] = [
                'name' => '優惠券折扣',
                'quantity' => 1,
                'price' => -1 * abs($order->coupon_discount),
            ];

        }

        $form = [
            'amount' => $order->total,
            'currency' => 'TWD',
            'orderId' => $order->order_number,
            'packages' => [
                [
                    'id' => $order->order_number,
                    'amount' => $order->total,
                    'name' => 'test',
                    'products' => $products
                ],
            ],
            'redirectUrls' => [
                'confirmUrl' => route('order.thirdPartyConfirm', ['order' => $order->id, 'paymentMethod' => 'LinePay']),
                'cancelUrl' => route('index.welcome'),
            ],
        ];

        $signature = $this->getSignature($uri, $form, 'POST');

        $headers = $this->getHeaders($signature);

        //發送請求
        $response = $this->client->request('POST', $uri, ['headers' => $headers, 'body' => json_encode($form)]);

        if ($response->getStatusCode() !== 200) {

            throw new \ErrorException('LinePay發送支付請求失敗') ;

        }

        $result = json_decode($response->getBody()->getContents(), TRUE);

        if ($result['returnCode'] !== '0000') {

            // TODO: 這類型的錯誤應該要做一個log表
            throw new \ErrorException(sprintf('Code: %s, Message: %s', $result['returnCode'], $result['returnMessage']));

        }

        //跳轉到LinePay的支付頁面
        return $result['info']['paymentUrl']['web'];

    }

    public function confirmRequest(Order $order)
    {
        $data = json_decode($order->data, TRUE);

        $paymentStatus = $this->checkPaymentStatus($data['transactionId']);

        switch ($paymentStatus['returnCode']) {
            case '9000':
            case '1105':
            case '1104':
            case '0000':

                //尚未授權 則存進redis裡
                if (!Redis::hexists(self::REDIS_LINEPAY_CONFIRM_LIST, $data['transactionId'])) {

                    Redis::hmset(self::REDIS_LINEPAY_CONFIRM_LIST, [$data['transactionId'] => $order->id]);

                    Redis::lpush(self::REDIS_LINEPAY_TRANSACTIONID_LIST, $data['transactionId']);

                }

                if ($order->payment_status !== Order::PAYMENT_STATUS_HAS_PAID_NOT_AUTHORIZED) {

                    $order->payment_status = Order::PAYMENT_STATUS_HAS_PAID_NOT_AUTHORIZED;

                    $order->save();

                }

                return $this->getReturnData($order->id, FALSE);

                break;

            case '0121':
            case '0122':

                // TODO: 取消訂單

                break;

            case '0110':

                $uri = sprintf('/v3/payments/%s/confirm', $data['transactionId']);

                //執行請款確認
                $form = [
                    'amount' => $order->total,
                    'currency' => 'TWD'
                ];

                $signature = $this->getSignature($uri, $form, 'POST');

                $headers = $this->getHeaders($signature);

                $response = $this->client->request('POST', $uri, ['headers' => $headers, 'body' => json_encode($form)]);

                $result = json_decode($response->getBody()->getContents(), TRUE);

                if ($result['returnCode'] === '0000') {

                    //請款成功 返回訂單頁面
                    $order->payment_status = Order::PAYMENT_STATUS_HAS_PAID_AND_CONFIRMED;

                    $order->save();

                    return $this->getReturnData($order->id, TRUE);

                } else {

                    $order->payment_status = Order::PAYMENT_STATUS_HAS_PAID_AND_AUTHORIZED;

                    $order->save();

                    return $this->getReturnData($order->id, FALSE, $result);

                }

                break;

            case '0123':

                return $this->getReturnData($order->id, TRUE);

                break;
        }



    }

    public function cancelRequest(Order $order)
    {
        $data = json_decode($order->data, TRUE);

        if (empty($data)) {

            return $this->getReturnData($order->id, FALSE, ['message' => '缺少LinePay所需的資料']);

        }

        $uri = sprintf('/v3/payments/authorizations/%s/void', $data['transactionId']);

        $signature = $this->getSignature($uri, [], 'POST');

        $headers = $this->getHeaders($signature);

        $response = $this->client->request('POST', $uri, ['headers' => $headers, 'body' => json_encode([])]);

        $result = json_decode($response->getBody()->getContents(), TRUE);

        dd($result);

        //目前cancel api 在測試版中好像有問題先跳過

        return $this->getReturnData($order->id, TRUE, $result);

    }

    public function refundRequest(Order $order) {

        $data = json_decode($order->data, TRUE);

        if (empty($data)) {

            return $this->getReturnData($order->id, FALSE, ['message' => '缺少LinePay所需的資料']);

        }

        $uri = sprintf('/v3/payments/%s/refund', $data['transactionId']);

        $form = [
            'refundAmount' => $order->total,
        ];

        $signature = $this->getSignature($uri, $form, 'POST');

        $headers = $this->getHeaders($signature);

        $response = $this->client->request('POST', $uri, ['headers' => $headers, 'body' => json_encode($form)]);

        $result = json_decode($response->getBody()->getContents(), TRUE);

        if ($result['returnCode'] === '0000' || $result['returnCode'] === '1165') {

            $order->payment_status = Order::PAYMENT_STATUS_REFUNDED;

            $order->order_status = Order::ORDER_STATUS_CANCEL;

            $order->save();

            return $this->getReturnData($order->id, TRUE, $result);

        } else {

            return $this->getReturnData($order->id, FALSE, $result);

        }

    }

    /**
     * 確認支付訂單狀態
     *
     */
    public function checkPaymentStatus($transactionId)
    {
        $returnMessageList = [
            '0000' => '授權尚未完成',
            '0110' => '授權完成 - 現在可以呼叫Confirm API',
            '0121' => '該交易已被用戶取消，或者超時取消（20秒）- 交易已經結束了',
            '0122' => '付款失敗 - 交易已經結束了',
            '0123' => '付款成功 - 交易已經結束了',
            '1104' => '此商家不存在',
            '1105' => '此商家處於無法使用LINE Pay的狀態',
            '9000' => '內部錯誤',
        ];

        $uri = sprintf('/v3/payments/requests/%s/check', $transactionId);

        $signature = $this->getSignature($uri, '', 'GET');

        $headers = $this->getHeaders($signature);

        $response = $this->client->request('GET', $uri, ['headers' => $headers]);

        $result = json_decode($response->getBody()->getContents(), TRUE);

        return $result;
    }

    protected function getSignature($uri, $params = NULL, $type = 'GET')
    {
        switch ($type) {

            case 'POST':

                $this->signature = base64_encode(hash_hmac('sha256', sprintf('%s%s%s%s', self::CHANNEL_SECRET, $uri, json_encode($params), $this->nonce), self::CHANNEL_SECRET, TRUE));

                break;

            case 'GET';

                $this->signature = base64_encode(hash_hmac('sha256', sprintf('%s%s%s%s', self::CHANNEL_SECRET, $uri, $params, $this->nonce), self::CHANNEL_SECRET, TRUE));

                break;
        }

        return $this->signature;
    }

    protected function getHeaders($signature)
    {
        $this->headers = [
            'Content-Type' => 'application/json',
            'X-LINE-ChannelId' => self::CHANNEL_ID,
            'X-LINE-Authorization-Nonce' => $this->nonce,
            'X-LINE-Authorization' => $signature,
        ];

        return $this->headers;
    }

    public function getReturnData($orderId, bool $isSuccess = TRUE, $data = [])
    {
        return ['orderId' => $orderId, 'isSuccess' => $isSuccess, 'data' => $data];
    }

}
