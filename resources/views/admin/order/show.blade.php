@php

    use App\Models\Order;
    use App\Helper\DetailView;

@endphp

@extends('admin.layouts.app')

@section('styles')

    @parent

    {{-- date picker --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.0-alpha14/css/tempusdominus-bootstrap-4.min.css" />

    <link href="{{ asset('css/admin/dataTables.bootstrap4.min.css') }}"
        rel="stylesheet">

@endsection

@section('content')

    @include('admin.includes.alert')

    <!-- DataTales Example -->
    <div class="card shadow mb-4">

        <div class="card-header py-3">

            <h1 class="m-0 font-weight-bold text-primary">{{ sprintf('訂單號: %s', $order->order_number) }}</h1>

            @include('admin.includes.breadcrumb', [
                'data' => [
                    '訂單列表' => route('admin.order.index'),
                    sprintf('訂單號: %s', $order->order_number) => ''
                ]
            ])

        </div>

        <div class="card-body">

            {{-- button --}}
            @if ($order->order_status !== Order::ORDER_STATUS_CANCEL && $order->order_status !== Order::ORDER_STATUS_COMPLETE)

                <a href="{{ route('admin.order.cancel', ['order' => $order->id]) }}" class="btn btn-danger">取消訂單</a>

            @endif

            <a href="{{ route('admin.order.edit', ['order' => $order->id]) }}" class="btn btn-warning">修改訂單狀態</a>

            {{-- table --}}
            <div class="table-responsive">

                {!!
                    DetailView::get($order, [
                        'table' => [
                            'class' => 'table table-bordered mt-2',
                            'width' => '100%',
                            'cellspacing' => 0
                        ],
                        'columns' => [
                            'order_number',
                            [
                                'attribute' => 'nickname',
                                'label' => '用戶名稱',
                                'value' => function ($attribute, $value, Order $model) {

                                    return sprintf('<a href="%s">%s</a>', route('admin.user.show', ['user' => $model->user->id]), $model->user->nickname);

                                }
                            ],
                            'order_status_label',
                            'payment_status_label',
                            'payment_method_label',
                            'full_address',
                            'total',
                            'shipping_fee',
                            'coupon_discount',
                            'data',
                            'created_at',
                            'updated_at',
                        ]
                    ])
                !!}

            </div>

            {{-- order item table --}}
            <h6 class="mt-3">訂單商品</h6>

            <div class="table-responsive" style="max-height: 600px; overflow-y:scroll">

                <table class="table table-bordered mt-2">

                    <thead>

                        <th>圖片</th>

                        <th>產品名稱</th>

                        <th>規格</th>

                        <th>價格</th>

                        <th>數量</th>

                    </thead>

                    <tbody style="max-height: 600px">

                        @foreach ($order->stocks()->withPivot('quantity')->get() as $stock)

                            <tr>

                                <td>

                                    <div style="display:flex; justify-content:center">

                                        <img style="max-width: 50px" src="{{ asset($stock->image_url) }}" alt="">

                                    </div>

                                </td>

                                <td><a href="#">{{ $stock->product->name }}</a></td>

                                <td>{{ $stock->attribute }}</td>

                                <td>{{ $stock->price }}</td>

                                <td>{{ $stock->pivot->quantity }}</td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>



        </div>

    </div>

@endsection
