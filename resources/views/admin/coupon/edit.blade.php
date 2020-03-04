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

            <h1 class="m-0 font-weight-bold text-primary">優惠券列表</h1>

            @include('admin.includes.breadcrumb', [
                'data' => [
                    '優惠券列表' => route('admin.coupon.index'),
                    sprintf('%s 修改', $coupon->code ) => '',
                ]
            ])

        </div>

        {{-- content --}}
        <div class="card-body">

            {!! Form::open(['url' => route('admin.coupon.update', ['coupon' => $coupon->id]), 'method' => 'POST']) !!}

                @method('PUT')

                <div class="row mb-3 ml-5">

                    <div class="col-7 p-3">

                        {!! Form::bsText('名稱', 'title', request()->input('title') ?? $coupon->title, 'vertical') !!}

                    </div>

                    <div class="col-7 p-3">

                        {!! Form::bsSelect('優惠券類型', 'value_type', request()->input('value_type') ?? $coupon->value_type, Coupon::getTypeLabelsForShow(), '', 'vertical') !!}

                    </div>

                    <div class="col-7 p-3">

                        {!! Form::bsText('折抵數', 'value', request()->input('value') ?? $coupon->value, 'vertical') !!}

                    </div>

                    <div class="col-7 p-3">

                        {!! Form::bsText('剩餘次數', 'remain', request()->input('remain') ?? $coupon->remain, 'vertical') !!}

                    </div>

                    <div class="col-7 p-3">

                        {!! Form::bsText('需要滿足金額', 'required_value', request()->input('required_value') ?? $coupon->required_value, 'vertical') !!}

                    </div>

                    <div class="col-7 p-3">

                        {!! Form::bsSelect('狀態', 'status', request()->input('status') ?? $coupon->status, Coupon::getStatusLabels(), '', 'vertical') !!}

                    </div>

                    <div class="col-7 p-3">

                        <div class="row">

                            <label>開始時間</label>

                            <div class="input-group date" id="datetimepicker1" data-target-input="nearest">

                                <input type="text" class="form-control datetimepicker-input" data-target="#datetimepicker1" name="start_time" value="{{ request()->input('start_time') ?? $coupon->start_time }}">

                                <div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker">

                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="col-7 p-3">

                        <div class="row">

                            <label>結束時間 (時間一律為當天的23:59:59)</label>

                            <div class="input-group date" id="datetimepicker2" data-target-input="nearest">

                                <input type="text" class="form-control datetimepicker-input" data-target="#datetimepicker2" name="end_time" value="{{ request()->input('end_time') ?? $coupon->end_time }}">

                                <div class="input-group-append" data-target="#datetimepicker2" data-toggle="datetimepicker">

                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="col-7 p-3">

                        {!! Form::button('修改', ['type' => 'Submit', 'class' => 'btn btn-primary mt-2']) !!}

                    </div>


                </div>

            {!! Form::close() !!}

        </div>
        {{--  --}}

    </div>

@endsection
