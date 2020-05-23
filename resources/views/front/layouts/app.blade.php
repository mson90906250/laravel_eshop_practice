@php

    use App\Http\Controllers\Front\CartController;

    $cart = CartController::getCart();

@endphp

<!DOCTYPE html>

<html lang="en">

<!-- Basic -->

<head>

    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- Mobile Metas -->

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Site Metas -->

    <title>Freshshop - Ecommerce Bootstrap 4 HTML Template</title>

    <meta name="keywords" content="">

    <meta name="description" content="">

    <meta name="author" content="">

    <!-- Site Icons -->

    <link rel="shortcut icon" href="{{ asset('images/front/favicon.ico') }}" type="image/x-icon">

    <link rel="apple-touch-icon" href="{{ asset('images/front/apple-touch-icon.png') }}">

    <!-- Bootstrap CSS -->

    <link rel="stylesheet" href="{{ asset('css/front/freshshop/bootstrap.min.css') }}">

    <!-- Site CSS -->

    <link rel="stylesheet" href="{{ asset('css/front/freshshop/style.css') }}">

    <!-- Responsive CSS -->

    <link rel="stylesheet" href="{{ asset('css/front/freshshop/responsive.css') }}">

    <!-- Custom CSS -->

    <link rel="stylesheet" href="{{ asset('css/front/freshshop/custom.css') }}">

    <link rel="stylesheet" href="{{ asset('css/common.css') }}">

    {{-- date picker --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.0-alpha14/css/tempusdominus-bootstrap-4.min.css" />

    <style type="text/css">

    .nav-account-box {

        float: right;

        margin-left: 20px;

    }



    .nav-account {

        display: inline-block;

    }



    .nav-link-account {

        color: white;

    }



    </style>

</head>



<body>

    <!-- Start Main Top -->

    <div class="main-top">

        <div class="container-fluid">

            <div class="row">

                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">

                </div>

                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">

                    <div class="nav-account-box">

                        @guest

                            <li class="nav-item nav-account">

                                <a class="nav-link  nav-link-account" href="{{ route('login.showLoginForm') }}">{{ __('登入') }}</a>

                            </li>

                        @if (Route::has('register.showRegisterForm'))

                            <li class="nav-item nav-account">

                                <a class="nav-link nav-link-account" href="{{ route('register.showRegisterForm') }}">{{ __('註冊') }}</a>

                            </li>

                        @endif

                        @else

                            <li class="nav-item dropdown">

                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>

                                    <span style="color: #ffffff;
                                                font-weight: 700;
                                                font-size: 14px;">

                                                {{ sprintf('Hello! %s', Auth::user()->nickname) }}

                                    </span>

                                    <span class="caret"></span>

                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">

                                    <a class="dropdown-item" href="{{ route('user.index') }}">

                                        <i class="fa fa-user s_color"></i> My Account

                                    </a>

                                    <a class="dropdown-item" href="{{ route('login.logout') }}" onclick="event.preventDefault();

                                                         document.getElementById('logout-form').submit();">

                                        {{ __('Logout') }}

                                    </a>

                                    <form id="logout-form" action="{{ route('login.logout') }}" method="POST" style="display: none;">

                                        @csrf

                                    </form>

                                </div>

                            </li>

                        @endguest

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- End Main Top -->

    <!-- Start Main Top -->

    <header class="main-header">

        <!-- Start Navigation -->

        <nav class="navbar navbar-expand-lg navbar-light bg-light navbar-default bootsnav">

            <div class="container">

                <!-- Start Header Navigation -->

                <div class="navbar-header">

                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-menu" aria-controls="navbars-rs-food" aria-expanded="false" aria-label="Toggle navigation">

                        <i class="fa fa-bars"></i>

                    </button>

                    <a class="navbar-brand" href="index.html"><img src="{{ asset('images/front/logo.png') }}" class="logo" alt=""></a>

                </div>

                <!-- End Header Navigation -->

                <!-- Collect the nav links, forms, and other content for toggling -->

                <div class="collapse navbar-collapse" id="navbar-menu">

                    <ul class="nav navbar-nav ml-auto" data-in="fadeInDown" data-out="fadeOutUp">

                        <li class="nav-item {{ Route::currentRouteName() == 'shop.index' ? 'active' : '' }}"><a class="nav-link" href="{{ route('shop.index') }}">商店</a></li>

                    </ul>

                </div>

                <!-- /.navbar-collapse -->

                <!-- Start Atribute Navigation -->

                <div class="attr-nav">

                    <ul>

                        <li class="search"><a href="#"><i class="fa fa-search"></i></a></li>

                        <li class="side-menu">

                            <a href="#">

                                <i class="fa fa-shopping-bag"></i>

                                @if ($cart && !$cart->isEmpty())

                                    <span class="badge">

                                        {{ $cart->getContent()->count() }}

                                    </span>

                                @endif

                                <p>My Cart</p>

                            </a>

                        </li>

                    </ul>

                </div>

                <!-- End Atribute Navigation -->

            </div>

            <!-- Start Side Menu -->

            <div class="side">

                <a href="#" class="close-side"><i class="fa fa-times"></i></a>

                <li class="cart-box" style="max-height: 60vh; overflow:scroll">

                    <ul class="cart-list">

                        @if($cart && !$cart->isEmpty())

                            @foreach($cart->getContent() as $item)

                                <li>

                                    <a href="#" class="photo">

                                        <img src="{{ asset($item->attributes->image) }}" class="cart-thumb" alt="" />

                                    </a>

                                    <h6>

                                        <a href="{{ route('shop.show', ['product' => $item->attributes->product['id']]) }}">{{ $item->name }}</a> <br>

                                        {{ $item->attributes->stock['description'] }}

                                    </h6>

                                    <p>{{ sprintf('%dx', $item->quantity) }} - <span class="price">{{ sprintf('$%d', $item->price) }}</span></p>

                                </li>

                            @endforeach

                        @else

                            <li>

                                <h6>目前沒有商品</h6>

                            </li>

                        @endif

                    </ul>

                </li>

                @if ($cart && !$cart->isEmpty())

                    <a href="{{ route('cart.index') }}" class="btn btn-info" style="display: block; width: 33%; margin: auto" >去結賬</a>

                @endif

            </div>

            <!-- End Side Menu -->

        </nav>

        <!-- End Navigation -->

    </header>

    <!-- End Main Top -->

    @yield('content')

    <!-- Start Footer  -->

    <footer>

        <div class="footer-main">

            <div class="container">

                <div class="row">

                    <div class="col-lg-4 col-md-12 col-sm-12">

                        <div class="footer-widget">

                            <h4>About Freshshop</h4>

                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>

                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. </p>

                        </div>

                    </div>

                    <div class="col-lg-4 col-md-12 col-sm-12">

                        <div class="footer-link-contact">

                            <h4>Contact Us</h4>

                            <ul>

                                <li>

                                    <p><i class="fas fa-map-marker-alt"></i>Address: Michael I. Days 3756 <br>Preston Street Wichita,<br> KS 67213 </p>

                                </li>

                                <li>

                                    <p><i class="fas fa-phone-square"></i>Phone: <a href="tel:+1-888705770">+1-888 705 770</a></p>

                                </li>

                                <li>

                                    <p><i class="fas fa-envelope"></i>Email: <a href="mailto:contactinfo@gmail.com">contactinfo@gmail.com</a></p>

                                </li>

                            </ul>

                        </div>

                    </div>

                    <div class="col-lg-4 col-md-12 col-sm-12">

                        <div class="footer-widget">

                            <h4>Business Time</h4>

                            <ul>

                                <li><p>Monday - Friday: 08.00am to 05.00pm</p></li>

                                <li><p>Saturday: 10.00am to 08.00pm</p></li>

                                <li><p>Sunday: <span>Closed</span></p></li>

                            </ul>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </footer>

    <!-- End Footer  -->

    <!-- Start copyright  -->

    <div class="footer-copyright">

        <p class="footer-company">All Rights Reserved. &copy; 2018 <a href="#">ThewayShop</a> Design By :

            <a href="https://html.design/">html design</a></p>

    </div>

    <!-- End copyright  -->

    <a href="#" id="back-to-top" title="Back to top" style="display: none;">&uarr;</a>

    @section('scripts')

    <!-- ALL JS FILES -->
    <script src="{{ asset('js/front/freshshop/jquery-3.2.1.min.js') }}"></script>

    <script src="{{ asset('js/front/freshshop/popper.min.js') }}"></script>

    <script src="{{ asset('js/front/freshshop/bootstrap.min.js') }}"></script>

    <!-- ALL PLUGINS -->
    <script src="{{ asset('js/front/freshshop/jquery.superslides.min.js') }}"></script>

    <script src="{{ asset('js/front/freshshop/bootstrap-select.js') }}"></script>

    <script src="{{ asset('js/front/freshshop/inewsticker.js') }}"></script>

    <script src="{{ asset('js/front/freshshop/bootsnav.js') }}"></script>

    <script src="{{ asset('js/front/freshshop/images-loded.min.js') }}"></script>

    <script src="{{ asset('js/front/freshshop/isotope.min.js') }}"></script>

    <script src="{{ asset('js/front/freshshop/owl.carousel.min.js') }}"></script>

    <script src="{{ asset('js/front/freshshop/baguetteBox.min.js') }}"></script>

    <script src="{{ asset('js/front/freshshop/jquery-ui.js') }}"></script>

    <script src="{{ asset('js/front/freshshop/jquery.nicescroll.min.js') }}"></script>

    <script src="{{ asset('js/front/freshshop/form-validator.min.js') }}"></script>

    <script src="{{ asset('js/front/freshshop/contact-form-script.js') }}"></script>

    <script src="{{ asset('js/front/freshshop/custom.js') }}"></script>

    {{-- date picker --}}
    <script src="{{ asset('js/moment/moment.js') }}"></script>

    <script src="{{ asset('js/moment/locale/zh-tw.js') }}"></script>

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.0-alpha14/js/tempusdominus-bootstrap-4.min.js"></script>

    <script>

        //用來解決圖片顯示錯誤
        window.addEventListener("load", function(event) {

            document.querySelectorAll('img').forEach(function(img){

                if (!img.complete || img.naturalWidth == 0) {

                    img.src="{{ asset(Config::get('custom.no_image_url')) }}";

                }

                img.onerror = function(){

                    img.src="{{ asset(Config::get('custom.no_image_url')) }}";

                };

            });

        });


    </script>

    @show

</body>



</html>

