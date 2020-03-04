@php

    use App\Models\Order;

@endphp

@extends('admin.layouts.app')

@section('styles')

    @parent

    {{-- date picker --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.0-alpha14/css/tempusdominus-bootstrap-4.min.css" />

    <link href="{{ asset('css/admin/dataTables.bootstrap4.min.css') }}"
        rel="stylesheet">

@endsection

@section('scripts')

    @parent

    {{-- date picker --}}
    <script src="{{ asset('js/moment/moment.js') }}"></script>

    <script src="{{ asset('js/moment/locale/zh-tw.js') }}"></script>

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.0-alpha14/js/tempusdominus-bootstrap-4.min.js"></script>

    <script>

        $(function () {

            //---排序用
            var orderAttribute = "{{ request()->input('order_by') }}";

            var isAsc = "{{ request()->input('is_asc') }}";

            $('a.sort-group').each(function () {

                var selfUrl = new URL($(this).attr('href'));

                if (orderAttribute === selfUrl.searchParams.get('order_by').replace('-', '')) {

                    if (isAsc == 1) {

                        $(this).addClass('sort-asc');

                        $(this).attr('href', "{!! route('admin.order.index', array_merge(request()->except('_token'), ['order_by' => request()->input('order_by'), 'is_asc' => 0])) !!}");

                    } else {

                        $(this).addClass('sort-desc');

                        $(this).attr('href', "{!! route('admin.order.index', array_merge(request()->except('_token'), ['order_by' => request()->input('order_by'), 'is_asc' => 1])) !!}");

                    }

                }

            });

            //--date picker
            $('#datetimepicker1').datetimepicker({
                format: 'YYYY-MM-DD',
            });

            $('#datetimepicker2').datetimepicker({
                format: 'YYYY-MM-DD',
            });

        });

    </script>

@endsection

@section('content')

    @include('admin.includes.alert')

    <!-- DataTales Example -->
    <div class="card shadow mb-4">

        <div class="card-header py-3">

            <h1 class="m-0 font-weight-bold text-primary">訂單列表</h1>

            @include('admin.includes.breadcrumb', [
                'data' => [
                    '訂單列表' => ''
                ]
            ])

        </div>

        <div class="card-body">

            {{-- search --}}
            {!! Form::open(['url' => route('admin.order.index'), 'method' => 'GET']) !!}

                <div class="row mb-3">

                    <div class="col-3 p-4">

                        {!! Form::bsText('訂單號', 'order_number', request()->input('order_number') ?? '', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        {!! Form::bsText('用戶名稱', 'nickname', request()->input('nickname') ?? '', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        {!! Form::bsSelect('訂單狀態', 'order_status', request()->input('order_status') ?? '', Order::getOrderStatusList(), '全部', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        {!! Form::bsSelect('付款狀態', 'payment_status', request()->input('payment_status') ?? '', Order::getPaymentStatusList(), '全部', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        {!! Form::bsSelect('付款方式', 'payment_method', request()->input('payment_method') ?? '', $paymentMethodList, '全部', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        <div class="row">

                            <div class="col-9">

                                <label>訂單總額</label>

                            </div>

                            <div class="col-5">

                                <select name="compare_type" class="form-control">

                                    @foreach ($compareTypeList as $compareType)

                                        <option value="{{ $compareType }}"  {{ request()->input('compare_type') == $compareType ? 'selected' : '' }}>{{ $compareType }}</option>

                                    @endforeach

                                </select>

                            </div>

                            <div class="col-5">

                                <input name="total" type="number" min="0" class="form-control" value="{{ request()->input('total') }}">

                            </div>

                        </div>

                    </div>

                    <div class="col-6 p-4">

                        <div class="row">

                            <div class="col-9">

                                <label>日期範圍</label>

                            </div>

                            <div class="col-5">

                                <div class="input-group date" id="datetimepicker1" data-target-input="nearest">

                                    <input type="text" class="form-control datetimepicker-input" data-target="#datetimepicker1" name="date_start" value="{{ request()->input('date_start') }}">

                                    <div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker">

                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>

                                    </div>

                                </div>

                            </div>

                            <span style="font-size: 20px">~</span>

                            <div class="col-5">

                                <div class="input-group date" id="datetimepicker2" data-target-input="nearest">

                                    <input type="text" class="form-control datetimepicker-input" data-target="#datetimepicker2" name="date_end" value="{{ request()->input('date_end') }}">

                                    <div class="input-group-append" data-target="#datetimepicker2" data-toggle="datetimepicker">

                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <div class="col-3 p-4">

                        <br>

                        {!! Form::button('搜尋', ['type' => 'Submit', 'class' => 'btn btn-primary mt-2']) !!}

                    </div>


                </div>

            {!! Form::close() !!}

            {{-- table --}}
            <div class="table-responsive">

                <table class="table table-bordered mt-2" id="dataTable" width="100%" cellspacing="0">

                    <thead>

                        <tr>

                            <th>

                                <a class="sort-group" href="{{ route('admin.order.index', array_merge(request()->except('_token'), ['order_by' => 'order_number'])) }}">

                                    訂單號

                                </a>

                            </th>

                            <th>

                                用戶名稱

                            </th>

                            <th>

                                <a class="sort-group" href="{{ route('admin.order.index', array_merge(request()->except('_token'), ['order_by' => 'order_status'])) }}">

                                    訂單狀態

                                </a>

                            </th>

                            <th>

                                <a class="sort-group" href="{{ route('admin.order.index', array_merge(request()->except('_token'), ['order_by' => 'payment_status'])) }}">

                                    付款狀態

                                </a>

                            </th>

                            <th>

                                <a class="sort-group" href="{{ route('admin.order.index', array_merge(request()->except('_token'), ['order_by' => 'payment_method'])) }}">

                                    付款方式

                                </a>

                            </th>

                            <th>

                                <a class="sort-group" href="{{ route('admin.order.index', array_merge(request()->except('_token'), ['order_by' => 'total'])) }}">

                                    訂單總額

                                </a>

                            </th>

                            <th>

                                <a class="sort-group" href="{{ route('admin.order.index', array_merge(request()->except('_token'), ['order_by' => 'created_at'])) }}">

                                    建立日期

                                </a>

                            </th>

                            <th></th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach ($orderList as $order)

                            <tr>

                                <td>{{ $order->order_number }}</td>

                                <td><a href="{{ route('admin.user.show', ['user' => $order->user->id]) }}">{{ $order->user->nickname }}</a></td>

                                <td>{{ Order::getOrderStatusList()[$order->order_status] }}</td>

                                <td>{{ Order::getPaymentStatusList()[$order->payment_status] }}</td>

                                <td>{{ $paymentMethodList[$order->payment_method] }}</td>

                                <td>{{ $order->total }}</td>

                                <td>{{ $order->created_at }}</td>

                                <td style="width: 10%">

                                    <a class="btn btn-sm btn-info mr-3" href="{{ route('admin.order.show', ['order' => $order->id]) }}"><i class="far fa-eye"></i></a>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            {{ $orderList->links() }}

        </div>

    </div>

@endsection
