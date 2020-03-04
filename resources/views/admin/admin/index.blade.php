@php

    use App\Models\Admin;

@endphp

@extends('admin.layouts.app')

@section('styles')

    @parent

    <link href="{{ asset('css/admin/dataTables.bootstrap4.min.css') }}"
        rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.3.1/css/select.dataTables.css"/>

@endsection

@section('scripts')

    @parent

    <script>

        $(function () {

            //當按倒刪除群組按鈕時要更動 action 及 method
            $('#button-delete-admins').on('click', function () {

                if (confirm('確定要刪除嗎?')) {

                    $('input[name="_method"][type="hidden"]').val('DELETE');

                    $('#form-admin').attr('action', '{{ route("admin.admin.destroy") }}');

                    $('#form-admin').submit();

                }

            });

            //---
            $('#select-all').on('click', function () {

                if (this.checked) {

                    $('tbody input[type="checkbox"]').not(':checked').trigger('click');

                } else {

                    $('tbody input[type="checkbox"]:checked').trigger('click');

                }

            });

            //---
            var orderAttribute = "{{ request()->input('order_by') }}";

            var isAsc = "{{ request()->input('is_asc') }}";

            $('a.sort-group').each(function () {

                var selfUrl = new URL($(this).attr('href'));

                if (orderAttribute === selfUrl.searchParams.get('order_by').replace('-', '')) {

                    if (isAsc == 1) {

                        $(this).addClass('sort-asc');

                        $(this).attr('href', "{!! route('admin.admin.index', array_merge(request()->except('_token'), ['order_by' => request()->input('order_by'), 'is_asc' => 0])) !!}");

                    } else {

                        $(this).addClass('sort-desc');

                        $(this).attr('href', "{!! route('admin.admin.index', array_merge(request()->except('_token'), ['order_by' => request()->input('order_by'), 'is_asc' => 1])) !!}");

                    }

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

            <h1 class="m-0 font-weight-bold text-primary">管理員清單</h1>

            @include('admin.includes.breadcrumb', [
                'data' => [
                    '管理員清單' => ''
                ]
            ])

        </div>

        <div class="card-body">

            {!! Form::open(['url' => route('admin.admin.index'), 'method' => 'GET']) !!}

                <div class="row mb-3">

                    <div class="col-3 p-4">

                        {!! Form::bsText('名稱', 'username', request()->input('username') ?? '', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        {!! Form::bsSelect('權限群組', 'permission_group', request()->input('permission_group') ?? '', $permissionGroupList, '全部', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        {!! Form::bsSelect('狀態', 'status', request()->input('status') ?? '', Admin::getStatusLabels(), '全部', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        <br>

                        {!! Form::button('搜尋', ['type' => 'Submit', 'class' => 'btn btn-primary mt-2']) !!}

                    </div>


                </div>

            {!! Form::close() !!}

            {!! Form::open(['url' => route('admin.admin.updateStatus'), 'method' => 'POST', 'id' => 'form-admin']) !!}

                @method('PUT')

                <div class="table-responsive">

                    {{ Form::button('開啓', ['name' => 'status', 'class' => 'btn btn-success', 'type' => 'Submit', 'value' => Admin::STATUS_ON]) }}

                    {{ Form::button('關閉', ['name' => 'status', 'class' => 'btn btn-danger', 'type' => 'Submit', 'value' => Admin::STATUS_OFF]) }}

                    {{ Form::button('刪除管理員', ['id' => 'button-delete-admins', 'class' => 'btn btn-warning', 'type' => 'Button']) }}

                    <a href="{{ route('admin.admin.create') }}" class="btn btn-primary">新增管理員</a>

                    <table class="table table-bordered mt-2" id="dataTable" width="100%" cellspacing="0">

                        <thead>

                            <tr>

                                <th>

                                    <input id="select-all" type="checkbox" style="width: 100%">

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.admin.index', array_merge(request()->except('_token'), ['order_by' => 'username'])) }}">

                                        Username

                                    </a>

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.admin.index', array_merge(request()->except('_token'), ['order_by' => 'status'])) }}">

                                        狀態

                                    </a>

                                </th>

                                <th></th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach ($adminList as $item)

                                <tr>

                                    <td style="width: 5%">

                                        @if (intval($item->id) !== Admin::SUPER_ADMIN_ID)

                                            <input type="checkbox" name="id[]" value="{{ $item->id }}" style="width: 100%">

                                        @endif

                                    </td>

                                    <td>{{ $item->username }}</td>

                                    <td>{{ Admin::getStatusLabels()[$item->status] }}</td>

                                    <td style="width: 10%">

                                        <a class="btn btn-sm btn-info mr-3" href="{{ route('admin.admin.show', ['admin' => $item->id]) }}"><i class="far fa-eye"></i></a>

                                        <a class="btn btn-sm btn-danger" href="{{ route('admin.admin.edit', ['admin' => $item->id]) }}"><i class="fas fa-pen"></i></a>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            {!! Form::close() !!}

            {{ $adminList->links() }}

        </div>

    </div>

@endsection
