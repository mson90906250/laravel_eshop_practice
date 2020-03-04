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

            });

        });

    </script>

@endsection

<div role="tabpanel" class="tab-pane fade" id="list-view">

    <!-- TODO 改成從DB抓資料 -->

    @foreach($products as $product)

        <div class="list-view-box">

            <div class="row">

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

                    </div>

                </div>

                <div class="col-sm-6 col-md-6 col-lg-8 col-xl-8">

                    <div class="why-text full-width">

                        <h4>{{ $product->name }}</h4>

                        <h5>

                            {{ $product->max_price != $product->min_price ? sprintf('%d ~ %d', $product->min_price, $product->max_price) : $product->min_price }}

                        </h5>

                        <p>

                            <div class="product-description" id="{{ sprintf('product-description-%d', $product->id) }}" data-id="{{ $product->id }}">

                                {!! str_replace('\n', '\\n', $product->description) !!}

                            </div>

                        </p>
                    </div>

                </div>

            </div>

        </div>

    @endforeach

    {{ $products->fragment('list-view')->links() }}

</div>

