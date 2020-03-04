<?php

namespace App\Cron\Workers;

use Workerman\Timer;
use App\Models\Order;
use Workerman\Worker;
use App\Logics\PaymentMethod\LinePay;
use Illuminate\Support\Facades\Redis;

// 處理未請款的訂單
$confirmLinePayWorker = new Worker();

$confirmLinePayWorker->name = 'ConfirmLinePayWorder';

$confirmLinePayWorker->onWorkerStart = function () {

    Timer::add(1, function () {

        echo sprintf('開始處理LinePay未請款的訂單%s', PHP_EOL);

        $transactionId = Redis::rpop(LinePay::REDIS_LINEPAY_TRANSACTIONID_LIST);

        if (!$transactionId) {

            echo sprintf('目前LinePay沒有未請款的訂單要處理%s', PHP_EOL);

            sleep(300);

            return;

        }

        $orderId = Redis::hget(LinePay::REDIS_LINEPAY_CONFIRM_LIST, $transactionId);

        $order = Order::find($orderId);

        if (!$order) {

            return;

        }

        $orderNumber = $order->order_number;

        $paymentMethod = new LinePay();

        $result = $paymentMethod->confirmRequest($order);

        if (!$result['isSuccess']) {

            if (Redis::hget(LinePay::REDIS_LINEPAY_CONFIRM_FAIL_COUNT_LIST, $transactionId) >= 5) {

                // TODO: 訂單取消或退款
                Redis::hDel(LinePay::REDIS_LINEPAY_CONFIRM_FAIL_COUNT_LIST, $transactionId);

                Redis::hDel(LinePay::REDIS_LINEPAY_CONFIRM_LIST, $transactionId);

                echo sprintf('LinePay訂單已被取消或退款: %s 失敗理由:%s%s', $orderNumber, $result['returnMessage'], PHP_EOL);

                return;

            }

            Redis::lpush(LinePay::REDIS_LINEPAY_TRANSACTIONID_LIST, $transactionId);

            //記錄請款失敗次數
            $failCount = Redis::hIncrBy(LinePay::REDIS_LINEPAY_CONFIRM_FAIL_COUNT_LIST, $transactionId, 1);

            echo sprintf('LinePay訂單請款失敗: %s ,第%d次%s', $orderNumber, $failCount, PHP_EOL);

        } else {

            Redis::hdel(LinePay::REDIS_LINEPAY_CONFIRM_LIST, $transactionId);

            echo sprintf('完成LinePay訂單: %s%s', $orderNumber, PHP_EOL);

        }


    });

};
