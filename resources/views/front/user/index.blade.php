@extends('front.layouts.app')

@section('content')

    @include('front.includes.titlebox', [
        'title' => '我的帳號'
    ])

    <!-- Start My Account  -->
    <div class="my-account-box-main">

        <div class="container">

            @include('front.includes.alert')

            @include('front.includes.breadcrumb', [
                'data' => [
                    '我的帳號'  => ''
                ]
            ])

            <div class="my-account-page">

                <div class="row">

                    <div class="col-lg-4 col-md-12">

                        <div class="account-box">

                            <div class="service-box">

                                <div class="service-icon">

                                    <a href="{{ route('order.index') }}"> <i class="fa fa-gift"></i> </a>

                                </div>

                                <div class="service-desc">

                                    <h4>我的訂單</h4>

                                    <p>Track, return, or buy things again</p>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="col-lg-4 col-md-12">

                        <div class="account-box">

                            <div class="service-box">

                                <div class="service-icon">

                                    <a href="{{ route('user.show') }}"><i class="fa fa-lock"></i> </a>

                                </div>

                                <div class="service-desc">

                                    <h4>個人資訊</h4>

                                    <p>Edit login, name, and mobile number</p>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="col-lg-4 col-md-12">

                        <div class="account-box">

                            <div class="service-box">

                                <div class="service-icon">

                                    <a href="{{ route('wishList.index') }}"> <i class="fa fa-location-arrow"></i> </a>

                                </div>

                                <div class="service-desc">

                                    <h4>願望清單</h4>

                                    <p>Edit addresses for orders and gifts</p>

                                </div>

                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End My Account -->

@endsection
