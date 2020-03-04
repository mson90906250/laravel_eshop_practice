@php

    use App\Models\Comment;

@endphp

@extends('admin.layouts.app')

@section('styles')

    @parent

    <link href="{{ asset('css/admin/dataTables.bootstrap4.min.css') }}"
        rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.3.1/css/select.dataTables.css"/>

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
    {{--  --}}

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

            //刪除評論
            $('#form-comment').on('submit', function () {

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

                        $(this).attr('href', "{!! route('admin.comment.index', array_merge(request()->except('_token'), ['order_by' => request()->input('order_by'), 'is_asc' => 0])) !!}");

                    } else {

                        $(this).addClass('sort-desc');

                        $(this).attr('href', "{!! route('admin.comment.index', array_merge(request()->except('_token'), ['order_by' => request()->input('order_by'), 'is_asc' => 1])) !!}");

                    }

                }

            });
            //--

            //--date picker
            $('#datetimepicker1').datetimepicker({
                format: 'YYYY-MM-DD',
            });

            $('#datetimepicker2').datetimepicker({
                format: 'YYYY-MM-DD',
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

            <h1 class="m-0 font-weight-bold text-primary">評論列表</h1>

            @include('admin.includes.breadcrumb', [
                'data' => [
                    '評論列表' => ''
                ]
            ])

        </div>

        <div class="card-body">

            {{-- search --}}
            {!! Form::open(['url' => route('admin.comment.index'), 'method' => 'GET']) !!}

                <div class="row mb-3">

                    <div class="col-3 p-4">

                        {!! Form::bsText('評論内容', 'content', request()->input('content') ?? '', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        {!! Form::bsText('用戶名', 'nickname', request()->input('nickname') ?? '', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        {!! Form::bsText('商品名', 'product_name', request()->input('product_name') ?? '', 'vertical') !!}

                    </div>

                    <div class="col-6 p-4">

                        <div class="row">

                            <div class="col-9">

                                <label>發表日期</label>

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
            {{-- end --}}

            {{-- content --}}
            {!! Form::open(['url' => route('admin.comment.destroy'), 'method' => 'POST', 'id' => 'form-comment']) !!}

                @method('DELETE')

                <div class="table-responsive">

                    {{ Form::button('刪除評論', ['id' => 'button-delete-admins', 'class' => 'btn btn-warning', 'type' => 'Submit']) }}

                    <table class="table table-bordered mt-2" id="dataTable" width="100%" cellspacing="0">

                        <thead>

                            <tr>

                                <th>

                                    <input id="select-all" type="checkbox" style="width: 100%">

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.comment.index', array_merge(request()->except('_token'), ['order_by' => 'product_id'])) }}">

                                        商品名稱

                                    </a>

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.comment.index', array_merge(request()->except('_token'), ['order_by' => 'user_id'])) }}">

                                        用戶名稱

                                    </a>

                                </th>

                                <th>

                                    内容

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.comment.index', array_merge(request()->except('_token'), ['order_by' => 'updated_at'])) }}">

                                        發表日期

                                    </a>

                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach ($commentList as $item)

                                <tr>

                                    <td style="width: 5%">

                                        <input type="checkbox" name="id[]" value="{{ $item->id }}" style="width: 100%">

                                    </td>

                                    <td>

                                        <a href="{{ route('admin.product.show', ['product' => $item->product_id]) }}">{{ $item->product_name }}</a>

                                    </td>

                                    <td>

                                        <a href="{{ route('admin.user.show', ['user' => $item->user_id]) }}">{{ $item->user_name }}</a>

                                    </td>

                                    <td>

                                        <p style="white-space: pre-wrap">{{ $item->content }}</p>

                                    </td>

                                    <td>

                                        {{ $item->updated_at }}

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            {!! Form::close() !!}
            {{-- end --}}

            {{ $commentList->links() }}

        </div>

    </div>

@endsection
