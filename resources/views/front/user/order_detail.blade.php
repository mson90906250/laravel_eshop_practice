@php

    use App\Models\Order;

@endphp

@extends('front.layouts.app')

@section('content')

    @include('front.includes.titlebox', [
        'title' => '我的訂單'
    ])

    <!-- Start Order  -->
    <div class="my-account-box-main">

        <div class="container">

            @include('front.includes.alert')

            @include('front.includes.breadcrumb', [
                'data' => [
                    '我的帳號' => route('user.index'),
                    '我的訂單'  => route('order.index'),
                    '訂單内容' => ''
                ]
            ])

            <div style="padding:0px 20px">

                <div class="order-detail">

                    <div class="row">

                        <div class="col-6">

                            <ul>

                                <li>{{ sprintf('訂單號: %s', $order->order_number) }}</li>

                                <li>{{ sprintf('訂單狀態: %s', Arr::get(Order::getOrderStatusList(), $order->order_status)) }}</li>

                                <li>{{ sprintf('購買日期: %s', $order->created_at) }}</li>

                                <li>{{ sprintf('收件地址 %s', $order->full_address) }}</li>

                                <li>{{ sprintf('貨運狀態: %s', '???') }}</li>

                            </ul>

                        </div>

                        <div class="col-6">

                            <ul>

                                <li>{{ sprintf('付款狀態: %s', Order::getPaymentStatusList()[$order->payment_status]) }}</li>

                                <li>{{ sprintf('總金額 %d', $order->total) }}</li>

                                <li>{{ sprintf('運費 %d', $order->shipping_fee) }}</li>

                                <li>{{ sprintf('折抵金額 %d', $order->coupon_discount) }}</li>

                            </ul>

                            @if ($order->payment_status === Order::PAYMENT_STATUS_NOT_PAID
                                    && $order->order_status !== Order::ORDER_STATUS_CANCEL
                                    && $order->order_status !== Order::ORDER_STATUS_COMPLETE)

                                <div class="mt-3">

                                    <a href="{{ route('order.thirdPartyCancel', ['order' => $order->id, 'paymentMethod' => $order->paymentMethod->name]) }}"
                                        class="btn btn-sm btn-danger">取消訂單</a>

                                </div>

                            @elseif ($order->payment_status !== Order::PAYMENT_STATUS_NOT_PAID
                                    && $order->payment_status !== Order::PAYMENT_STATUS_REFUNDED)

                                <div class="mt-3">

                                    <a href="{{ route('order.thirdPartyRefund', ['order' => $order->id, 'paymentMethod' => $order->paymentMethod->name]) }}"
                                        class="btn btn-sm btn-danger">取消訂單並退款</a>

                                </div>

                            @endif

                        </div>


                    </div>

                </div>

                <table class="table table-hover mt-5">

                    <thead>

                        <tr>

                            <th>商品名</th>

                            <th>購買數量</th>

                            <th>價格</th>

                            <th>小計</th>

                            <th></th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach ($stocks as $stock)

                            <tr>

                                <th>

                                    {{ $stock->product->name }}

                                    @if ($stock->attribute)

                                        <p style="font-size:10px; color:#aaa">{{ $stock->attribute }}</p>

                                    @endif

                                </th>

                                <td>{{ $stock->pivot->quantity }}</td>

                                <td>{{ $stock->price }}</td>

                                <td>{{ intval($stock->price * $stock->pivot->quantity) }}</td>

                                <td>

                                    <a href="{{ route('shop.show', ['product' => $stock->product->id]) }}" class="btn btn-info">商品頁面</a>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <!-- End Order -->


@endsection
