<div role="tabpanel" class="tab-pane fade show active" id="grid-view">

    <div class="row">

        <!-- TODO 改成從DB抓資料 -->

        @foreach($products as $product)

            @if ($product->min_price > 0 && $product->max_price > 0)

                <div class="col-sm-6 col-md-6 col-lg-4 col-xl-4">

                    <div class="products-single fix">

                        <div class="box-img-hover">

                            <div class="type-lb">

                                <p class="{{ strtotime($product->created_at) > strtotime(Date('Y-m-d')) ? 'new' : '' }}">

                                    {{ strtotime($product->created_at) > strtotime(Date('Y-m-d')) ? 'New' : '' }}

                                </p>

                            </div>

                            <img src="{{ asset($product->image_url) }}" class="img-fluid" alt="Image">

                            <div class="mask-icon">

                                <ul>

                                    <li>

                                        <a href="{{ route('shop.show', ['product' => $product->id]) }}" data-toggle="tooltip" data-placement="right" title="View">

                                            <i class="fas fa-eye"></i>

                                        </a>

                                    </li>

                                    <li>

                                        {!! Form::open(['url' => route('wishList.store'), 'method' => 'POST']) !!}

                                            {!! Form::number('id', $product->id, ['style' => 'display: none']) !!}

                                            {!! Form::button('<i class="far fa-heart"></i>', ['data-toggle' => 'tooltip', 'data-placement' => 'right', 'title' => 'Add To Wishlist', 'type' => 'Submit']) !!}

                                        {!! Form::close() !!}

                                    </li>

                                </ul>

                            </div>

                        </div>

                        <div class="why-text">

                            <h4>{{ $product->name }}</h4>

                            <h5>{{ $product->max_price != $product->min_price ? sprintf('%d ~ %d', $product->min_price, $product->max_price) : $product->min_price }}</h5>

                        </div>

                    </div>

                </div>

            @endif

        @endforeach

    </div>

    {{ $products->fragment('grid-view')->links() }}
</div>

