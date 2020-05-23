<?php

namespace App\Http\Controllers\Front;

use App\Models\Comment;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\Common\CustomController;
// TODO: 修改 將ShopController 改成 ProductController
class ShopController extends CustomController
{
    // const CART_COOKIE_NAME = 'cart_cookie';

    const SELECT_OPTION_LOW_TO_HIGH = 1;
    const SELECT_OPTION_HIGH_TO_LOW = 2;

    public static function getSelectionOptionLabels()
    {
        return [
            self::SELECT_OPTION_LOW_TO_HIGH => '由低價到高價',
            self::SELECT_OPTION_HIGH_TO_LOW => '由高價到低價',
        ];
    }

    public function index($category = NULL)
    {
        $products = $this->search($category);

        //取得父類別
        $categories = Category::where('parent_id', '=', NULL)->get();

        return view('front.shop.index', [
            'products' => $products,
            'categories' => $categories
        ]);
    }

    public function show(Product $product)
    {
        $onlyOneStock = $product->stocks()->count() > 1 ? FALSE : $product->stocks()->first();

        $user = Auth::guard('web')->user();

        $ownedComment = NULL;

        if ($user) {

            $ownedComment = $product->comments()
                                    ->with('user')
                                    ->where('user_id', $user->id)
                                    ->first();

        }

        $otherComments = $product->comments()
                                    ->with('user')
                                    ->where('user_id', '!=', $user ? $user->id : '')
                                    ->limit(10)
                                    ->orderBy('updated_at', 'desc')
                                    ->get();

        return view('front.shop.product_detail', [
            'product' => $product,
            'onlyOneStock' => $onlyOneStock,
            'user' => $user,
            'ownedComment' => $ownedComment,
            'otherComments' => $otherComments
        ]);
    }

    protected function search($category = NULL)
    {
        $category = intval($category);

        //用來撈出price大於0的product, 且用來查詢價格範圍
        $subPriceQuery = DB::table('stocks')
                        ->select('product_id')
                        ->where('price', '>', 0)
                        ->groupBy('product_id');

        $subImageQuery = DB::table('images')
                        ->select(['url', 'product_id'])
                        ->where([
                            ['is_first_image', '=', TRUE]
                        ]);

        $query = DB::table('products AS p')
                    ->select(['p.*', 'i.url AS image_url'])
                    ->selectRaw('MIN(s.price) AS min_price, MAX(s.price) AS max_price')
                    ->leftJoin('category_product AS cp', 'p.id', '=', 'cp.product_id')
                    ->leftJoin('stocks AS s', 'p.id', '=', 's.product_id')
                    ->leftJoinSub($subImageQuery, 'i', 'p.id', '=', 'i.product_id');

         //加入搜尋條件
        if ($category) {

            $query->where('cp.category_id', '=', $category);

        }

        if (Request::capture()->except('page')) {

            $validatedData = Request::validate([
                'min'               => ['nullable', 'integer', 'min:0'],
                'max'               => ['nullable', 'integer', 'min:0'],
                'price-order'       => ['nullable', Rule::in(array_keys(self::getSelectionOptionLabels())) ],
                'product-name'      => ['nullable', 'string']
            ]);

            $min = $validatedData['min'] ?? 0;

            $max = $validatedData['max'] ?? 0;

            $priceOrder = $validatedData['price-order'] ?? NULL;

            $productName = $validatedData['product-name'] ?? '';

            $subPriceQuery->where('price', '>=', $min)
                        ->when($max, function ($query, $max) {
                            if ($max > 0) {
                                return $query->where('price' ,'<=', $max);
                            }
                        });

            if ($productName) {

                $query->where('p.name', 'like', sprintf('%%%s%%', $productName));

            }

            switch ($priceOrder) {

                case self::SELECT_OPTION_HIGH_TO_LOW:

                    $query->orderBy('max_price', 'desc');

                    break;

                case self::SELECT_OPTION_LOW_TO_HIGH:

                    $query->orderBy('min_price', 'asc');

                    break;
            }

        }

        $query->where('p.status', '=', Product::STATUS_ON);

        $query->joinSub($subPriceQuery, 'tmp', 'p.id', '=', 'tmp.product_id');

        $products = $query->groupBy(['p.id', 'i.url'])->get();

        $products = $this->paginate($products, 9)->withPath(route('shop.category', ['category' => $category ?? 0]))
                        ->appends(Request::capture()->except('page'));

        return $products;
    }

}
