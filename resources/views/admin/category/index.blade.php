@php

    use App\Models\Category;

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

            //選擇全部
            $('#select-all').on('click', function () {

                if (this.checked) {

                    $('tbody input[type="checkbox"]').not(':checked').trigger('click');

                } else {

                    $('tbody input[type="checkbox"]:checked').trigger('click');

                }

            });
            //--

            //刪除類型
            $('#form-category').on('submit', function () {

                return confirm('確定要刪除嗎?');

            });
            //--

            //排序
            var orderAttribute = "{{ request()->input('order_by') }}";

            var isAsc = "{{ request()->input('is_asc') }}";

            $('a.sort-group').each(function () {

                var selfUrl = new URL($(this).attr('href'));

                if (orderAttribute === selfUrl.searchParams.get('order_by').replace('-', '')) {

                    if (isAsc == 1) {

                        $(this).addClass('sort-asc');

                        $(this).attr('href', "{!! route('admin.category.index', array_merge(request()->except('_token'), ['order_by' => request()->input('order_by'), 'is_asc' => 0])) !!}");

                    } else {

                        $(this).addClass('sort-desc');

                        $(this).attr('href', "{!! route('admin.category.index', array_merge(request()->except('_token'), ['order_by' => request()->input('order_by'), 'is_asc' => 1])) !!}");

                    }

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

            <h1 class="m-0 font-weight-bold text-primary">商品類型列表</h1>

            @include('admin.includes.breadcrumb', [
                'data' => [
                    '商品類型列表' => ''
                ]
            ])

        </div>

        <div class="card-body">

            {{-- search --}}
            {!! Form::open(['url' => route('admin.category.index'), 'method' => 'GET']) !!}

                <div class="row mb-3">

                    <div class="col-3 p-4">

                        {!! Form::bsSelect('類型', 'id', request()->input('id') ?? '', Category::getSelectOptions(FALSE), '全部', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        {!! Form::bsSelect('主類型', 'parent_id', request()->input('parent_id') ?? '', Category::getSelectOptions(TRUE), '全部', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        <br>

                        {!! Form::button('搜尋', ['type' => 'Submit', 'class' => 'btn btn-primary mt-2']) !!}

                    </div>


                </div>

            {!! Form::close() !!}
            {{-- end --}}

            {{-- content --}}
            {!! Form::open(['url' => route('admin.category.destroy'), 'method' => 'POST', 'id' => 'form-category']) !!}

                @method('DELETE')

                <div class="table-responsive">

                    {{ Form::button('刪除類型', ['id' => 'button-delete-category', 'class' => 'btn btn-warning', 'type' => 'Submit']) }}

                    <a href="{{ route('admin.category.create') }}" class="btn btn-primary">新增類型</a>

                    <table class="table table-bordered mt-2" id="dataTable" width="100%" cellspacing="0">

                        <thead>

                            <tr>

                                <th>

                                    <input id="select-all" type="checkbox" style="width: 100%">

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.category.index', array_merge(request()->except('_token'), ['order_by' => 'id'])) }}">

                                        名稱

                                    </a>

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.category.index', array_merge(request()->except('_token'), ['order_by' => 'parent_id'])) }}">

                                        主類型

                                    </a>

                                </th>

                                <th></th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach ($categoryList as $item)

                                <tr>

                                    <td style="width: 5%">

                                        <input type="checkbox" name="id[]" value="{{ $item->id }}" style="width: 100%">

                                    </td>

                                    <td>{{ $item->name }}</td>

                                    <td>{{ $item->parent ? $item->parent->name : '無'  }}</td>

                                    <td style="width: 10%">

                                        <a class="btn btn-sm btn-danger" href="{{ route('admin.category.edit', ['category' => $item->id]) }}"><i class="fas fa-pen"></i></a>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            {!! Form::close() !!}
            {{-- end --}}

            {{ $categoryList->links() }}

        </div>

    </div>

@endsection
