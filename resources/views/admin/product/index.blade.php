@php

    use App\Models\Product;
    use App\Models\Category;
    use App\Models\Brand;

@endphp

@extends('admin.layouts.app')

@section('styles')

    {{-- TODO: 繼續調整js 及 css --}}

    @parent

    {{-- date picker --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.0-alpha14/css/tempusdominus-bootstrap-4.min.css" />

    <link href="{{ asset('css/admin/dataTables.bootstrap4.min.css') }}"
        rel="stylesheet">

@endsection

@section('scripts')

    @parent

    {{-- date picker --}}
    <script src="{{ asset('js/moment/moment.js') }}"></script>

    <script src="{{ asset('js/moment/locale/zh-tw.js') }}"></script>

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.0-alpha14/js/tempusdominus-bootstrap-4.min.js"></script>

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

                        $(this).attr('href', "{!! route('admin.product.index', array_merge(request()->except('_token'), ['order_by' => request()->input('order_by'), 'is_asc' => 0])) !!}");

                    } else {

                        $(this).addClass('sort-desc');

                        $(this).attr('href', "{!! route('admin.product.index', array_merge(request()->except('_token'), ['order_by' => request()->input('order_by'), 'is_asc' => 1])) !!}");

                    }

                }

            });

            //當按倒刪除群組按鈕時要更動 action 及 method
            $('#btn-delete-product').on('click', function () {

                if (confirm('確定要刪除嗎?')) {

                    $('input[name="_method"][type="hidden"]').val('DELETE');

                    $('#form-product').attr('action', '{{ route("admin.product.destroy") }}');

                    $('#form-product').submit();

                }

            });

             //選擇全部checkbox
             $('#select-all').on('click', function () {

                if (this.checked) {

                    $('tbody input[type="checkbox"]').not(':checked').trigger('click');

                } else {

                    $('tbody input[type="checkbox"]:checked').trigger('click');

                }

            });

            //--date picker
            $('#datetimepicker1').datetimepicker({
                format: 'YYYY-MM-DD',
            });

            $('#datetimepicker2').datetimepicker({
                format: 'YYYY-MM-DD',
            });

        });

    </script>

@endsection

@section('content')

    @include('admin.includes.alert')

    <!-- DataTales Example -->
    <div class="card shadow mb-4">

        <div class="card-header py-3">

            <h1 class="m-0 font-weight-bold text-primary">商品清單</h1>

            @include('admin.includes.breadcrumb', [
                'data' => [
                    '商品清單' => ''
                ]
            ])

        </div>

        <div class="card-body">

            {{-- search --}}
            {!! Form::open(['url' => route('admin.product.index'), 'method' => 'GET']) !!}

                <div class="row mb-3">

                    <div class="col-3 p-4">

                        {!! Form::bsText('商品名稱', 'name', request()->input('name') ?? '', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        {!! Form::bsSelect('種類', 'category_id', request()->input('category_id') ?? '', Category::getSelectOptions(), '全部', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        {!! Form::bsSelect('品牌', 'brand_id', request()->input('brand_id') ?? '', Brand::getSelectOptions(), '全部', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        {!! Form::bsSelect('狀態', 'status', request()->input('status') ?? '', Product::getStatusLabels(), '全部', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        {!! Form::bsText('原價', 'original_price', request()->input('original_price') ?? '', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        <div class="row">

                            <div class="col-9">

                                <label>價格範圍</label>

                            </div>

                            <div class="col-5">

                                <input name="price_range[min]" type="number" min="0" class="form-control" value="{{ request()->input('price_range.min') }}" placeholder="最小值">

                            </div>

                            <div class="col-5">

                                <input name="price_range[max]" type="number" min="0" class="form-control" value="{{ request()->input('price_range.max') }}" placeholder="最大值">

                            </div>

                        </div>

                    </div>

                    <div class="col-6 p-4">

                        <div class="row">

                            <div class="col-9">

                                <label>上架日期</label>

                            </div>

                            <div class="col-5">

                                <div class="input-group date" id="datetimepicker1" data-target-input="nearest">

                                    <input type="text" class="form-control datetimepicker-input" data-target="#datetimepicker1" name="date_start" value="{{ request()->input('date_start') }}">

                                    <div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker">

                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>

                                    </div>

                                </div>

                            </div>

                            <span style="font-size: 20px">~</span>

                            <div class="col-5">

                                <div class="input-group date" id="datetimepicker2" data-target-input="nearest">

                                    <input type="text" class="form-control datetimepicker-input" data-target="#datetimepicker2" name="date_end" value="{{ request()->input('date_end') }}">

                                    <div class="input-group-append" data-target="#datetimepicker2" data-toggle="datetimepicker">

                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <div class="col-3 p-4">

                        <br>

                        {!! Form::button('搜尋', ['type' => 'Submit', 'class' => 'btn btn-primary mt-2']) !!}

                    </div>


                </div>

            {!! Form::close() !!}

            {{-- table --}}
            {!! Form::open(['url' => route('admin.product.updateStatus'), 'method' => 'post', 'id' => 'form-product']) !!}

                @method('PUT')

                <div class="table-responsive">

                    {!! Form::button('上架', ['class' => 'btn btn-success', 'type' => 'submit', 'value' => Product::STATUS_ON, 'name' => 'status']) !!}

                    {!! Form::button('下架', ['class' => 'btn btn-danger', 'type' => 'submit', 'value' => Product::STATUS_OFF, 'name' => 'status']) !!}

                    {!! Form::button('選擇刪除已下架的商品', ['class' => 'btn btn-warning', 'type' => 'button', 'id' => 'btn-delete-product']) !!}

                    <a href="{{ route('admin.product.create') }}" class="btn btn-info">新增商品</a>

                    <table class="table table-bordered mt-2" id="dataTable" width="100%" cellspacing="0">

                        <thead>

                            <tr>

                                <th>

                                    <input id="select-all" type="checkbox">

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.product.index', array_merge(request()->except('_token'), ['order_by' => 'name'])) }}">

                                        商品名稱

                                    </a>

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.product.index', array_merge(request()->except('_token'), ['order_by' => 'brand_id'])) }}">

                                        品牌

                                    </a>

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.product.index', array_merge(request()->except('_token'), ['order_by' => 'category_id'])) }}">

                                        種類

                                    </a>

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.product.index', array_merge(request()->except('_token'), ['order_by' => 'original_price'])) }}">

                                        原價

                                    </a>

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.product.index', array_merge(request()->except('_token'), ['order_by' => 'price_range'])) }}">

                                        價格範圍

                                    </a>

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.product.index', array_merge(request()->except('_token'), ['order_by' => 'status'])) }}">

                                        狀態

                                    </a>

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.product.index', array_merge(request()->except('_token'), ['order_by' => 'created_at'])) }}">

                                        上架日期

                                    </a>

                                </th>

                                <th></th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach ($productList as $product)

                                <tr>

                                    <td>

                                        <input type="checkbox" name="id[]" value="{{ $product->id }}">

                                    </td>

                                    <td>{{ $product->name }}</td>

                                    <td>{{ $product->brand }}</td>

                                    <td>{{ $product->category }}</td>

                                    <td>{{ $product->original_price }}</td>

                                    <td>{{ $product->price_range }}</td>

                                    <td>{{ Product::getStatusLabels()[$product->status] }}</td>

                                    <td>{{ $product->created_at }}</td>

                                    <td style="width: 10%">

                                        <a class="btn btn-sm btn-info mr-3" href="{{ route('admin.product.show', ['product' => $product->id]) }}"><i class="far fa-eye"></i></a>

                                        <a class="btn btn-sm btn-danger mr-3" href="{{ route('admin.product.edit', ['product' => $product->id]) }}"><i class="far fa-edit"></i></a>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            {!! Form::close() !!}

            {{ $productList->links() }}

        </div>

    </div>

@endsection
