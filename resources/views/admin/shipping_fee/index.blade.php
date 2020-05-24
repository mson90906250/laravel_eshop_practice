@php

    use App\Models\ShippingFee;

@endphp

@extends('admin.layouts.app')

@section('styles')

    @parent

    {{-- date picker --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.0-alpha14/css/tempusdominus-bootstrap-4.min.css" />

    <link href="{{ asset('css/admin/dataTables.bootstrap4.min.css') }}"
        rel="stylesheet">

@endsection

@section('scripts')

    @parent

    <script>

        $(function () {

            //---排序用
            var orderAttribute = "{{ request()->input('order_by') }}";

            var isAsc = "{{ request()->input('is_asc') }}";

            $('a.sort-group').each(function () {

                var selfUrl = new URL($(this).attr('href'));

                if (orderAttribute === selfUrl.searchParams.get('order_by').replace('-', '')) {

                    if (isAsc == 1) {

                        $(this).addClass('sort-asc');

                        $(this).attr('href', "{!! route('admin.shippingFee.index', array_merge(request()->except('_token'), ['order_by' => request()->input('order_by'), 'is_asc' => 0])) !!}");

                    } else {

                        $(this).addClass('sort-desc');

                        $(this).attr('href', "{!! route('admin.shippingFee.index', array_merge(request()->except('_token'), ['order_by' => request()->input('order_by'), 'is_asc' => 1])) !!}");

                    }

                }

            });
            //--

            //當按倒刪除群組按鈕時要更動 action 及 method
            $('#btn-delete-shippingFee').on('click', function () {

                if (confirm('確定要刪除嗎?')) {

                    $('input[name="_method"][type="hidden"]').val('DELETE');

                    $('#form-shippingFee').attr('action', '{{ route("admin.shippingFee.destroy") }}');

                    $('#form-shippingFee').submit();

                }

            });
            //--

             //選擇全部checkbox
             $('#select-all').on('click', function () {

                if (this.checked) {

                    $('tbody input[type="checkbox"]').not(':checked').trigger('click');

                } else {

                    $('tbody input[type="checkbox"]:checked').trigger('click');

                }

            });
            //--

        });

    </script>

@endsection

@section('content')

    @include('admin.includes.alert')

    <!-- DataTales Example -->
    <div class="card shadow mb-4">

        <div class="card-header py-3">

            <h1 class="m-0 font-weight-bold text-primary">運費列表</h1>

            @include('admin.includes.breadcrumb', [
                'data' => [
                    '運費列表' => ''
                ]
            ])

        </div>

        <div class="card-body">

            {{-- search --}}
            {!! Form::open(['url' => route('admin.shippingFee.index'), 'method' => 'GET']) !!}

                <div class="row mb-3">

                    <div class="col-3 p-4">

                        {!! Form::bsText('名稱', 'name', request()->input('name') ?? '', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        {!! Form::bsText('運費', 'value', request()->input('value') ?? '', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        {!! Form::bsText('滿足金額', 'required_value', request()->input('required_value') ?? '', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        {!! Form::bsSelect('運費類型', 'type', request()->input('type') ?? '', ShippingFee::getTypeList(), '全部', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        {!! Form::bsSelect('狀態', 'status', request()->input('status') ?? '', ShippingFee::getStatusLabels(), '全部', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        <br>

                        {!! Form::button('搜尋', ['type' => 'Submit', 'class' => 'btn btn-primary mt-2']) !!}

                    </div>


                </div>

            {!! Form::close() !!}

            {{-- table --}}
            {!! Form::open(['url' => route('admin.shippingFee.updateStatus'), 'method' => 'post', 'id' => 'form-shippingFee']) !!}

                @method('PUT')

                <div class="table-responsive">

                    {!! Form::button('開啓', ['class' => 'btn btn-success', 'type' => 'submit', 'value' => ShippingFee::STATUS_ON, 'name' => 'status']) !!}

                    {!! Form::button('關閉', ['class' => 'btn btn-danger', 'type' => 'submit', 'value' => ShippingFee::STATUS_OFF, 'name' => 'status']) !!}

                    {!! Form::button('刪除優惠券', ['class' => 'btn btn-warning', 'type' => 'button', 'id' => 'btn-delete-shippingFee']) !!}

                    <a href="{{ route('admin.shippingFee.create') }}" class="btn btn-info">新增運費規則</a>

                    <table class="table table-bordered mt-2" id="dataTable" width="100%" cellspacing="0">

                        <thead>

                            <tr>

                                <th>

                                    <input id="select-all" type="checkbox">

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.shippingFee.index', array_merge(request()->except('_token'), ['order_by' => 'id'])) }}">

                                        名稱

                                    </a>

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.shippingFee.index', array_merge(request()->except('_token'), ['order_by' => 'value'])) }}">

                                        運費

                                    </a>

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.shippingFee.index', array_merge(request()->except('_token'), ['order_by' => 'required_value'])) }}">

                                        滿足金額

                                    </a>

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.shippingFee.index', array_merge(request()->except('_token'), ['order_by' => 'type'])) }}">

                                        運費類型

                                    </a>

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.shippingFee.index', array_merge(request()->except('_token'), ['order_by' => 'status'])) }}">

                                        狀態

                                    </a>

                                </th>

                                <th></th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach ($shippingFeeList as $shippingFee)

                                <tr>

                                    <td>

                                        <input type="checkbox" name="id[]" value="{{ $shippingFee->id }}">

                                    </td>

                                    <td>{{ $shippingFee->name }}</td>

                                    <td>{{ $shippingFee->value }}</td>

                                    <td>{{ $shippingFee->required_value }}</td>

                                    <td>{{ ShippingFee::getTypeList()[$shippingFee->type] }}</td>

                                    <td>{{ ShippingFee::getStatusLabels()[$shippingFee->status] }}</td>

                                    <td style="width: 10%">

                                        <a class="btn btn-sm btn-danger mr-3" href="{{ route('admin.shippingFee.edit', ['shippingFee' => $shippingFee->id]) }}"><i class="far fa-edit"></i></a>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            {!! Form::close() !!}

            {{ $shippingFeeList->links() }}

        </div>

    </div>

@endsection
