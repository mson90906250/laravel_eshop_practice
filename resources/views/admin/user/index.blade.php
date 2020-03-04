@php

    use App\Models\User;

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

            //---排序用
            var orderAttribute = "{{ request()->input('order_by') }}";

            var isAsc = "{{ request()->input('is_asc') }}";

            $('a.sort-group').each(function () {

                var selfUrl = new URL($(this).attr('href'));

                if (orderAttribute === selfUrl.searchParams.get('order_by').replace('-', '')) {

                    if (isAsc == 1) {

                        $(this).addClass('sort-asc');

                        $(this).attr('href', "{!! route('admin.user.index', array_merge(request()->except('_token'), ['order_by' => request()->input('order_by'), 'is_asc' => 0])) !!}");

                    } else {

                        $(this).addClass('sort-desc');

                        $(this).attr('href', "{!! route('admin.user.index', array_merge(request()->except('_token'), ['order_by' => request()->input('order_by'), 'is_asc' => 1])) !!}");

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

            <h1 class="m-0 font-weight-bold text-primary">用戶清單</h1>

            @include('admin.includes.breadcrumb', [
                'data' => [
                    '用戶清單' => ''
                ]
            ])

        </div>

        <div class="card-body">

            {!! Form::open(['url' => route('admin.user.index'), 'method' => 'GET']) !!}

                <div class="row mb-3">

                    <div class="col-3 p-4">

                        {!! Form::bsText('暱稱', 'nickname', request()->input('nickname') ?? '', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        {!! Form::bsText('信箱', 'email', request()->input('email') ?? '', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        <br>

                        {!! Form::button('搜尋', ['type' => 'Submit', 'class' => 'btn btn-primary mt-2']) !!}

                    </div>


                </div>

            {!! Form::close() !!}

            <div class="table-responsive">

                <table class="table table-bordered mt-2" id="dataTable" width="100%" cellspacing="0">

                    <thead>

                        <tr>

                            <th>

                                <a class="sort-group" href="{{ route('admin.user.index', array_merge(request()->except('_token'), ['order_by' => 'id'])) }}">

                                    ID

                                </a>

                            </th>

                            <th>

                                <a class="sort-group" href="{{ route('admin.user.index', array_merge(request()->except('_token'), ['order_by' => 'nickname'])) }}">

                                    暱稱

                                </a>

                            </th>

                            <th>

                                <a class="sort-group" href="{{ route('admin.user.index', array_merge(request()->except('_token'), ['order_by' => 'email'])) }}">

                                    email

                                </a>

                            </th>

                            <th></th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach ($userList as $user)

                            <tr>

                                <td>{{ $user->id }}</td>

                                <td>{{ $user->nickname }}</td>

                                <td>{{ $user->email }}</td>

                                <td style="width: 10%">

                                    <a class="btn btn-sm btn-info mr-3" href="{{ route('admin.user.show', ['user' => $user->id]) }}"><i class="far fa-eye"></i></a>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            {{ $userList->links() }}

        </div>

    </div>

@endsection
