@php

    use App\Models\Admin;

    $ownedPermissionGroupId = $admin->permission_groups()->pluck('permission_groups.id');

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
                    sprintf('修改管理員: %s', $admin->username) => ''
                ]
            ])

        </div>

        <div class="card-body">

            {!! Form::open(['url' => route('admin.admin.update', ['admin' => $admin->id]), 'method' => 'POST']) !!}

                @method('PUT')

                <div class="row mb-3 ml-5">

                    <div class="col-7 p-3">

                        {!! Form::bsText('名稱', 'username', request()->input('username') ?? $admin->username, 'vertical') !!}

                    </div>

                    <div class="col-7 p-3">

                        <div class="row">

                            <label for="old_password">舊密碼</label>

                            <input class="form-control" type="password" name="old_password">

                        </div>
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

                    @if ($admin->id !== Admin::SUPER_ADMIN_ID)

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

                                            <input type="checkbox" name="permission_group[]" value="{{ $id }}" {{ in_array($id, $ownedPermissionGroupId->toArray()) ? 'checked' : '' }}>

                                            {{ $name }}

                                        </label>

                                    </div>

                                @endforeach

                            </div>

                        </div>

                    @endif

                    <div class="col-7 p-3">

                        {!! Form::button('修改', ['type' => 'Submit', 'class' => 'btn btn-primary mt-2']) !!}

                    </div>


                </div>

            {!! Form::close() !!}

        </div>

    </div>

@endsection
