@extends('front.layouts.app')

@section('scripts')

    @parent

    <script>

        var updateFromData = [];

        $("#update-cart").on("click", function(){

            var form = $("#cart-update-form");

            $(".cart-update-input").each(function(index, item){

                var cloneItem = $(item).clone();

                $(cloneItem).attr('type', 'hidden');

                form.append(cloneItem);

            });

            form.submit();

        });

    </script>

    <script>

        $(function(){

            $('#coupon-submit').on('click', function(){

                var checkedCoupon = $('input[type="radio"][name="coupon"]:checked');

                var code = checkedCoupon.data('code');

                if (code != undefined) {

                    $('#coupon-code-input').val(code);

                    $('#add-coupon-form').submit();

                }

            });

        });

    </script>

@endsection

@section('content')

@include('front.includes.titlebox', [
    'title' => 'Cart'
])

<!-- Start Cart  -->

<div class="cart-box-main">

    <div class="container">

        @include('front.includes.alert')

        @include('front.includes.breadcrumb', [
            'data' => [
                'Shop'  => route('shop.index'),
                'Cart'  => ''
            ]
        ])

        @if($cart && !$cart->isEmpty())

            <div class="row">

                <div class="col-lg-12">

                    <div class="table-main table-responsive" style="max-height: 60vh">

                        <table class="table">

                            <thead>

                                <tr>

                                    <th>圖片</th>

                                    <th>商品名</th>

                                    <th>價錢</th>

                                    <th>數量</th>

                                    <th>小計</th>

                                    <th></th>

                                </tr>

                            </thead>

                            <tbody>

                                @foreach($cart->getContent() as $item)

                                    <tr>

                                        <td class="thumbnail-img">

                                            <a href="{{ route('shop.show', ['product' => $item->attributes->product['id']]) }}">

                                                <img class="img-fluid" src="{{ asset($item->attributes->stock['image']) }}" alt="" />

                                            </a>

                                        </td>

                                        <td class="name-pr">

                                            <a href="{{ route('shop.show', ['product' => $item->attributes->product['id']]) }}">

                                                {{ $item->name }}

                                            </a>

                                            <p>

                                                {{ $item->attributes->stock['description'] }}

                                            </p>

                                        </td>

                                        <td class="price-pr">

                                            <p>{{ sprintf('$ %d', $item->price) }}</p>

                                        </td>

                                        <td class="quantity-box">

                                            <input class="cart-update-input"
                                                    type="number"
                                                    size="4"
                                                    value="{{ $item->quantity }}"
                                                    min="0"
                                                    max="{{ $item->attributes->stock['maxQuantity'] }}"
                                                    step="1"
                                                    class="c-input-text qty text"
                                                    name="item[{{ $item->id }}]">

                                        </td>

                                        <td class="total-pr">

                                            <p>{{ sprintf('$ %d', $item->getPriceSum()) }}</p>

                                        </td>

                                        <td class="remove-pr">

                                            <form method="POST" action="{{ route('cart.destroy') }}">

                                                @csrf

                                                @method('DELETE')

                                                <input type="hidden" name="rowId" value="{{ $item->id }}">

                                                <button type="submit" class="fas fa-times"></button>

                                            </form>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                        <form id="cart-update-form" method="POST" action="{{ route('cart.update') }}" style="display: hidden">

                            @csrf

                            @method('PUT')

                        </form>

                    </div>

                </div>

            </div>

            <div class="row my-5">

                {{-- 優惠券 --}}
                <div class="col-lg-6 col-sm-6">

                    <div class="coupon-box">

                        <form id="add-coupon-form" action="{{ route('cart.addCoupon') }}" method="POST">

                            @csrf

                            <div class="input-group input-group-sm">

                                <input id="coupon-code-input" class="form-control" value="{{ $ownedCoupon ? $ownedCoupon->title : '' }}" placeholder="請輸入優惠券代碼" aria-label="Coupon code" type="text" name="code" readonly>

                                <div class="input-group-append">

                                    <button type="button" class="btn btn-theme" data-toggle="modal" data-target="#coupon-list">選擇可用的優惠券</button>

                                </div>

                            </div>

                            <!-- Modal -->
                            <div class="modal fade" id="coupon-list" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">

                                <div class="modal-dialog" role="document">

                                    <div class="modal-content">

                                        <div class="modal-header">


                                            <h5 class="modal-title" id="exampleModalLabel">可用的優惠券</h5>

                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">

                                                <span aria-hidden="true">&times;</span>

                                            </button>

                                        </div>

                                        <div class="modal-body">

                                            {{-- TODO:顯示優惠券清單 --}}
                                            @if (!$couponList->isEmpty())

                                                @foreach ($couponList as $coupon)

                                                    <div class="custom-control custom-radio">

                                                        <input type="radio"
                                                                id="{{sprintf('cp-%d', $coupon->id)}}"
                                                                name="coupon"
                                                                class="custom-control-input"
                                                                value="{{ $coupon->id }}"
                                                                data-code="{{ $coupon->code }}"
                                                                {{ $ownedCoupon && $coupon->id == $ownedCoupon->id ? "checked" : "" }}>

                                                        <label class="custom-control-label mb-3" for="{{sprintf('cp-%d', $coupon->id)}}">

                                                            <h3 class="mb-1">{{ $coupon->title }}</h3>

                                                            <p>{{ sprintf('代碼:%s  剩餘次數:%d', $coupon->code, $coupon->remain) }}</p>

                                                        </label>

                                                    </div>

                                                @endforeach

                                            @else

                                                目前無可用的優惠券

                                            @endif


                                        </div>

                                        <div class="modal-footer">

                                            <button type="button" class="btn btn-primary" id="coupon-submit" data-dismiss="modal">確定</button>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </form>

                    </div>

                </div>

                <div class="col-lg-6 col-sm-6">

                    <div class="update-box">

                        <button type="submit" class="btn" style="color: white" id="update-cart">更新購物車</button>

                    </div>

                </div>

            </div>

            <div class="row my-5">

                <div class="col-lg-8 col-sm-12"></div>

                <div class="col-lg-4 col-sm-12">

                    <div class="order-box">

                        <h3>訂單總結</h3>

                        <div class="d-flex">

                            <h4>小計</h4>

                        <div class="ml-auto font-weight-bold"> {{ sprintf('$ %d', $subTotal) }} </div>

                        </div>

                        @if ($hasCoupon)

                            <hr class="my-1">

                            <div class="d-flex">

                                <h4>優惠券折抵</h4>

                                <div class="ml-auto font-weight-bold"> {{ sprintf('$ %d', $couponDiscountValue) }} </div>

                            </div>

                        @endif

                        <hr>

                        <div class="d-flex gr-total">

                            <h5>總計</h5>

                            <div class="ml-auto h5"> {{ sprintf('$ %d', $total) }} </div>

                        </div>

                        <hr>

                    </div>

                </div>

                <div class="col-12 d-flex shopping-box"><a href="{{ route('order.create') }}" class="ml-auto btn hvr-hover">Checkout</a> </div>

            </div>

        @else

            <p>目前無商品加入購物車</p>

        @endif

    </div>

</div>

<!-- End Cart -->

@endsection

