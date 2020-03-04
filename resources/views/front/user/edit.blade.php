@extends('front.layouts.app')

@section('scripts')

    @parent

    <script>

        var cityList = {!! json_encode(Config::get('custom.city_list')) !!}

        console.log(cityList);

        $(function () {

            $('#city').on('change', function () {

                var selectedCity = $(this).val();

                if (selectedCity == '') {

                    return false;

                }

                $('#district').empty();

                $('#district').append('<option>請選擇鄉鎮市區</option>')

                cityList[selectedCity].forEach(function (ele) {

                    $('#district').append('<option value='+ ele +'> '+ ele +' </option>')

                });

            });

        });

    </script>


@endsection

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
                    '用戶資訊' => route('user.show'),
                    '更新' => ''
                ]
            ])

            <div style="padding:0px 20px">

                <div class="row">

                    <div class="col-2"></div>

                    <div class="col-8">

                        {!! Form::open(['url' => route('user.update'), 'method' => 'PUT']) !!}

                            {!! Form::bsText('暱稱', 'nickname', $user->nickname, 'vertical') !!}

                            {!! Form::bsText('姓氏', 'last_name', $user->last_name, 'vertical') !!}

                            {!! Form::bsText('名稱', 'first_name', $user->first_name, 'vertical') !!}

                            {!! Form::bsText('信箱', 'email', $user->email, 'vertical') !!}

                            {!! Form::bsText('聯絡電話', 'phone_number', $user->phone_number, 'vertical') !!}


                            <hr>

                            <div class="row">

                                <div class="col-3 mr-3">

                                    <div class="row">

                                        <label for="city">縣市</label>

                                        <select class="form-control" name="city" id="city">

                                            <option value="">請選擇縣市</option>

                                            @foreach (array_keys(Config::get('custom.city_list')) as $k => $v)

                                                <option value="{{ $v }}" {{ $v == old('city') || $v == $user->city ? 'selected' : '' }} >{{ $v }}</option>

                                            @endforeach

                                        </select>

                                    </div>

                                </div>

                                <div class="col-3">

                                    <div class="row">

                                        <label for="district">鄉鎮市區</label>

                                        <select class="form-control" name="district" id="district">

                                            <option value="">請選擇鄉鎮市區</option>

                                            @if ($user->city)

                                                @foreach (Config::get('custom.city_list')[$user->city] as $k => $v)

                                                    <option value='{{ $v }}' {{ $v == old('city') || $v == $user->district ? 'selected' : '' }}>{{ $v }}</option>

                                                @endforeach

                                            @endif

                                        </select>

                                    </div>

                                </div>

                            </div>

                            {!! Form::bsText('地址', 'address', $user->address, 'vertical') !!}

                            <div class="row">

                                {!! Form::button('更新', ['class' => 'btn btn-primary mt-3', 'style' => 'margin: 0 auto', 'type' => 'Submit']) !!}

                            </div>

                        {!! Form::close() !!}

                    </div>

                    <div class="col-2"></div>

                </div>

            </div>

        </div>

    </div>

    <!-- End Order -->


@endsection
