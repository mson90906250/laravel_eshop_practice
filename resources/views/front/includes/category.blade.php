@php

    use App\Http\Controllers\Front\ShopController;
    use App\Http\Controllers\Front\CartController;

    $cart = CartController::getCart();

@endphp

@section('scripts')

    @parent

    <script>

        //用來標示目前在哪個類別
        var category = {{ Request::route('category') ?? 0 }};

        $('a[class="list-group-item list-group-item-action"]').each(function () {

            if (category == $(this).data('category')) {

                $(this).addClass('active');

                //使父類別可以展開來顯示 選中的子類別
                $(this).parents('div[class="collapse"]').addClass('show');

            }

        });

    </script>

@endsection

<form action="{{ route('shop.category', ['category' => Request::route('category') ?? 0]) }}" method="GET">

    <div class="product-categori">

        <div class="search-product">

            <input name="product-name" class="form-control" placeholder="Search here..." type="text">

            <button type="submit"> <i class="fa fa-search"></i> </button>

        </div>

        <div class="filter-sidebar-left">

            <div class="title-left">

                <h3>Categories</h3>

            </div>

            <div class="list-group list-group-collapse list-group-sm list-group-tree" id="list-group-men" data-children=".sub-men">

                @foreach ($categories as $category)

                    @if ($category->totalProducts()->count() > 0)

                        @if (!$category->subCategories->isEmpty())

                            <div class="list-group-collapse sub-men">

                                <a class="list-group-item list-group-item-action" href="{{ sprintf('#sub-men%d', $category->id) }}" data-toggle="collapse" aria-expanded="false" aria-controls="sub-men1">

                                    {{ $category->name }}

                                    <small class="text-muted">{{ sprintf('(%d)', $category->totalProducts()->count()) }}</small>

                                </a>

                                <div class="collapse" id="{{ sprintf('sub-men%d', $category->id) }}" data-parent="#list-group-men">

                                    <div class="list-group">

                                        @foreach ($category->subCategories as $subCategory)

                                            @if ($subCategory->products()->count() > 0)

                                                <a href="{{ route('shop.category', ['category' => $subCategory->id]) }}" class="list-group-item list-group-item-action" data-category="{{ $subCategory->id }}">

                                                    {{ $subCategory->name }}

                                                    <small class="text-muted">{{ sprintf('(%d)', $subCategory->products()->count()) }}</small>

                                                </a>

                                            @endif

                                            @if ($loop->last && !$category->products->isEmpty())

                                                <a href="{{ route('shop.category', ['category' => $category->id]) }}" class="list-group-item list-group-item-action" data-category="{{ $category->id }}">

                                                    other

                                                </a>

                                            @endif

                                        @endforeach

                                    </div>

                                </div>

                            </div>

                        @else

                            <a href="{{ route('shop.category', ['category' => $category->id]) }}" class="list-group-item list-group-item-action" data-category="{{ $category->id }}">

                                {{ $category->name }}

                                <small class="text-muted">{{ sprintf('(%d)', $category->products()->count()) }}</small>

                            </a>

                        @endif

                    @endif

                @endforeach

                <a href="{{ route('shop.category', ['category' => 0]) }}" class="list-group-item list-group-item-action" data-category="0">

                    Show All

                </a>

            </div>

        </div>

        <div class="filter-price-left">

            <div class="title-left">

                <h3>Price</h3>

            </div>

            <div class="price-box-slider">

                <select id="basic" class="selectpicker show-tick form-control" data-placeholder="$ USD" name="price-order">

                    @foreach (ShopController::getSelectionOptionLabels() as $type => $name)

                        @if ($loop->first)

                            <option value="" disabled selected>--選擇排序--</option>

                        @endif

                        <option value="{{ $type }}" {{ Request::input('price-order') == $type ? 'selected' : '' }}>{{ $name }}</option>

                    @endforeach

                </select>

                <div class="mt-3" style="display: flex; flex-direction:row; justify-content: space-around">

                    <input class="form-control" style="width: 40%; display: inline-block" type="number" min="0" name="min" placeholder="低價" value="{{ Request::input('min') }}">

                    ~

                    <input class="form-control" style="width: 40%; display: inline-block" type="number" min="0" name="max" placeholder="高價" value="{{ Request::input('max') }}">

                </div>

                <button type="submit" class="btn btn-info mt-3" style="display: block; margin: auto">搜尋</button>

            </div>

        </div>

    </div>

</form>

