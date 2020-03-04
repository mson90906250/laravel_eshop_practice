@php

    use App\Models\Coupon;

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
    {{--  --}}

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

                        $(this).attr('href', "{!! route('admin.coupon.index', array_merge(request()->except('_token'), ['order_by' => request()->input('order_by'), 'is_asc' => 0])) !!}");

                    } else {

                        $(this).addClass('sort-desc');

                        $(this).attr('href', "{!! route('admin.coupon.index', array_merge(request()->except('_token'), ['order_by' => request()->input('order_by'), 'is_asc' => 1])) !!}");

                    }

                }

            });
            //--

            //當按倒刪除群組按鈕時要更動 action 及 method
            $('#btn-delete-coupon').on('click', function () {

                if (confirm('確定要刪除嗎?')) {

                    $('input[name="_method"][type="hidden"]').val('DELETE');

                    $('#form-coupon').attr('action', '{{ route("admin.coupon.destroy") }}');

                    $('#form-coupon').submit();

                }

            });
            //--

             //選擇全部checkbox
             $('#select-all').on('click', function () {

                if (this.checked) {

                    $('tbody input[type="checkbox"]').not(':checked').trigger('click');

                } else {

                    $('tbody input[type="checkbox"]:checked').trigger('click');

                }

            });
            //--

            //--date picker
            $('#datetimepicker1').datetimepicker({
                format: 'YYYY-MM-DD',
            });

            $('#datetimepicker2').datetimepicker({
                format: 'YYYY-MM-DD',
            });
            //--

        });

    </script>

@endsection

@section('content')

    @include('admin.includes.alert')

    <!-- DataTales Example -->
    <div class="card shadow mb-4">

        <div class="card-header py-3">

            <h1 class="m-0 font-weight-bold text-primary">優惠券清單</h1>

            @include('admin.includes.breadcrumb', [
                'data' => [
                    '優惠券清單' => ''
                ]
            ])

        </div>

        <div class="card-body">

            {{-- search --}}
            {!! Form::open(['url' => route('admin.coupon.index'), 'method' => 'GET']) !!}

                <div class="row mb-3">

                    <div class="col-3 p-4">

                        {!! Form::bsText('名稱', 'title', request()->input('title') ?? '', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        {!! Form::bsText('代碼', 'code', request()->input('code') ?? '', 'vertical') !!}

                    </div>


                    <div class="col-3 p-4">

                        {!! Form::bsSelect('優惠券類型', 'value_type', request()->input('value_type') ?? '', Coupon::getTypeLabelsForShow(), '全部', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        {!! Form::bsText('折抵數', 'value', request()->input('value') ?? '', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        {!! Form::bsText('剩餘次數', 'remain', request()->input('remain') ?? '', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        {!! Form::bsText('需要滿足金額', 'required_value', request()->input('required_value') ?? '', 'vertical') !!}

                    </div>

                    <div class="col-6 p-4">

                        <div class="row">

                            <div class="col-9">

                                <label>有效時間範圍</label>

                            </div>

                            <div class="col-5">

                                <div class="input-group date" id="datetimepicker1" data-target-input="nearest">

                                    <input type="text" class="form-control datetimepicker-input" data-target="#datetimepicker1" name="start_time" value="{{ request()->input('start_time') }}">

                                    <div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker">

                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>

                                    </div>

                                </div>

                            </div>

                            <span style="font-size: 20px">~</span>

                            <div class="col-5">

                                <div class="input-group date" id="datetimepicker2" data-target-input="nearest">

                                    <input type="text" class="form-control datetimepicker-input" data-target="#datetimepicker2" name="end_time" value="{{ request()->input('end_time') }}">

                                    <div class="input-group-append" data-target="#datetimepicker2" data-toggle="datetimepicker">

                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="col-3 p-4">

                        {!! Form::bsSelect('狀態', 'status', request()->input('status') ?? '', Coupon::getStatusLabels(), '全部', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        <br>

                        {!! Form::button('搜尋', ['type' => 'Submit', 'class' => 'btn btn-primary mt-2']) !!}

                    </div>


                </div>

            {!! Form::close() !!}

            {{-- table --}}
            {!! Form::open(['url' => route('admin.coupon.updateStatus'), 'method' => 'post', 'id' => 'form-coupon']) !!}

                @method('PUT')

                <div class="table-responsive">

                    {!! Form::button('開啓', ['class' => 'btn btn-success', 'type' => 'submit', 'value' => Coupon::STATUS_ON, 'name' => 'status']) !!}

                    {!! Form::button('關閉', ['class' => 'btn btn-danger', 'type' => 'submit', 'value' => Coupon::STATUS_OFF, 'name' => 'status']) !!}

                    {!! Form::button('刪除優惠券', ['class' => 'btn btn-warning', 'type' => 'button', 'id' => 'btn-delete-coupon']) !!}

                    <a href="{{ route('admin.coupon.create') }}" class="btn btn-info">新增優惠券</a>

                    <table class="table table-bordered mt-2" id="dataTable" width="100%" cellspacing="0">

                        <thead>

                            <tr>

                                <th>

                                    <input id="select-all" type="checkbox">

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.coupon.index', array_merge(request()->except('_token'), ['order_by' => 'id'])) }}">

                                        名稱

                                    </a>

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.coupon.index', array_merge(request()->except('_token'), ['order_by' => 'code'])) }}">

                                        代碼

                                    </a>

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.coupon.index', array_merge(request()->except('_token'), ['order_by' => 'remain'])) }}">

                                        剩餘次數

                                    </a>

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.coupon.index', array_merge(request()->except('_token'), ['order_by' => 'value_type'])) }}">

                                        優惠券類型

                                    </a>

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.coupon.index', array_merge(request()->except('_token'), ['order_by' => 'value'])) }}">

                                        折抵數

                                    </a>

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.coupon.index', array_merge(request()->except('_token'), ['order_by' => 'required_value'])) }}">

                                        需要滿足金額

                                    </a>

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.coupon.index', array_merge(request()->except('_token'), ['order_by' => 'status'])) }}">

                                        狀態

                                    </a>

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.coupon.index', array_merge(request()->except('_token'), ['order_by' => 'start_time'])) }}">

                                        開始時間

                                    </a>

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.coupon.index', array_merge(request()->except('_token'), ['order_by' => 'end_time'])) }}">

                                        結束時間

                                    </a>

                                </th>

                                <th></th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach ($couponList as $coupon)

                                <tr>

                                    <td>

                                        <input type="checkbox" name="id[]" value="{{ $coupon->id }}">

                                    </td>

                                    <td>{{ $coupon->title }}</td>

                                    <td>{{ $coupon->code }}</td>

                                    <td>{{ $coupon->remain }}</td>

                                    <td>{{ Coupon::getTypeLabelsForShow()[$coupon->value_type] }}</td>

                                    <td>{{ $coupon->value }}</td>

                                    <td>{{ $coupon->required_value }}</td>

                                    <td>{{ Coupon::getStatusLabels()[$coupon->status] }}</td>

                                    <td>{{ $coupon->start_time }}</td>

                                    <td>{{ $coupon->end_time }}</td>

                                    <td style="width: 10%">

                                        <a class="btn btn-sm btn-danger mr-3" href="{{ route('admin.coupon.edit', ['coupon' => $coupon->id]) }}"><i class="far fa-edit"></i></a>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            {!! Form::close() !!}

            {{ $couponList->links() }}

        </div>

    </div>

@endsection
