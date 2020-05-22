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
                    sprintf('%s群組', $permissionGroup->name) => route('admin.permissionGroup.show', ['permissionGroup' => $permissionGroup->id]),
                    '修改' => ''
                ]
            ])

        </div>

        <div class="card-body">

            {!! Form::open(['url' => route('admin.permissionGroup.update', ['permissionGroup' => $permissionGroup->id]), 'method' => 'POST']) !!}

                @method('PUT')

                <div class="row">

                    <div class="col-3 p-3">

                        {!! Form::bsText('群組名', 'name', request()->input('name') ?? $permissionGroup->name, 'vertical') !!}

                    </div>

                    @if (!$permissionGroup->has_all_permissions)

                        <div class="col-3 p-3">

                            {!! Form::bsSelect('狀態', 'status', request()->input('status') ?? $permissionGroup->status, PermissionGroup::getStatusLabels(), '', 'vertical') !!}

                        </div>

                    @endif

                    <div class="col-3 p-3">

                        <br>

                        {!! Form::button('修改', ['class' => 'btn btn-primary mt-2', 'type' => 'Submit']) !!}

                    </div>

                </div>

                @if (!$permissionGroup->has_all_permissions)

                    <div class="row">

                        @foreach ($permissionList as $name => $permissions)

                            <div class="col-3 p-3">

                                <label style="font-size: 1.5em">

                                    <input data-group="{{ $name }}" class="select-all mr-1" type="checkbox">

                                    {{ $name }}

                                </label>

                                <hr class="m-0">

                                @foreach ($permissions as $id => $action)

                                    <label style="font-size: 1.2em; display: block; padding-left: 20px">

                                        <input class="mr-1 {{ sprintf('%s-action', $name) }}"
                                                name="permissionId[]"
                                                type="checkbox"
                                                value='{{ $id }}'
                                                {{ in_array($id, old('permissionId') ?? []) || isset($selfPermissionList[$name][$id]) ? 'checked' : '' }} >

                                        {{ $action }}

                                    </label>

                                @endforeach

                            </div>

                        @endforeach

                    </div>

                @endif

            {!! Form::close() !!}

        </div>

    </div>

@endsection
