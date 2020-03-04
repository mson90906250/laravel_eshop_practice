@extends('front.layouts.app')

@section('scripts')

    @parent

    {{-- Quill --}}
    {{-- 用來顯示商品的簡介 --}}
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

    <script>

        $(function() {

            //---Quill
            $('.product-description').each(function (index, element) {

                let id = '#product-description-' + $(element).data('id');

                let content = JSON.parse($(id).html());

                let quill;

                quill = new Quill(id, {
                    modules: {
                        toolbar: []
                    },
                });

                quill.setContents(content);

                quill.setText(quill.getText(0, 200) + '...');

                quill.enable(false);


                $('.ql-clipboard').remove();

            });

        });

    </script>

@endsection

@section('content')

    @include('front.includes.titlebox', [
        'title' => '願望清單'
    ])

     <!-- Start WishList  -->
     <div class="my-account-box-main">

        <div class="container">

            @include('front.includes.alert')

            @include('front.includes.breadcrumb', [
                'data' => [
                    '我的帳號' => route('user.index'),
                    '願望清單'  => '',
                ]
            ])

            <div style="padding:0px 20px">

                @if ($wishList->isEmpty())

                    目前沒有願望清單

                    <div>

                        <a class="mt-5 btn btn-info" href="{{route('shop.index')}}">前往商店--></a>

                    </div>

                @else

                    <table class="table table-hover mt-5">

                        <thead>


                            {{-- TODO: 在model裏設定要用來搜尋的attribute 用loop生成 --}}
                            <tr>

                                <th>商品圖片</th>

                                <th>

                                    <a class="sort-group" href="{{ route('order.index', array_merge(request()->except('_token'), ['order_by' => 'created_at'])) }}">

                                        商品名稱

                                    </a>


                                </th>

                                <th>

                                    商品簡介

                                </th>

                                <th></th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach ($wishList as $wish)

                                <tr>

                                    <th>

                                        <img style="height: 100px" src="{{ $wish->product->first_image ? $wish->product->first_image->url : ''  }}" alt="">

                                    </th>

                                    <td>{{ $wish->product->name }}</td>

                                    <td style="width: 50%">

                                        <div class="product-description" id="{{ sprintf('product-description-%d', $wish->product->id) }}" data-id="{{ $wish->product->id }}">

                                            {!! str_replace('\n', '\\n', $wish->product->description) !!}

                                        </div>

                                    </td>

                                    <td>

                                        <a href="{{ route('shop.show', ['product' => $wish->product->id]) }}" class="btn btn-info mb-1" style="font-size: 10px">前往商品頁面</a>

                                        <br>

                                        {!! Form::open(['url' => route('wishList.destroy', ['id' => $wish->id]), 'method' => 'POST']) !!}

                                            @method('DELETE')

                                            {!! Form::number('id', $wish->id, ['style' => 'display: none']) !!}

                                            {!! Form::button('移出願望清單', ['class' => 'btn btn-danger', 'type' => 'Submit', 'style' => 'font-size: 10px']) !!}

                                        {!! Form::close() !!}


                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                    {{ $wishList->links() }}

                @endif

            </div>

        </div>

    </div>

    <!-- End WishList -->


@endsection
