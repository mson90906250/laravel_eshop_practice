@php

    use App\Models\Category;

@endphp

@extends('admin.layouts.app')

@section('content')

    @include('admin.includes.alert')

    <!-- DataTales Example -->
    <div class="card shadow mb-4">

        <div class="card-header py-3">

            <h1 class="m-0 font-weight-bold text-primary">商品類型列表</h1>

            @include('admin.includes.breadcrumb', [
                'data' => [
                    '商品類型列表' => route('admin.category.index'),
                    '創建' => ''
                ]
            ])

        </div>

        {{-- content --}}
        <div class="card-body">

            {!! Form::open(['url' => route('admin.category.store'), 'method' => 'POST']) !!}

                <div class="row mb-3 ml-5">

                    <div class="col-7 p-3">

                        {!! Form::bsText('名稱', 'name', request()->input('name') ?? '', 'vertical') !!}

                    </div>

                    <div class="col-7 p-3">

                        {!! Form::bsSelect('主類型', 'parent_id', request()->input('parent_id') ?? '', Category::getSelectOptions(TRUE), '沒有主類型', 'vertical') !!}

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
