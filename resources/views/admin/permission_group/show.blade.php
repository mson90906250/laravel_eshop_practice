@php

    use App\Models\PermissionGroup;

@endphp

@extends('admin.layouts.app')

@section('styles')

    @parent

    <link href="{{ asset('css/admin/dataTables.bootstrap4.min.css') }}"
        rel="stylesheet">


@endsection

@section('scripts')

    @parent

    <script>

        $(function () {

            $('.select-all').on('click', function () {

                if (this.checked) {

                    $('.'+ $(this).data('group') +'-action').not(':checked').trigger('click');


                } else {

                    $('.'+ $(this).data('group') +'-action:checked').trigger('click');

                }

            });

        });

    </script>

@endsection

@section('content')

    @include('admin.includes.alert')

    <!-- DataTales Example -->
    <div class="card shadow mb-4">

        <div class="card-header py-3">

            <h1 class="m-0 font-weight-bold text-primary">權限群組清單</h1>

            @include('admin.includes.breadcrumb', [
                'data' => [
                    '權限群組清單' => route('admin.permissionGroup.index'),
                    sprintf('%s群組', $permissionGroup->name) => ''
                ]
            ])

        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-3 p-3">

                    <label>群組名</label>

                    <input class="form-control" type="text" disabled value="{{ $permissionGroup->name }}">

                </div>

                <div class="col-3 p-3">

                    <label>狀態</label>

                    <input class="form-control" type="text" disabled value="{{ PermissionGroup::getStatusLabels()[$permissionGroup->status] }}">

                </div>

                <div class="col-3 p-3">

                    <br>

                    <a href="{{ route('admin.permissionGroup.edit', ['permissionGroup' => $permissionGroup->id]) }}"
                        class="btn btn-danger mt-2">修改</a>

                </div>

            </div>

            <div class="row">

                @foreach ($permissionList as $name => $permissions)

                    <div class="col-3 p-3">

                        <label style="font-size: 1.5em">

                            {{ $name }}

                        </label>

                        <hr class="m-0">

                        <ul>

                            @foreach ($permissions as $id => $action)

                                <li style="font-size: 1.2em">

                                    {{ $action }}

                                </li>

                            @endforeach

                        </ul>

                    </div>

                @endforeach

            </div>

        </div>

    </div>

@endsection
