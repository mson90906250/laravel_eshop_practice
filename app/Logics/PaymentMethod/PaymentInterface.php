<?php

namespace App\Logics\PaymentMethod;

use App\Models\Order;
use Illuminate\Support\Facades\Request;

interface PaymentInterface {

    /**
     * 發起支付請求到第三方支付
     *
     */
    public function paymentRequest(Order $order);


    /**
     * 發起確認付款請求
     *
     */
    public function confirmRequest(Order $order);

    /**
     * 發起取消請求
     *
     */
    public function cancelRequest(Order $order);

    /**
     * 發起退款請求
     *
     */
    public function refundRequest(Order $order);

    public function getReturnData($orderId, bool $isSuccess = TRUE, $data = []);


}
