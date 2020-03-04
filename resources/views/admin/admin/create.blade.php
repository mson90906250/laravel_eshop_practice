@php

    use App\Models\Admin;

@endphp

@extends('admin.layouts.app')

@section('content')

    @include('admin.includes.alert')

    <!-- DataTales Example -->
    <div class="card shadow mb-4">

        <div class="card-header py-3">

            <h1 class="m-0 font-weight-bold text-primary">管理員清單</h1>

            @include('admin.includes.breadcrumb', [
                'data' => [
                    '管理員清單' => route('admin.admin.index'),
                    '創建管理員' => ''
                ]
            ])

        </div>

        <div class="card-body">

            {!! Form::open(['url' => route('admin.admin.store'), 'method' => 'POST']) !!}

                <div class="row mb-3 ml-5">

                    <div class="col-7 p-3">

                        {!! Form::bsText('名稱', 'username', request()->input('username') ?? '', 'vertical') !!}

                    </div>

                    <div class="col-7 p-3">

                        <div class="row">

                            <label for="password">密碼</label>

                            <input class="form-control" type="password" name="password">

                        </div>
                    </div>

                    <div class="col-7 p-3">

                        <div class="row">

                            <label for="confirm_password">確認密碼</label>

                            <input class="form-control" type="password" name="confirm_password">

                        </div>

                    </div>

                    <div class="col-7 p-3">

                        {!! Form::bsSelect('狀態', 'status', request()->input('status') ?? '', Admin::getStatusLabels(), '', 'vertical') !!}

                    </div>

                    <div class="col-7 p-3">

                        <div class="row">

                            <p>權限群組(可複選)</p>

                        </div>

                        <div class="row">

                            @foreach ($permissionGroupList as $id => $name)

                                <div class="col-3">

                                    <label>

                                        <input type="checkbox" name="permission_group[]" value="{{ $id }}">

                                        {{ $name }}

                                    </label>

                                </div>

                            @endforeach

                        </div>

                    </div>

                    <div class="col-7 p-3">

                        {!! Form::button('創建', ['type' => 'Submit', 'class' => 'btn btn-primary mt-2']) !!}

                    </div>


                </div>

            {!! Form::close() !!}

        </div>

    </div>

@endsection
