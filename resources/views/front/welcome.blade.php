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
    <link rel="shortcut icon" href="{{ asset('images/front/favicon.ico" type="image/x-icon') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/front/apple-touch-icon.png') }}">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('css/front/freshshop/bootstrap.min.css') }}">
    <!-- Site CSS -->
    <link rel="stylesheet" href="{{ asset('css/front/freshshop/style.css') }}">
    <!-- Responsive CSS -->
    <link rel="stylesheet" href="{{ asset('css/front/freshshop/responsive.css') }}">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/front/freshshop/custom.css') }}">

    <style type="text/css">

        a:hover {
            color: white
        }

    </style>

</head>

<body>

<!-- Start Slider -->
    <div id="slides-shop" class="cover-slides">
        <ul class="slides-container">
            <!-- TODO 改成後臺設置  -->
            @for($i = 1; $i <= 3; $i++)
                <li class="text-center">
                    <img src="{{ asset(sprintf('images/front/banner-0%d.jpg', $i)) }}" alt="">
                </li>
            @endfor

        </ul>
        <div class="slides-navigation" style="z-index: 100">
            <a href="#" class="next"><i class="fa fa-angle-right" aria-hidden="true"></i></a>
            <a href="#" class="prev"><i class="fa fa-angle-left" aria-hidden="true"></i></a>
        </div>
    </div>
    <!-- TODO 改成後臺設置  -->
    <div style="position: fixed; top: 29%; width: 100%; z-index: 10">
        <p style="text-align: center; font-size: 5em; color: #E6BF78; line-height: 1.2em"><strong>{!! $title !!}</strong></p>
        <p style="padding-left: 33%; padding-right: 33%; color: #EBDFAE">{{ $description }}</p>
        <p style="text-align: center; margin-top: 15px"><a class="btn hvr-hover" href="{{ route('shop.index') }}">進入商店</a></p>
    </div>
<!-- End Slider -->

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
    <script src="{{ asset('js/front/freshshop/form-validator.min.js') }}"></script>
    <script src="{{ asset('js/front/freshshop/contact-form-script.js') }}"></script>
    <script src="{{ asset('js/front/freshshop/custom.js') }}"></script>

</body>
