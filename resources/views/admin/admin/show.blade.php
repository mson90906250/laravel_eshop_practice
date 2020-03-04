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
                    sprintf('管理員: %s', $admin->username) => ''
                ]
            ])

        </div>

        <div class="card-body">

            <div class="row mb-3 ml-5">

                <div class="col-7 p-3">

                    <div class="row">

                        <label>名稱</label>

                        <input class="form-control" type="text" value="{{ $admin->username }}" disabled>

                    </div>
                </div>

                <div class="col-7 p-3">

                    <div class="row">

                        <label>狀態</label>

                        <input class="form-control" type="text" value="{{ Admin::getStatusLabels()[$admin->status] }}" disabled>

                    </div>

                </div>

                <div class="col-7 p-3">

                    <div class="row">

                        <p>權限群組</p>

                    </div>

                    <div class="row">

                        @foreach ($admin->permission_groups as $group)

                            <div class="col-3">

                                <label>

                                    <input type="checkbox"  disabled checked>

                                    {{ $group->name }}

                                </label>

                            </div>

                        @endforeach

                    </div>

                </div>

                <div class="col-7 p-3">

                    <a href="{{ route('admin.admin.edit', ['admin' => $admin->id]) }}" class="btn btn-primary">修改</a>

                </div>


            </div>

        </div>

    </div>

@endsection
