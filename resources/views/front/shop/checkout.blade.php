@php

    use App\Models\ShippingFee;

@endphp

@extends('front.layouts.app')

@section('scripts')

    @parent

    <script>

        //計算運費和商品的總和
        $('input[name="shipping-type"][type="radio"]').change(function () {

            var shippingFee = Number($(this).data('fee'));

            var productTotal = Number({{ $cart->getTotal() }});

            $('#shipping-fee').text(shippingFee ? shippingFee : '免費');

            $('#total').text(shippingFee+productTotal);
        });

    </script>

    <script>

        var districtAjax;

        //取得鄉鎮市區
        $('#city').on('change', getCityDistricts);

        function getCityDistricts () {

            var element = $('#city');

            var selectedCity = element.val();

            var selection = $('#district');

            districtAjax = $.ajax({
                url: "{{ route('api.area.getCityDistricts') }}",
                method: "GET",
                data: {city: selectedCity},
                success: function (data) {

                    selection.empty();

                    selection.append("<option data-display='Select'>Choose...</option>");

                    if ( data.length > 0 ) {

                        data.forEach(function (district) {

                            selection.append("<option value=" + district + ">" + district + "</option>");

                        });

                    }

                },

            });
        }

    </script>

    <script>

        //取得user資訊
        $('#same-address').on('change', function () {

            if (this.checked) {

                $.ajax({
                    url: "{{ route('api.userInfo.getUserInfo') }}",
                    method: "POST",
                    data: {
                        'user_id': {{ $user->id }},
                        'timestamp': {{ $timestamp }},
                        'api_token': '{{ $token }}'
                    },
                    dataType: "JSON",
                    success: function (data) {

                        $('#last_name').val(data.last_name);

                        $('#first_name').val(data.first_name);

                        $('#phone_number').val(data.phone_number);

                        $('#city').val(data.city).change();

                        $.when(districtAjax).then(function () {

                            $('#district').val(data.district);

                        });

                        $('#address').val(data.address);

                    },

                });

            }

        });

    </script>

@endsection

@section('content')

<style>

    /* HIDE RADIO */
    [type=radio] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
    }

    /* IMAGE STYLES */
    [type=radio] + img {
    cursor: pointer;
    }

    /* CHECKED STYLES */
    [type=radio]:checked + img {
    outline: 5px solid #b0b435;;
    }


</style>

@include('front.includes.titlebox', [
'title' => 'Checkout'
])

<!-- Start Cart  -->
<div class="cart-box-main">

    <div class="container">

        @include('front.includes.alert')

        @include('front.includes.breadcrumb', [
            'data' => [
                'Shop' => route('shop.index'),
                'Checkout' => ''
            ]
        ])

        <form id="checkout-form" class="needs-validation" method="POST" action="{{ route('order.store') }}">

            @csrf

            <div class="row">

                <div class="col-sm-6 col-lg-6 mb-3">

                    <div class="checkout-address">

                        <div class="title-left">

                            <h3>收件人資訊</h3>

                        </div>

                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label for="last_name">姓氏 *</label>

                                <input name="last_name" type="text" class="form-control" id="last_name" placeholder="" value="{{ old('last_name') }}" required>

                                <div class="invalid-feedback"> 請填入姓氏 </div>

                            </div>

                            <div class="col-md-6 mb-3">

                                <label for="first_name">名 *</label>

                                <input name="first_name" type="text" class="form-control" id="first_name" placeholder="" value="{{ old('first_name') }}" required>

                                <div class="invalid-feedback"> 請填入名 </div>

                            </div>

                            <div class="col-md-6 mb-3">

                                <label for="phone_number">聯絡電話 *</label>

                                <input name="phone_number" type="tel" pattern="^09[0-9]{8}$" class="form-control" id="phone_number" placeholder="" value="{{ old('phone_number') }}" required>

                                <div class="invalid-feedback"> 請填入聯絡電話 </div>

                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-5 mb-3">

                                <label for="city">縣市 *</label>

                                <select name="city" class="wide w-100" id="city" required>

                                    <option value="" data-display="Select">Choose...</option>

                                    @foreach (array_keys(Config::get('custom.city_list')) as $city)

                                        <option value="{{ $city }}">{{ $city }}</option>

                                    @endforeach

                                </select>

                                <div class="invalid-feedback"> 請選擇縣市 </div>

                            </div>

                            <div class="col-md-4 mb-3">

                                <label for="district">鄉鎮市區 *</label>

                                <select name="district" class="wide w-100" id="district" required>

                                    <option value="" data-display="Select">Choose...</option>

                                </select>

                                <div class="invalid-feedback"> 請選擇鄉鎮市區 </div>

                            </div>

                        </div>

                        <div class="mb-3">

                            <label for="address">住址 *</label>

                            <input type="text" class="form-control" name="address" id="address" placeholder="" required>

                            <div class="invalid-feedback"> 請填寫地址 </div>

                        </div>

                        <div class="custom-control custom-checkbox">

                            <input type="checkbox" class="custom-control-input" id="same-address">

                            <label class="custom-control-label" for="same-address">收件地址等同會員地址</label>

                        </div>

                        <div class="custom-control custom-checkbox">

                            <input type="checkbox" class="custom-control-input" id="save-info" name="save-info" value="1">

                            <label class="custom-control-label" for="save-info">記錄此次資訊供下次使用</label>

                        </div>

                        <hr class="mb-4">

                        <div class="title mb-1">

                            <span>付款方式</span>

                            <input class="form-check-input" style="width:1px; height:10px" type="radio" name="payment_method" value="" required>

                        </div>

                        <div class="row">

                            <div class="col-md-12 mb-3">

                                <div class="payment-icon">

                                    @foreach ($paymentMethodList as $paymentMethod)

                                        <div class="form-check form-check-inline mb-3">

                                            <label class="form-check-label" style="z-index: 10">

                                                <input class="form-check-input" type="radio" name="payment_method" value="{{ $paymentMethod->id }}">

                                                <img style="width:40px; height: 100%" src="{{ asset($paymentMethod->logo_url) }}" alt="no-image">

                                            </label>

                                        </div>

                                    @endforeach

                                </div>

                            </div>

                        </div>

                        <hr class="mb-1">

                    </div>

                </div>

                <div class="col-sm-6 col-lg-6 mb-3">

                    <div class="row">

                        <div class="col-md-12 col-lg-12">

                            <div class="shipping-method-box">

                                <div class="title-left">

                                    <h3>運送方式</h3>

                                </div>

                                <div class="mb-4">

                                    @foreach (ShippingFee::getTypeList() as $key => $shipType)

                                        <div class="custom-control custom-radio">

                                            <input id="{{ sprintf('shipping-type-%d', $key) }}" name="shipping-type" class="custom-control-input" type="radio" data-fee="{{ $shippingFeeList[$key] }}" value="{{ $key }}" required>

                                            <label for="{{ sprintf('shipping-type-%d', $key) }}" class="custom-control-label">{{ $shipType }}</label>

                                            <span class="float-right font-weight-bold">{{ $shippingFeeList[$key] ? sprintf('%d 元', $shippingFeeList[$key]) : '免費' }}</span>

                                        </div>

                                        <div class="ml-4 mb-2 small">(3-7 工作天)</div>

                                    @endforeach

                                </div>

                            </div>

                        </div>

                        {{-- cart start --}}
                        <div class="col-md-12 col-lg-12">

                            <div class="odr-box">

                                <div class="title-left">

                                    <h3>購物車</h3>

                                </div>

                                <div class="rounded p-2 bg-light">

                                    @foreach ($cart->getContent() as $item)

                                    <div class="media mb-2 border-bottom">

                                        <div class="media-body">

                                            <a href="{{ route('shop.show', ['product' => $item->attributes->product['id']]) }}">{{ $item->name }}</a>

                                            <span class="ml-3">{{ $item->attributes->stock['description'] }}</span>

                                            <div class="small text-muted">{{ sprintf('Price: %d', $item->price) }}

                                                <span class="mx-2">|</span>{{ sprintf(' Qty: %d', $item->quantity) }}

                                                <span
                                                    class="mx-2">|</span>{{ sprintf(' Subtotal: %d', $item->getPriceSum()) }}

                                            </div>

                                        </div>

                                    </div>

                                    @endforeach

                                </div>

                            </div>

                        </div>
                        {{-- cart end --}}

                        {{-- order start --}}
                        <div class="col-md-12 col-lg-12">

                            <div class="order-box">

                                <div class="title-left">

                                    <h3>您的訂單</h3>

                                </div>

                                <div class="d-flex">

                                    <div class="font-weight-bold">商品</div>

                                    <div class="ml-auto font-weight-bold">總和</div>

                                </div>

                                <hr class="my-1">

                                <div class="d-flex">

                                    <h4>商品總額</h4>

                                    <div class="ml-auto font-weight-bold" id="product-total">

                                        {{ sprintf('$ %d', $cart->getSubTotal()) }}

                                    </div>

                                </div>

                                <hr class="my-1">

                                    <div class="d-flex">

                                        <h4>優惠券折扣</h4>

                                        @if ($cart->getConditions()->get('coupon'))

                                            <div class="ml-auto font-weight-bold"> {{ $cart->getConditions()->get('coupon')->parsedRawValue }} </div>

                                        @else

                                            <div class="ml-auto font-weight-bold">0</div>

                                        @endif

                                    </div>

                                <div class="d-flex">

                                    <h4>運費</h4>

                                    <div class="ml-auto font-weight-bold" id="shipping-fee"> ??? </div>

                                </div>

                                <hr>

                                <div class="d-flex gr-total">

                                    <h5>總和</h5>

                                    <div class="ml-auto h5" id="total">

                                        {{ sprintf('$ %d', $cart->getTotal()) }}

                                    </div>

                                </div>

                                <hr>

                            </div>


                        </div>

                        <div class="col-12 d-flex shopping-box"> <button id="checkout-button" style="color:white" class="ml-auto btn hvr-hover">送出訂單</button> </div>

                    </div>

                </div>

            </div>

        </form>

    </div>
</div>
<!-- End Cart -->

@endsection
