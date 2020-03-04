@php

    use App\Models\Brand;

@endphp

@extends('admin.layouts.app')

@section('content')

    @include('admin.includes.alert')

    <!-- DataTales Example -->
    <div class="card shadow mb-4">

        {{-- header --}}
        <div class="card-header py-3">

            <h1 class="m-0 font-weight-bold text-primary">品牌列表</h1>

            @include('admin.includes.breadcrumb', [
                'data' => [
                    '品牌列表' => route('admin.brand.index'),
                    sprintf('修改: %s', $brand->name) => ''
                ]
            ])

        </div>
        {{--  --}}

        {{-- content --}}
        <div class="card-body">

            {!! Form::open(['url' => route('admin.brand.update', ['brand' => $brand->id]), 'method' => 'POST']) !!}

                @method('PUT')

                <div class="row mb-3 ml-5">

                    <div class="col-7 p-3">

                        {!! Form::bsText('名稱', 'name', request()->input('name') ?? $brand->name, 'vertical') !!}

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
