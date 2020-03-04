@php

    use App\Models\Order;

@endphp

@section('scripts')

    @parent

    <script>

        $('#datetimepicker1').datetimepicker({
            format: 'YYYY-MM-DD',
        });

        $('#datetimepicker2').datetimepicker({
            format: 'YYYY-MM-DD',
        });

    </script>

    <script>

        var orderAttribute = "{{ request()->input('order_by') }}";

        var isAsc = "{{ request()->input('is_asc') }}";

        $('a.sort-group').each(function () {

            var selfUrl = new URL($(this).attr('href'));

            if (orderAttribute === selfUrl.searchParams.get('order_by').replace('-', '')) {

                if (isAsc == 1) {

                    $(this).addClass('sort-asc');

                    $(this).attr('href', "{!! route('order.index', array_merge(request()->except('_token'), ['order_by' => request()->input('order_by'), 'is_asc' => 0])) !!}");

                } else {

                    $(this).addClass('sort-desc');

                    $(this).attr('href', "{!! route('order.index', array_merge(request()->except('_token'), ['order_by' => request()->input('order_by'), 'is_asc' => 1])) !!}");

                }

            }

        });

    </script>

@endsection

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
                    '我的訂單'  => ''
                ]
            ])

            <div class="order-search-bar">

                {{-- TODO: 全部改用laravel的Form方法 --}}
                {!! Form::open(['url' => '#', 'method' => 'GET']) !!}

                    <div class="form-group row">

                        @csrf

                        <div class="col-3">

                            {!! Form::bsSelect('訂單狀態', 'order_status', '', Order::getOrderStatusList(), '全部') !!}

                        </div>

                        <div class="col-3">

                            {!! Form::bsText('訂單號', 'order_number') !!}

                        </div>

                        <div class="col-6">

                            <div class="row">

                                <div class="col-2">

                                    <label>日期範圍</label>

                                </div>

                                <div class="col-4">

                                    <div class="input-group date" id="datetimepicker1" data-target-input="nearest">

                                        <input type="text" class="form-control datetimepicker-input" data-target="#datetimepicker1" name="date_start" value="{{ old('date_start') }}">

                                        <div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker">

                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>

                                        </div>

                                    </div>

                                </div>

                                <span style="font-size: 20px">~</span>

                                <div class="col-4">

                                    <div class="input-group date" id="datetimepicker2" data-target-input="nearest">

                                        <input type="text" class="form-control datetimepicker-input" data-target="#datetimepicker2" name="date_end" value="{{ old('date_end') }}">

                                        <div class="input-group-append" data-target="#datetimepicker2" data-toggle="datetimepicker">

                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="col-3">

                            {!! Form::button('搜尋', ['class' => 'btn btn-primary', 'type' => 'Submit']) !!}

                        </div>

                    </div>

                {!! Form::close() !!}

            </div>

            <div style="padding:0px 20px">

                @if ($orders->isEmpty())

                    目前沒有任何訂單

                    <div>

                        <a class="mt-5 btn btn-info" href="{{route('shop.index')}}">前往消費--></a>

                    </div>

                @else

                    <table class="table table-hover mt-5">

                        <thead>


                            {{-- TODO: 在model裏設定要用來搜尋的attribute 用loop生成 --}}
                            <tr>

                                <th>訂單號</th>

                                <th>

                                    <a class="sort-group" href="{{ route('order.index', array_merge(request()->except('_token'), ['order_by' => 'created_at'])) }}">

                                        購買日期

                                    </a>


                                </th>

                                <th>

                                    <a class="sort-group" href="{!! route('order.index', array_merge(request()->except('_token'), ['order_by' => 'total'])) !!}">

                                        總額

                                    </a>

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('order.index', array_merge(request()->except('_token'), ['order_by' => 'order_status'])) }}">

                                        訂單狀態

                                    </a>

                                </th>

                                <th></th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach ($orders as $order)

                                <tr>

                                    <th>{{ $order->order_number }}</th>

                                    <td>{{ $order->created_at }}</td>

                                    <td>{{ $order->total }}</td>

                                    <td>{{ Order::getOrderStatusList()[$order->order_status] }}</td>

                                    <td>

                                        <a href="{{ route('order.show', ['order' => $order->id]) }}" class="btn btn-info">詳細内容</a>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                    {{ $orders->links() }}

                @endif

            </div>

        </div>

    </div>

    <!-- End Order -->


@endsection
