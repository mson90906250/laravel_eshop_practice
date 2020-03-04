@extends('front.layouts.app')

@section('content')

    @include('front.includes.titlebox', [
        'title' => '用戶資訊'
    ])

    <!-- Start Order  -->
    <div class="my-account-box-main">

        <div class="container">

            @include('front.includes.alert')

            @include('front.includes.breadcrumb', [
                'data' => [
                    '我的帳號' => route('user.index'),
                    '用戶資訊'  => '',
                ]
            ])

            <div style="padding:0px 20px">

                <div class="row">

                    <div class="col-6">

                        <ul>

                            <li>{{ sprintf('昵稱: %s', $user->nickname) }}</li>

                            <li>{{ sprintf('姓名: %s', $user->full_name) }}</li>

                            <li>{{ sprintf('信箱: %s', $user->email) }}</li>

                            <li>{{ sprintf('電話: %s', $user->phone_number) }}</li>

                            <li>{{ sprintf('住址: %s', $user->full_address) }}</li>

                        </ul>

                    </div>

                    <div class="col-6">

                        <a class="btn btn-sm btn-info" href="{{ route('user.edit') }}">修改用戶資料</a>

                        <a class="btn btn-sm btn-danger" href="{{ route('resetPassword.reset') }}">修改密碼</a>

                    </div>


                </div>



            </div>

        </div>

    </div>

    <!-- End Order -->


@endsection
