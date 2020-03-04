@php

    use App\Models\Permission;

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
                            return '<input name="id[]" type="checkbox" value='+ data +'>';
                        }
                    },
                    { data: 'controller' },
                    { data: 'action' },
                    {
                        data: 'status',
                        render: function (data, type, row) {
                            let statuslabelList = {!! json_encode(Permission::getStatusLabels()) !!};
                            return statuslabelList[data];
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

            <h1 class="m-0 font-weight-bold text-primary">權限清單</h1>

        </div>

        <div class="card-body">

            {!! Form::open(['url' => route('admin.permission.index'), 'method' => 'GET']) !!}

                <div class="row mb-3">

                    <div class="col-3 p-4">

                        {!! Form::bsSelect('Controller', 'controller', request()->input('controller') ?? '', $controllerList, '全部', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        {!! Form::bsSelect('Action', 'action', request()->input('action') ?? '', $actionList, '全部', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        {!! Form::bsSelect('狀態', 'status', request()->input('status') ?? '', Permission::getStatusLabels(), '全部', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        <br>

                        {!! Form::button('搜尋', ['type' => 'Submit', 'class' => 'btn btn-primary mt-2']) !!}

                    </div>


                </div>

            {!! Form::close() !!}

            {!! Form::open(['url' => route('admin.permission.update'), 'method' => 'POST']) !!}

                @method('PUT')

                <div class="table-responsive">

                    {{ Form::button('開啓', ['name' => 'status', 'class' => 'btn btn-success', 'type' => 'Submit', 'value' => Permission::STATUS_ON]) }}

                    {{ Form::button('關閉', ['name' => 'status', 'class' => 'btn btn-danger', 'type' => 'Submit', 'value' => Permission::STATUS_OFF]) }}

                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">

                        <thead>

                            <tr>

                                <th>

                                    <input id="select-all" type="checkbox">

                                </th>

                                <th>Controller</th>

                                <th>Action</th>

                                <th>狀態</th>

                            </tr>

                        </thead>

                    </table>

                </div>

            {!! Form::close() !!}

            {{ $permissionList->links() }}

        </div>

    </div>

@endsection
