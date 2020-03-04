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

    <script src="{{ asset('js/admin/jquery.dataTables.min.js') }}"></script>

    <script src="{{ asset('js/admin/dataTables.bootstrap4.min.js') }}"></script>

    <script>

        $(function () {

            //當按倒刪除群組按鈕時要更動 action 及 method
            $('#button-delete-groups').on('click', function () {

                if (confirm('確定要刪除嗎?')) {

                    $('input[name="_method"][type="hidden"]').val('DELETE');

                    $('#form-permission-group').attr('action', '{{ route("admin.permissionGroup.destroy") }}');

                    $('#form-permission-group').submit();

                }

            });

            $('#select-all').on('click', function () {

                if (this.checked) {

                    $('tbody input[type="checkbox"]').not(':checked').trigger('click');

                } else {

                    $('tbody input[type="checkbox"]:checked').trigger('click');

                }

            });

            $('#dataTable').DataTable({
                searching: false,
                paging: false,
                data: {!! $data !!},
                columns: [
                    {
                        data: 'id',
                        orderable: false,
                        width:"20px",
                        render: function (data, type, row) {
                            return data === 1 ? '' : '<input name="id[]" type="checkbox" value='+ data +'>';
                        }
                    },
                    { data: 'name' },
                    {
                        data: 'has_all_permissions',
                        render: function (data, type, row) {
                            return data ? '是' : '否';
                        },
                    },
                    {
                        data: 'status',
                        render: function (data, type, row) {
                            let statuslabelList = {!! json_encode(PermissionGroup::getStatusLabels()) !!};
                            return statuslabelList[data];
                        }
                    },
                    {
                        defaultContent: '',
                        orderable: false,
                        width: "10%",
                        render: function (data, type, row) {
                            let showUrl = "{{ route('admin.permissionGroup.show') }}";
                            return '<a class="btn btn-sm btn-info mr-3" href="'+ showUrl + '/' + row.id +'"><i class="far fa-eye"></i></a>'
                                   + '<a class="btn btn-sm btn-danger" href="'+ showUrl + '/' + row.id +'/edit"><i class="fas fa-pen"></i></a>';
                        }
                    },
                ]
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
                    '權限群組清單' => ''
                ]
            ])

        </div>

        <div class="card-body">

            {!! Form::open(['url' => route('admin.permissionGroup.index'), 'method' => 'GET']) !!}

                <div class="row mb-3">

                    <div class="col-3 p-4">

                        {!! Form::bsText('群組名', 'name', request()->input('name') ?? '', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        {!! Form::bsSelect('狀態', 'status', request()->input('status') ?? '', PermissionGroup::getStatusLabels(), '全部', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        <br>

                        {!! Form::button('搜尋', ['type' => 'Submit', 'class' => 'btn btn-primary mt-2']) !!}

                    </div>


                </div>

            {!! Form::close() !!}

            {!! Form::open(['url' => route('admin.permissionGroup.updateStatus'), 'method' => 'POST', 'id' => 'form-permission-group']) !!}

                @method('PUT')

                <div class="table-responsive">

                    {{ Form::button('開啓', ['name' => 'status', 'class' => 'btn btn-success', 'type' => 'Submit', 'value' => PermissionGroup::STATUS_ON]) }}

                    {{ Form::button('關閉', ['name' => 'status', 'class' => 'btn btn-danger', 'type' => 'Submit', 'value' => PermissionGroup::STATUS_OFF]) }}

                    {{ Form::button('刪除群組', ['id' => 'button-delete-groups', 'class' => 'btn btn-warning', 'type' => 'Button']) }}

                    {{-- TODO: href --}}
                    <a href="{{ route('admin.permissionGroup.create') }}" class="btn btn-primary">新增群組</a>

                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">

                        <thead>

                            <tr>

                                <th>

                                    <input id="select-all" type="checkbox">

                                </th>

                                <th>群組名</th>

                                <th>擁有全部權限</th>

                                <th>狀態</th>

                                <th></th>

                            </tr>

                        </thead>

                    </table>

                </div>

            {!! Form::close() !!}

            {{ $permissionGroupList->links() }}

        </div>

    </div>

@endsection
