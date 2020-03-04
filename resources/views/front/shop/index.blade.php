@extends('front.layouts.app')

@section('scripts')

    @parent

    <script type="text/javascript">

        // 使用分頁時,可以自動切換grid-view和list-view
        // TODO: 改成不用trigger的方式
        var currentUrlHash = window.location.hash;

        $("a[href = '"+ currentUrlHash +"']").trigger('click');

    </script>

@endsection

@section('content')

@include('front.includes.titlebox')

<!-- Start Shop Page  -->

<div class="shop-box-inner">

    <div class="container">

        @include('front.includes.alert')

        @include('front.includes.breadcrumb', ['data' => [

            'Shop' => NULL

        ]])

        <div class="row">

            <div class="col-xl-9 col-lg-9 col-sm-12 col-xs-12 shop-content-right">

                <div class="right-product-box">

                    <div class="product-item-filter row">

                        <div class="col-12 col-sm-8 text-center text-sm-left">

                            <p style="float: left">{{ sprintf('Showing all %d results', $products->total()) }}</p>

                        </div>

                        <div class="col-12 col-sm-4 text-center text-sm-right">

                            <ul class="nav nav-tabs ml-auto">

                                <li>

                                    <a class="nav-link active" href="#grid-view" data-toggle="tab"> <i class="fa fa-th"></i> </a>

                                </li>

                                <li>

                                    <a class="nav-link" href="#list-view" data-toggle="tab"> <i class="fa fa-list-ul"></i> </a>

                                </li>

                            </ul>

                        </div>

                    </div>

                    <div class="product-categorie-box">

                        <div class="tab-content">

                            @include('front.includes.gridview')

                            @include('front.includes.listview')

                        </div>

                    </div>

                </div>

            </div>

            <div class="col-xl-3 col-lg-3 col-sm-12 col-xs-12 sidebar-shop-left">

                @include('front.includes.category')

            </div>

        </div>

    </div>

</div>

<!-- End Shop Page -->

@endsection

