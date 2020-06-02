@extends('front.layouts.app')

@section('scripts')

    @parent

    {{-- Quill --}}
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

    <script>

        $(function () {

            //---Quill
            var quill = new Quill('#product-description', {
                modules: {
                    toolbar: []
                },
            });

            var oldDescription = String.raw`{!! $product->description !!}`;

            oldDescription = JSON.parse(oldDescription);

            quill.setContents(oldDescription);

            quill.enable(false);
            //---

            //---comment

            //確認是否登入
            $('#btn-create-comment').on('click', function () {

                var isMember = '{{ auth()->check() }}';

                if (!isMember) {

                    window.location.href = '{{ route("login.showLoginForm") }}';

                    return false;

                }

            });

            var oldStr = '';

            $('#comment').on('input propertychange', function () {

                var str = $(this).val();

                var byteSize = (new TextEncoder().encode(str)).length;

                if (byteSize > 300) {

                    $(this).val(oldStr);

                    alert('字數不得超過100');

                    return false;

                }

                oldStr = str;

            });

            //新增comment
            $('#btn-submit-comment').on('click', function () {

                var commentStr = $('#comment').val();

                $.ajax({
                    url: '{{ route("comment.store") }}',
                    type: 'POST',
                    data: {
                        comment: commentStr,
                        product_id: {{ $product->id }},
                    },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    dataType: 'JSON',
                    success: function (data) {

                        var commentContent = data.data;

                        var html = '<div class="media mb-3 comment-block" id="self-comment" style="background: antiquewhite">'
                                        +'<div class="media-body">'
                                            +'<p style="white-space: pre-wrap">'+ commentContent.comment.content +'</p>'
                                            +'<div style="text-align: end">'
                                                +'<a id="delete-comment" style="cursor: pointer" data-id="'+ commentContent.comment.id +'"><small style="color: brown">刪除評論</small></a>'
                                                +'<small class="text-muted">Posted by '+ commentContent.user.nickname +' on '+ commentContent.comment.updated_at +' </small>'
                                            +'</div>'
                                        +'</div>'
                                    +'</div>';

                        $('#self-comment').remove();

                        $('div.card-body').prepend(html);

                    },
                    error: function (data) {

                        //跳轉至登入頁面
                        console.log(data);

                    }
                });

            });

            //刪除comment
            $('div.card-body').on('click', '#delete-comment', function () {

                var commentId = $(this).data('id');

                $.ajax({
                    url: '{{ route("comment.destroy") }}',
                    type: 'POST',
                    data: {comment: commentId},
                    dataType: 'JSON',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function (data) {

                        if (data.status) {

                            $('#self-comment').remove();

                            $('#comment').val('');

                            alert('刪除成功');

                        } else {

                            alert(data.message);

                        }

                    }
                });

            });

            //取得更多的comments
            $('#btn-show-more-comments').on('click', function () {

                var commentId = $(this).data('comment_id');

                var timestamp = $(this).data('timestamp');

                if (commentId && timestamp) {

                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        url: '{{ route("api.comment.getMoreComments") }}',
                        type: 'POST',
                        data: {
                            'comment_id': commentId,
                            'timestamp': timestamp,
                            'product_id': '{{ $product->id }}',
                        },
                        dataType: 'JSON',
                        success: function (data) {

                            console.log(data);

                            var html = '';

                            var lastCommentId, timestamp;

                            for (let i=0; i<data.length; i++) {

                                html += '<div class="media mb-3 comment-block">'
                                            +'<div class="media-body">'
                                                +'<p style="white-space: pre-wrap">'+ data[i].content +'</p>'
                                                +'<div style="text-align: end">'
                                                    +'<small class="text-muted">Posted by test on '+ data[i].updated_at +'</small>'
                                                +'</div>'
                                            +'</div>'
                                        +'</div>';

                                if (i === (data.length-1) ) {

                                    lastCommentId = data[i].id;

                                    timestamp = (new Date(data[i].updated_at).getTime())/1000;

                                    $('#btn-show-more-comments').data('timestamp', timestamp)
                                                                .data('comment_id', lastCommentId);

                                }

                            }

                            $('div.comment-block').last().after(html);

                        }
                    });

                }

            });

            //---

        });

    </script>

    <script>

        var form = $('#custom-cart-form');

        //當游標hover到stock-item時發生的行爲
        $('.stock-item').hover(function () {

            var stockImage = $(this).data('img');

            if (stockImage == "{{ asset('front/') }}") {

                return false;

            }

            $('#carousel-example-1').css({display : 'none'});

            $('#stock-image').css({display : 'block'}).attr('src', stockImage);


        }, function () {

            $('#stock-image').css({display : 'none'});

            $('#carousel-example-1').css({display : 'block'});

        });

        //click到stock-item時所發生的行爲
        $('.stock-item').on('click', function () {

            var stockQuantity = $(this).data('quantity');
            var stockId = $(this).data('id');
            var stockPrice = $(this).data('price');
            var stockAttribute = $(this).data('attribute');
            var stockImage = $(this).data('img');

            $('div[class="stock-item"]').css({
                background: 'white',
                color: 'black'
            });

            $('div[class="stock-item out-of-stock"]').css({
                background: '#c4bdbd',
                color: 'black'
            });

            $(this).css({
                background: 'black',
                color: 'white'
            });

            $('#available-stock b').text(stockQuantity);

            $('#stock-item-price b').text(stockPrice);

            $('.quantity-box input[name="quantity"]').attr('max', stockQuantity);

            form.find('input[name="price"]').val(stockPrice);

            if (stockQuantity > 0) {

                form.find('input[name="id"]').val(stockId);

            }

            form.find('input[name="attributes[stock][description]"]').val(stockAttribute);

            form.find('input[name="attributes[stock][image]"]').val(stockImage);

            form.find('input[name="attributes[stock][maxQuantity]"]').val(stockQuantity);
        });

        //送出表單時
        form.on('submit', function () {

            if (form.find('input[name="id"]').val() == '') {

                alert('請選擇一個商品種類');

                return false;

            }

        });

    </script>

@endsection

@section('content')

@include('front.includes.titlebox')

<!-- Start Shop Detail  -->

<div class="shop-detail-box-main">

    <div class="container">

        @include('front.includes.alert')

        @include('front.includes.breadcrumb', [
        'data' => [
            'Shop' => route('shop.index'),
            'Product' => ''
        ]])

        <div class="row">

            <!-- 幻燈片start -->

            <div class="col-xl-5 col-lg-5 col-md-6">

                {{-- 當游標hover到特定的stock時呈現圖片 --}}
                <img id="stock-image" class="w-100" src="" style="display:none">

                <div id="carousel-example-1" class="single-product-slider carousel slide" data-ride="carousel">

                    <div class="carousel-inner" role="listbox">

                        @if(!$product->images->isEmpty())

                            @foreach($product->images as $image)

                                @if($loop->first)

                                    <div class="carousel-item active"> <img class="d-block w-100" src="{{ asset($image->url) }}"
                                            alt="First slide"> </div>

                                    @else

                                    <div class="carousel-item"> <img class="d-block w-100" src="{{ asset($image->url) }}"
                                            alt="Second slide"> </div>

                                @endif

                            @endforeach

                        @else

                            <div class="carousel-item active"> <img class="d-block w-100" src="{{ asset(Config::get('custom.no_image_url')) }}"
                                    alt="First slide"> </div>

                        @endif

                    </div>

                    <a class="carousel-control-prev" href="#carousel-example-1" role="button" data-slide="prev">

                        <i class="fa fa-angle-left" aria-hidden="true"></i>

                        <span class="sr-only">Previous</span>

                    </a>

                    <a class="carousel-control-next" href="#carousel-example-1" role="button" data-slide="next">

                        <i class="fa fa-angle-right" aria-hidden="true"></i>

                        <span class="sr-only">Next</span>

                    </a>

                    <ol class="carousel-indicators">

                        @foreach($product->images as $image)

                            @if($loop->first)

                                <li data-target="#carousel-example-1" data-slide-to="{{ $loop->index }}" class="active">

                                    <img class="d-block w-100 img-fluid" src="{{ asset($image->url) }}" alt="" />

                                </li>

                            @else

                                <li data-target="#carousel-example-1" data-slide-to="{{ $loop->index }}">

                                    <img class="d-block w-100 img-fluid" src="{{ asset($image->url) }}" alt="" />

                                </li>

                            @endif

                        @endforeach

                    </ol>

                </div>

            </div>

            <!-- 幻燈片end -->

            <div class="col-xl-7 col-lg-7 col-md-6">

                <div class="single-product-details">

                    <h2>{{ $product->name }}</h2>

                    <h4>Short Description:</h4>

                    <div id="product-description"></div>

                    <ul>

                        <form id="custom-cart-form" method="POST" action="{{ route('cart.store') }}">

                            @csrf

                            <li>

                                <div class="row" style="padding:5px">

                                    @if (!$onlyOneStock)

                                        @foreach ($product->stocks AS $stock)

                                            <div class="col-4 mb-1">

                                                <div class="stock-item{{ $stock->quantity ? '' : ' out-of-stock' }}"
                                                    style="padding:5px; border: 1px dotted black; text-align:center"
                                                    data-quantity={{ $stock->quantity ?? 0 }}
                                                    data-id={{ $stock->id ?? 0 }}
                                                    data-img={{ asset($stock->image ? $stock->image->url : config('custom.no_image_url')) }}
                                                    data-price={{ $stock->price ?? '???'}}
                                                    data-attribute={{ $stock->attribute ?? '' }}
                                                >
                                                    {{ $stock->attribute }}</div>

                                            </div>

                                        @endforeach

                                    @endif

                                </div>

                                <p class="available-stock">

                                    Price: <span id="stock-item-price"><b>{{ $onlyOneStock ? $onlyOneStock->price : '???' }}</b></span>

                                    <br>

                                    <span id="available-stock"><b>{{ $onlyOneStock ? $onlyOneStock->quantity : '???' }}</b></span> available

                                </p>

                            </li>

                            <li>

                                <div class="form-group quantity-box">

                                    <label class="control-label">Quantity</label>

                                    <input class="form-control" value="1" min="1" max="{{ $onlyOneStock ? $onlyOneStock->quantity : NULL }}"
                                        type="number" name="quantity">

                                </div>

                                <input type="hidden" name="id" value="{{ $onlyOneStock ? $onlyOneStock->id : NULL }}">

                                <input type="hidden" name="attributes[stock][description]" value="">

                                <input type="hidden" name="name" value="{{ $product->name }}">

                                <input type="hidden" name="price" value="{{ $onlyOneStock ? $onlyOneStock->price : NULL }}">

                                <input type="hidden" name="attributes[product][id]" value="{{ $product->id }}">

                                <input type="hidden" name="attributes[stock][image]" value="{{ $onlyOneStock ? asset($onlyOneStock->image->url) : NULL }}">

                                <input type="hidden" name="attributes[stock][maxQuantity]" value="{{ $onlyOneStock ? $onlyOneStock->quantity : NULL }}">

                                <button type="submit" class="btn hvr-hover" style="color: white; font-weight: 700">

                                    Add to Cart

                                </button>

                                <a class="btn hvr-hover" href="{{ route('wishList.store', ['id' => $product->id]) }}" style="color: white; font-weight: 700">

                                    <i class="fas fa-heart"></i> Add to wishlist

                                </a>

                            </li>

                        </form>

                    </ul>

                </div>

            </div>

        </div>

        <div class="row my-5">

            <div style="width: 100%" class="card card-outline-secondary my-4">

                <div class="card-header">

                    <h2>Product Reviews</h2>

                </div>

                <div class="card-body" style="max-height: 400px; overflow-y: scroll">

                    {{-- comment --}}
                    @if ($ownedComment)

                        <div class="media mb-3 comment-block" id="self-comment" style="background: antiquewhite">

                            <div class="media-body">

                                <p style="white-space: pre-wrap">{{ $ownedComment->content }}</p>

                                <div style="text-align: end">

                                    <a id="delete-comment" style="cursor: pointer" data-id="{{ $ownedComment->id }}"><small style="color: brown">刪除評論</small></a>

                                    <small class="text-muted">Posted by {{ $ownedComment->user->nickname }} on {{ $ownedComment->updated_at ?? $ownedComment->created_at }}</small>

                                </div>

                            </div>

                        </div>

                    @endif

                    @foreach ($otherComments as $comment)

                        <div class="media mb-3 comment-block">

                            <div class="media-body">

                                <p style="white-space: pre-wrap">{{ $comment->content }}</p>

                                <div style="text-align: end">

                                    <small class="text-muted">Posted by {{ $comment->user->nickname }} on {{ $comment->created_at }}</small>

                                </div>

                            </div>

                        </div>

                        @if ($loop->last)

                            <div style="display: flex; justify-content: center">

                                <button id="btn-show-more-comments" data-timestamp="{{ strtotime($comment->updated_at) }}" data-comment_id="{{ $comment->id }}"  type="button" class="btn-info btn-sm">more...</button>

                            </div>

                        @endif

                    @endforeach


                    {{--  --}}

                    <!-- Create Comment Modal -->
                    <div class="modal fade" id="create-comment" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">

                        <div class="modal-dialog modal-lg">

                            <div class="modal-content">

                                <div class="modal-header">

                                    <h5 class="modal-title" id="staticBackdropLabel">評論</h5>

                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">

                                        <span aria-hidden="true">&times;</span>

                                    </button>

                                </div>

                                <div class="modal-body">

                                    <textarea id="comment" style="width: 100%" name="comment" rows="5">{{ $ownedComment ? $ownedComment->content : ''  }}</textarea>

                                </div>

                                <div class="modal-footer">

                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

                                    <button type="button" id="btn-submit-comment" class="btn btn-primary">送出</button>

                                </div>

                            </div>

                        </div>

                    </div>
                    {{--  --}}

                </div>

            </div>

            <button id="btn-create-comment" type="button" data-toggle="modal" {{ auth()->check() ? 'data-target=#create-comment' : '' }} class="btn hvr-hover btn-comment">評論</button>


        </div>

    </div>

</div>

<!-- End Cart -->

@endsection
