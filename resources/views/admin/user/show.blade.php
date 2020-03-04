@php

    use App\Models\User;
    use App\Helper\DetailView;

@endphp

@section('styles')

    @parent

    {{-- date picker --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.0-alpha14/css/tempusdominus-bootstrap-4.min.css" />

    <link href="{{ asset('css/admin/dataTables.bootstrap4.min.css') }}"
        rel="stylesheet">


@endsection

@extends('admin.layouts.app')

@section('content')

    @include('admin.includes.alert')

    <!-- DataTales Example -->
    <div class="card shadow mb-4">

        <div class="card-header py-3">

            <h1 class="m-0 font-weight-bold text-primary">用戶清單</h1>

            @include('admin.includes.breadcrumb', [
                'data' => [
                    '用戶清單' => route('admin.user.index'),
                    sprintf('用戶: %s', $user->nickname) => ''
                ]
            ])

        </div>

        <div class="card-body">

            {!!
                DetailView::get($user, [
                    'table' => [
                        'class' => 'table table-bordered mt-2',
                        'width' => '100%',
                        'cellspacing' => 0
                    ],
                    'columns' => array_keys(User::getAttributeLabelsForShow())
                ])

            !!}

        </div>

    </div>

@endsection
