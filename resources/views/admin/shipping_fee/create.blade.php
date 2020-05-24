@php

    use App\Models\ShippingFee;

@endphp

@extends('admin.layouts.app')

@section('content')

    @include('admin.includes.alert')

    <!-- DataTales Example -->
    <div class="card shadow mb-4">

        <div class="card-header py-3">

            <h1 class="m-0 font-weight-bold text-primary">運費列表</h1>

            @include('admin.includes.breadcrumb', [
                'data' => [
                    '運費列表' => route('admin.shippingFee.index'),
                    '創建' => ''
                ]
            ])

        </div>

        {{-- content --}}
        <div class="card-body">

            {!! Form::open(['url' => route('admin.shippingFee.store'), 'method' => 'POST']) !!}

                <div class="row mb-3 ml-5">

                    <div class="col-7 p-4">

                        {!! Form::bsText('名稱', 'name', request()->input('name') ?? '', 'vertical') !!}

                    </div>

                    <div class="col-7 p-4">

                        {!! Form::bsText('運費', 'value', request()->input('value') ?? '', 'vertical') !!}

                    </div>

                    <div class="col-7 p-4">

                        {!! Form::bsText('滿足金額', 'required_value', request()->input('required_value') ?? '', 'vertical') !!}

                    </div>

                    <div class="col-7 p-4">

                        {!! Form::bsSelect('運費類型', 'type', request()->input('type') ?? '', ShippingFee::getTypeList(), '', 'vertical') !!}

                    </div>

                    <div class="col-7 p-4">

                        {!! Form::bsSelect('狀態', 'status', request()->input('status') ?? '', ShippingFee::getStatusLabels(), '', 'vertical') !!}

                    </div>

                    <div class="col-7 p-3">

                        {!! Form::button('創建', ['type' => 'Submit', 'class' => 'btn btn-primary mt-2']) !!}

                    </div>


                </div>

            {!! Form::close() !!}

        </div>
        {{--  --}}

    </div>

@endsection
