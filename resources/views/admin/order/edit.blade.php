@php

    use App\Models\Order;

@endphp

@extends('admin.layouts.app')

@section('content')

    @include('admin.includes.alert')

    <!-- DataTales Example -->
    <div class="card shadow mb-4">

        <div class="card-header py-3">

            <h1 class="m-0 font-weight-bold text-primary">修改訂單狀態</h1>

            @include('admin.includes.breadcrumb', [
                'data' => [
                    '訂單列表' => route('admin.order.index'),
                    sprintf('訂單號: %s', $order->order_number) => route('admin.order.show', ['order' => $order->id]),
                    '修改訂單狀態' => ''
                ]
            ])

        </div>

        <div class="card-body ml-5">

            {!! Form::open(['url' => route('admin.order.update', ['order' => $order->id]), 'method' => 'POST']) !!}

                @method('PUT')

                <div class="row">

                    <div class="col-7">

                        {!! Form::bsSelect('訂單狀態', 'order_status', request()->input('order_status') ?? $order->order_status, Order::getOrderStatusList(), '', 'vertical') !!}

                    </div>

                    <div class="col-7 mt-3">

                         {!! Form::bsSelect('支付狀態', 'payment_status', request()->input('payment_status') ?? $order->payment_status, Order::getPaymentStatusList(), '', 'vertical') !!}

                    </div>

                    <div class="col-7 mt-3">

                        {!! Form::button('修改', ['type' => 'submit', 'class' => 'btn btn-primary']) !!}

                   </div>

                </div>

            {!! Form::close() !!}

        </div>

    </div>

@endsection
