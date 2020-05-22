<?php

namespace App\Http\Controllers\Front;

use App\Helper\HashHelper;
use App\Models\Order;
use App\Models\ShippingFee;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Common\CustomController;
use App\Models\PaymentMethod;
use ErrorException;

class OrderController extends CustomController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = $this->orderSearch();

        $orders = $this->paginate($orders, 5)->withPath(route('order.index'))
                    ->appends(Request::capture()->except(['page', '_token']));

        return view('front.user.order', [
            'orders' => $orders,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //判斷user是否還有未付款的訂單
        $user = Auth::guard('web')->user();

        $unpaidOrder = $user->orders()->where('payment_status', Order::PAYMENT_STATUS_NOT_PAID)->first();

        //如果有為付款的訂單則跳轉至該訂單頁面
        if ($unpaidOrder) {

            $errors = new MessageBag(['請先處理未付款的訂單']);

            return redirect(route('order.show', ['order' => $unpaidOrder->id]))->withErrors($errors);

        }

        $cart = CartController::getCart();

        if (!$cart || $cart->getContent()->isEmpty()) {

            $errors = new MessageBag(['目前無商品於購物車内']);

            return redirect(route('shop.index'))->withErrors($errors);

        }

        $shippingFeeList = ShippingFee::getShippingFeeList($cart->getSubTotal());

        $paymentMethodList = PaymentMethod::where('status', PaymentMethod::STATUS_ON)->get();

        $timestamp = time();

        return view('front.shop.checkout', [
            'cart' => $cart,
            'shippingFeeList' => $shippingFeeList,
            'paymentMethodList' => $paymentMethodList,
            'user' => $user,
            'timestamp' => $timestamp,
            'token' => HashHelper::makeApiToken($user->id, $timestamp)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        $validatedData = Request::validate([
            'last_name' => ['required', 'string'],
            'first_name' => ['required', 'string'],
            'phone_number' => ['required', 'regex:/^09[0-9]{8}$/'],
            'city'  => ['required', 'string', Rule::in(array_keys(Config::get('custom.city_list')))],
            'district' => ['required', 'string', Rule::in(Config::get('custom.city_list')[Request::get('city')])],
            'address' => ['required', 'string'],
            'save-info' => ['nullable', 'boolean'],
            'shipping-type' => ['required', Rule::in(array_keys(ShippingFee::getTypeList()))],
            'payment_method' => ['required', 'exists:payment_methods,id']
        ]);

        $user = Auth::guard('web')->user();

        if (isset($validatedData['save-info']) && $validatedData['save-info']) {

            if (!$user) {

                $errors = new MessageBag(['無此用戶']);

                return redirect(route('login.showLoginForm'))->withErrors($errors);

            }

            //記錄user info
            $user->fill($validatedData);

            $user->save();

        }

        //確認目前支付方式是否可用
        $selectedPaymentMethod = PaymentMethod::where([
                                        ['id', '=', $validatedData['payment_method']],
                                        ['status', '=', PaymentMethod::STATUS_ON]
                                    ])
                                    ->first();

        if (!$selectedPaymentMethod) {

            $errors = new MessageBag(['您所選擇的支付方式目前無法使用']);

            return redirect(route('order.create'))->withErrors($errors);

        }

        $cart = CartController::getCart();

        //套用運費
        $shippingFee = ShippingFee::getShippingFeeList($cart->getSubTotal(), $validatedData['shipping-type']);

        if (!empty($shippingFee)) {

            $shippingFeeCondition = new \Darryldecode\Cart\CartCondition(array(
                'name' => 'shipping-fee',
                'type' => 'shipping',
                'target' => 'total',
                'value' => $shippingFee[$validatedData['shipping-type']],
            ));

            $cart->condition($shippingFeeCondition);

        }

        //開始創建訂單
        $productId = NULL;

        try {

            $order = NULL;

            DB::transaction(function () use (&$cart, &$productId, $validatedData, &$order, $selectedPaymentMethod, $user) {

                foreach ($cart->getContent() as $item) {

                    $stockId = $item->id;
                    $quantity = $item->quantity;
                    $productId = $item->attributes->product['id'];

                    //先扣掉商品庫存
                    $stockQuery = DB::update('UPDATE stocks SET quantity = quantity - ? WHERE id = ? AND quantity >= ?', [$quantity, $stockId, $quantity]);

                    if ($stockQuery !== 1) {

                        //去除庫存不夠的商品
                        $cart->remove($stockId);

                        throw new \ErrorException(sprintf('商品 %s (%s) 庫存不足, 請調整購買數量', $item->name, $item->attributes->stock['description']));

                    }
                }

                //成功後 清空CartStorage的記錄

                $cartStorageQuery = DB::delete('DELETE FROM cart_storage WHERE user_id = ?', [$user->id]);

                if ($cartStorageQuery !== 1) {

                    throw new \ErrorException('刪除購物車記錄失敗');

                }

                $cartTotal = round($cart->getTotal()); //此行一定要在parsedRawValue的前面 不然會得不到parsedRawValue

                $shippingFee = $cart->getConditions()->get('shipping-fee')->parsedRawValue ?? 0;

                $couponDiscount = round($cart->getConditions()->get('coupon')->parsedRawValue) ?? 0;

                //創建訂單
                $order = new Order([
                    'order_number' => sprintf('order_%d%s', intval(microtime(TRUE)*10000), substr(encrypt(sprintf('%d_$s_%d', $user->id, $user->name, time())), -10, 10)),
                    'user_id' => $user->id,
                    'total' => $cartTotal,
                    'shipping_fee' => $shippingFee,
                    'coupon_discount' => $couponDiscount,
                    'order_status' => ORDER::ORDER_STATUS_PROCESSING,
                    'payment_status' => ORDER::PAYMENT_STATUS_NOT_PAID,
                    'city' => $validatedData['city'],
                    'district' => $validatedData['district'],
                    'address' => $validatedData['address'],
                    'payment_method' => $selectedPaymentMethod->id
                ]);

                if (!$order->save()) {

                    throw new \ErrorException('創建訂單失敗');

                }

                //記錄訂單的商品
                $orderItemTable = DB::table('order_stock');

                $itemsData = [];

                foreach ($cart->getContent() as $item) {

                    $itemsData[] = ['order_id' => $order->id, 'stock_id' => $item->id, 'quantity' => $item->quantity];

                }

                if (!$orderItemTable->insert($itemsData)) {

                    throw new \ErrorException('建立訂單商品記錄失敗');

                }

                //扣掉coupon的使用次數
                if ($couponDiscount > 0) {

                    $couponId = $cart->getConditions()->get('coupon')->getAttributes()['couponId'];

                    $couponUpdateQuery = DB::update('UPDATE coupons SET remain = remain - 1 WHERE id = ? AND remain > 0', [$couponId]);

                    if ($couponUpdateQuery !== 1) {

                        throw new \ErrorException('優惠券的使用次數已用盡');

                    }

                }

                //清空購物車
                $cart->clear();

                //去掉運費 及 優惠券
                $cart->removeCartCondition('shipping-fee');
                $cart->removeCartCondition('coupon');

            }, 5);

        } catch (\Exception $e) {

            //去掉運費 及 優惠券
            $cart->removeCartCondition('shipping-fee');
            $cart->removeCartCondition('coupon');

            $errors = new MessageBag([$e->getMessage()]);

            return redirect(route('shop.show', ['product' => $productId]))->withErrors($errors);

        }

        //訂單建立完之後 前往支付頁面
        $paymentMethod = resolve($selectedPaymentMethod->name);

        try {

            $result = $paymentMethod->paymentRequest($order);

            //跳轉到第三方的支付頁面
            return redirect($result);

        } catch (\Exception $e) {

            $errors = new MessageBag([$e->getMessage()]);

            return redirect(route('order.show', ['order' => $order->id]))
                        ->withErrors($errors);

        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show($id)
    {
        $order = Order::find($id);

        if (!$order) {

            $errors = new MessageBag(['查無此訂單']);

            return redirect(route('order.index'))->withErrors($errors);

        }

        $stocks = $order->stocks()->withPivot('quantity')->get();

        return view('front.user.order_detail', [
            'order' => $order,
            'stocks' => $stocks,
        ]);
    }

    /**
     * rollback已被取消的訂單
     *
     */
    protected function rollback(Order $order)
    {
        try {

            DB::transaction(function () use ($order) {

                //返還訂單内的商品
                foreach ($order->stocks as $stock) {

                    $updateStockQuery = DB::update('UPDATE stocks SET quantity = quantity + ? WHERE id = ?', [$stock->pivot->quantity, $stock->id]);

                    if ($updateStockQuery !== 1) {

                        throw new ErrorException('返還stocks失敗');

                    }

                }

                $order->order_status = Order::ORDER_STATUS_CANCEL;

                if (!$order->save()) {

                    throw new ErrorException('訂單狀態更新失敗');

                }

            });

            return TRUE;

        } catch (\Exception $e) {

            return FALSE;

        }

    }

    protected function orderSearch()
    {
        $validatedData = Request::validate([
            'order_number' => ['nullable', 'string'],
            'order_status' => ['nullable', 'integer'],
            'date_start'   => ['nullable', 'date_format:Y-m-d'],
            'date_end'     => ['nullable', 'date_format:Y-m-d'],
            'order_by'     => ['nullable', 'string', Rule::in(Order::getOrderByList())],
            'is_asc'       => ['nullable', 'boolean']
        ]);

        $whereData = [];

        if (isset($validatedData['order_number'])) {

            $whereData[] = ['order_number', 'like', sprintf('%%%s%%', $validatedData['order_number'])];

        }

        if (isset($validatedData['order_status'])) {

            $whereData[] = ['order_status', '=', $validatedData['order_status']];

        }

        //排序
        $orderBy = $validatedData['order_by'] ?? 'created_at';

        $isAsc = $validatedData['is_asc'] ?? 0;

        $query = Auth::guard('web')->user()
                    ->orders()
                    ->whereBetween('created_at', [$validatedData['date_start'] ?? date('Y-m-d 00:00:00', (time()-86400*30)), $validatedData['date_end'] ?? date('Y-m-d 23:59:59')])
                    ->orderBy($orderBy, $isAsc ? 'asc' : 'desc');

        if (!empty($whereData)) {

            $query->where($whereData);

        }

        $orders = $query->get();

        Request::flashOnly($validatedData);

        return $orders;
    }

    /**
     * 發起第三方確認付款請求
     *
     */
    public function thirdPartyConfirm(Order $order, $paymentMethod)
    {
        if (!$order) {

            $errors = new MessageBag(['此訂單不存在']);

            return redirect(route('order.index'))->withErrors($errors);

        }

        //儲存資料
        if (!empty(Request::all())) {

            $order->data = json_encode(array_merge(json_decode($order->data, TRUE) ?? [], Request::all()));

            $order->save();

        }

        $paymentMethod = resolve($paymentMethod);

        $result = $paymentMethod->confirmRequest($order);

        return redirect(route('order.show', ['order' => $result['orderId']]));

    }

    /**
     * 發起第三方取消訂單的請求
     *
     */
    public function thirdPartyCancel(Order $order, $paymentMethod)
    {
        try {

            // TODO: 取消訂單不應該跟第三方支付綁在一起處理

            //確認訂單狀態
            if ($order->payment_status !== ORDER::PAYMENT_STATUS_NOT_PAID) {

                throw new ErrorException('只有未付款的訂單才可以被取消, 已付款的訂單請以退款的方法處理');

            }

            $paymentMethod = resolve($paymentMethod);

            $result = $paymentMethod->cancelRequest($order);



            if (!$result['isSuccess'] || !$this->rollback($order)) {

                throw new ErrorException('取消訂單失敗');

            }

            return redirect(route('order.show', ['order' => $order->id]))->with('status', '取消訂單成功');

        } catch (\Exception $e) {

            $errors = new MessageBag([$e->getMessage()]);

            return redirect(route('order.show', ['order' => $order->id]))->withErrors($errors);

        }

    }

    public function thirdPartyRefund(Order $order, $paymentMethod)
    {
        $paymentStatusList = [
            Order::PAYMENT_STATUS_HAS_PAID_AND_AUTHORIZED,
            Order::PAYMENT_STATUS_HAS_PAID_NOT_AUTHORIZED,
            Order::PAYMENT_STATUS_HAS_PAID_AND_CONFIRMED
        ];

        try {

            //確認訂單狀態
            if (!in_array($order->payment_status, $paymentStatusList)) {

                throw new ErrorException('只有狀態為已付款的訂單才可以申請退款');

            }

            $paymentMethod = resolve($paymentMethod);

            $result = $paymentMethod->refundRequest($order);

            if (!$result['isSuccess'] || !$this->rollback($order)) {

                throw new ErrorException('退款失敗, 請稍後再做嘗試');

            }

            return redirect(route('order.show', ['order' => $order->id]))->with('status', '退款成功');

        } catch (\Exception $e) {

            $errors = new MessageBag([$e->getMessage()]);

            return redirect(route('order.show', ['order' => $order->id]))->withErrors($errors);

        }
    }

}
