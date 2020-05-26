<?php

namespace App\Http\Controllers\Admin;

use finfo;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Common\CustomController;
use App\Models\Image;
use App\Models\Stock;
use Illuminate\Support\MessageBag;

class ProductController extends CustomController
{
    CONST CACHE_STORED_IMAGE = 'CACHE_STORED_IMAGE:%s'; //%s 接ip

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $productList = $this->paginate($this->search($request), 10)
                                ->withPath(route('admin.product.index'))
                                ->appends($request->capture()->except('page'));

        return view('admin.product.index', [
            'productList' => $productList
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $cachedImageId = json_decode(Cache::get(sprintf(self::CACHE_STORED_IMAGE, $request->ip())), TRUE);

        $cachedImages = $cachedImageId ? Image::whereIn('id', $cachedImageId)->get() : collect([]);

        $categoryList = Category::with('subcategories')->where('parent_id', '<', 1)->orWhere('parent_id', '=', NULL)->orderBy('id')->get();

        return View('admin.product.create', [
            'cachedImages'  => $cachedImages,
            'categoryList'  => $categoryList
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name'                      => ['required', 'string'],
            'brand'                     => ['required', 'integer', 'exists:brands,id'],
            'category'                  => ['required', 'array'],
            'category.*'                => ['required', 'exists:categories,id'],
            'original_price'            => ['required', 'integer', 'min:0'],
            'status'                    => ['required', 'integer', Rule::in(array_keys(Product::getStatusLabels()))],
            'description'               => ['nullable', 'string'],
            'images_to_store'           => ['required', 'array'],
            'images_to_store.*'         => ['required', 'integer', 'exists:images,id'],
            'first_image'               => ['required', 'integer', 'exists:images,id'],
            'stock'                     => ['required', 'array'],
            'stock.*.image_for_stock'   => ['required', 'integer', 'exists:images,id'],
            'stock.*.attribute'         => ['nullable', 'string'],
            'stock.*.quantity'          => ['required', 'integer', 'min:0'],
            'stock.*.price'             => ['required', 'integer', 'min:0'],
        ]);

        //開始進行transaction
        try {

            DB::transaction(function () use ($validatedData) {

                $newProduct = Product::create([
                    'name'              => $validatedData['name'],
                    'brand_id'          => $validatedData['brand'],
                    'original_price'    => $validatedData['original_price'],
                    'status'            => $validatedData['status'],
                    'description'       => $validatedData['description'],
                ]);

                if (!$newProduct) {

                    throw new \ErrorException('商品創建失敗');

                }

                //儲存商品種類
                $newProduct->categories()->attach($validatedData['category']);

                //更新圖片的product_id
                $updateImages = Image::whereIn('id', $validatedData['images_to_store'])
                                        ->update(['product_id' => $newProduct->id]);

                if (!$updateImages) {

                    throw new \ErrorException('更新圖片的product_id失敗');

                }

                //更新封面圖片
                $firstImage = Image::where([
                                    ['id', '=', $validatedData['first_image']],
                                    ['product_id', '=', $newProduct->id]
                                ])
                                ->update(['is_first_image' => TRUE]);

                if (!$firstImage) {

                    throw new \ErrorException('設定封面圖片失敗');

                }

                //儲存stocks
                $stocks = [];

                foreach ($validatedData['stock'] as $stock) {

                    $stocks[] = [
                        'product_id'    => $newProduct->id,
                        'attribute'     => $stock['attribute'] ?? '',
                        'quantity'      => $stock['quantity'],
                        'price'         => $stock['price'],
                        'image_id'      => $stock['image_for_stock']
                    ];

                }

                if (!Stock::insert($stocks)) {

                    throw new \ErrorException('插入stocks失敗');

                }

            });

             //清除cache
             Cache::forget(sprintf(self::CACHE_STORED_IMAGE, $request->ip()));

             return redirect(route('admin.product.index'))->with('status', '創建商品成功');

        } catch (\Exception $e) {

            $request->flash();

            return redirect()->back()->withErrors(new MessageBag([$e->getMessage()]));

        }

    }

    public function storeImage(Request $request)
    {
        try {

            //確認圖片格式
            if (!preg_match('/base64,(.*)/', $request->get('image'), $matches)) {

                throw new \ErrorException('上傳的圖片不符合格式');

            };

            $image = base64_decode($matches[1]);

            $finfo = new finfo(FILEINFO_MIME_TYPE);

            $mimetype = $finfo->buffer($image);

            if (!preg_match('/image\/(\w+)/', $mimetype, $matches)) {

                throw new \ErrorException('請上傳圖片');

            }

            //儲存圖片
            $imageName = sprintf('%d-%s.%s', intval(microtime(TRUE) * 10000), uniqid(), $matches[1]);

            $rootDirPath = trim(Config::get('custom.product_image_path'), '/');

            $dirPath = date('Y-m-d');

            $imageUrl = sprintf('%s/%s/%s', $rootDirPath, $dirPath, $imageName);

            $storePath = public_path(sprintf('%s/%s', $rootDirPath, $dirPath));

            //建立資料夾
            if (!file_exists($storePath)) {

                mkdir($storePath, 0777, TRUE);

            }

            if (!file_put_contents(public_path($imageUrl), $image)) {

                throw new \ErrorException('圖片儲存失敗');

            }

            //存入db
            $imageInDb = Image::create(['url' => $imageUrl]);

            if (!$imageInDb) {

                unlink(public_path($imageUrl));

                throw new \ErrorException('圖片存入資料庫失敗');

            }

            //cache
            $cachedImages = json_decode(Cache::pull(sprintf(self::CACHE_STORED_IMAGE, $request->ip())), TRUE) ?? [];

            Cache::put(sprintf(self::CACHE_STORED_IMAGE, $request->ip()), json_encode(array_merge($cachedImages, [$imageInDb->id])), 3600);

            $returnData = [
                'imageId' => $imageInDb->id,
                'url' => $imageInDb->url,
            ];

            return $this->getReturnData(TRUE, 'SUCCESS', $returnData);

        } catch (\Exception $e) {

            return $this->getReturnData(FALSE, $e->getMessage());

        }


    }

    /**
     * Display the specified resource.
     *
     * @param  Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return view('admin.product.show', [
            'product' => $product
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Product $product)
    {
        $cachedImageId = json_decode(Cache::get(sprintf(self::CACHE_STORED_IMAGE, $request->ip())), TRUE);

        $cachedImages = $cachedImageId ? Image::whereIn('id', $cachedImageId)->get() : collect([]);

        $mergedImages = $product->images->merge($cachedImages);

        $categoryList = Category::with('subcategories')->where('parent_id', '<', 1)->orWhere('parent_id', '=', NULL)->orderBy('id')->get();

        $firstImage = $mergedImages->where('is_first_image', TRUE)->first();

        return view('admin.product.edit', [
            'product' => $product,
            'categoryList' => $categoryList,
            'mergedImages' => $mergedImages,
            'ownedCategoryList' => $product->categories->pluck('id')->toArray(),
            'firstImageId' => $firstImage ? $firstImage->id : '',
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $validatedData = $request->validate([
            'name'                      => ['required', 'string'],
            'brand'                     => ['required', 'integer', 'exists:brands,id'],
            'category'                  => ['required', 'array'],
            'category.*'                => ['required', 'exists:categories,id'],
            'original_price'            => ['required', 'integer', 'min:0'],
            'status'                    => ['required', 'integer', Rule::in(array_keys(Product::getStatusLabels()))],
            'description'               => ['nullable', 'string'],
            'images_to_store'           => ['required', 'array'],
            'images_to_store.*'         => ['required', 'integer', 'exists:images,id'],
            'first_image'               => ['required', 'integer', 'exists:images,id'],
            'stock'                     => ['required', 'array'],
            'stock.*.id'                => ['required', 'integer'],
            'stock.*.image_id'          => ['required', 'integer', 'exists:images,id'],
            'stock.*.attribute'         => ['nullable', 'string'],
            'stock.*.quantity'          => ['required', 'integer', 'min:0'],
            'stock.*.price'             => ['required', 'integer', 'min:0'],
        ]);


        //開始進行transaction
        try {
            DB::transaction(function () use ($validatedData, $product) {

                $updateProduct = $product->update([
                    'name' => $validatedData['name'],
                    'brand_id' => $validatedData['brand'],
                    'original_price' => $validatedData['original_price'],
                    'status' => $validatedData['status'],
                    'description' => $validatedData['description'],
                ]);

                if (!$updateProduct) {

                    throw new \ErrorException('商品更新失敗');

                }

                //儲存商品種類
                $product->categories()->detach();

                $product->categories()->attach($validatedData['category']);

                //更新圖片的product_id 並 將圖片的is_first_image都設為false
                $updateImages = Image::whereIn('id', $validatedData['images_to_store'])
                                        ->update([
                                            'product_id' => $product->id,
                                            'is_first_image' => FALSE,
                                        ]);

                if (!$updateImages) {

                    throw new \ErrorException('更新圖片的product_id失敗');

                }

                //更新封面圖片
                $firstImage = Image::where([
                                    ['id', '=', $validatedData['first_image']],
                                    ['product_id', '=', $product->id]
                                ])
                                ->update(['is_first_image' => TRUE]);

                if (!$firstImage) {

                    throw new \ErrorException('設定封面圖片失敗');

                }

                //儲存stocks
                $newStocks = [];

                foreach ($validatedData['stock'] as $stock) {

                    //已經存在於db的stock(即id > 0)
                    if ($stock['id']) {

                        $updateStock = Stock::find($stock['id'])->update([
                            'product_id'    => $product->id,
                            'attribute'     => $stock['attribute'] ?? '',
                            'quantity'      => $stock['quantity'],
                            'price'         => $stock['price'],
                            'image_id'      => $stock['image_id']
                        ]);

                        if (!$updateStock) {

                            throw new \ErrorException(sprintf('更新商品規格id: %d 失敗', $stock['id']));

                        }

                        continue;
                    }

                    $newStocks[] = [
                        'product_id'    => $product->id,
                        'attribute'     => $stock['attribute'] ?? '',
                        'quantity'      => $stock['quantity'],
                        'price'         => $stock['price'],
                        'image_id'      => $stock['image_id']
                    ];

                }

                if (!Stock::insert($newStocks)) {

                    throw new \ErrorException('插入stocks失敗');

                }

            });

             //清除cache
             Cache::forget(sprintf(self::CACHE_STORED_IMAGE, $request->ip()));

             return redirect(route('admin.product.show', ['product' => $product->id]))->with('status', '修改商品成功');

        } catch (\Exception $e) {

            $request->flash();

            return redirect()->back()->withErrors(new MessageBag([$e->getMessage()]));

        }
    }

    public function updateStatus(Request $request)
    {
        $validatedData = $request->validate([
            'id'        => ['required', 'array'],
            'id.*'      => ['required', 'integer', 'exists:products,id'],
            'status'    => ['required', 'integer', Rule::in(array_keys(Product::getStatusLabels()))]
        ]);

        $query = Product::query()->whereIn('id', $validatedData['id']);

        if ($query->update(['status' => $validatedData['status']])) {

            return redirect()->back()->with('status', sprintf('%s成功!', Product::getStatusLabels()[$validatedData['status']]));

        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $validatedData = $request->validate([
            'id'    => ['required', 'array'],
            'id.*'  => ['required', 'integer', 'exists:products,id'],
        ]);

        //刪掉商品同時, 也將圖片刪掉
        try {

            DB::transaction(function () use ($validatedData, $request) {

                //刪除商品
                $productQuery = Product::whereIn('id', $validatedData['id'])
                                        ->where([
                                            ['status', '=', Product::STATUS_OFF]
                                        ])
                                        ->delete();

                if (!$productQuery) {

                    throw new \ErrorException('刪除已下架的商品失敗');

                }

                //刪除圖片
                $imageQuery = Image::whereIn('product_id', $validatedData['id']);

                foreach ($imageQuery->get() as $image) {

                    if (file_exists(public_path($image->url))) {

                        unlink(public_path($image->url));

                        if (is_dir_empty(public_path(dirname($image->url)))) {

                            rmdir(public_path(dirname($image->url)));

                        }

                    }

                    $image->delete();

                }

            });

            return redirect()->back()->with('status', '商品刪除成功');

        } catch (\Exception $e) {

            return redirect()->back()->withErrors(new MessageBag([$e->getMessage()]));

        }
    }

    public function deleteImage(Request $request)
    {
        $validatedData = $request->validate([
            'images' => ['required', 'array'],
            'images.*' => ['required', 'integer'],
        ]);

        $images = Image::whereIn('id', $validatedData['images'])->get();

        $cachedImages = json_decode(Cache::pull(sprintf(self::CACHE_STORED_IMAGE, $request->ip())), TRUE) ?? [];

        $data = [];

        foreach ($images as $image) {

            $data[] = $image->id;

            $imageUrl = $image->url;

            if (unlink(public_path($imageUrl))) {

                //圖片刪除成功時 同時將其從db那掉
                $image->delete();

            }

            //確認該資料夾是否還有圖片 沒有的話就刪掉
            preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}/', $imageUrl, $matches);

            $dirPath = public_path(sprintf('%s/%s', Config::get('custom.product_image_path'), $matches[0]));

            if (is_dir_empty($dirPath)) {

                rmdir($dirPath);

            }
        }

        //---更新cache
        if (!empty($cachedImages)) {

            Cache::put(sprintf(self::CACHE_STORED_IMAGE, $request->ip()), json_encode(array_diff($cachedImages, $data)), 3600);

        }

        return $this->getReturnData(TRUE, 'SUCCESS');

    }

    public function deleteStock(Request $request)
    {
        $validatedData = $request->validate([
            'product_id'        => ['required', 'integer', 'exists:products,id'],
            'stockIdList'       => ['required', 'array'],
            'stockIdlist.*'     => ['required', 'integer', 'exists:stocks,id']
        ]);

        $product = Product::find($validatedData['product_id']);

        $stocks = $product->stocks;

        $countDiff = $stocks->count() - count($validatedData['stockIdList']);

        if ($countDiff < 1) {

            return $this->getReturnData(FALSE, 'stock必須至少保留一個');

        }

        //刪除所選的stock
        $product->stocks()->whereIn('id', $validatedData['stockIdList'])->delete();

        return $this->getReturnData(TRUE, '刪除商品規格成功');

    }

    protected function search(Request $request)
    {
        $orderByList = ['name', 'brand_id', 'category_id', 'original_price', 'price_range', 'created_at', 'status'];

        $validatedData = $request->validate([
            'name'              => ['nullable', 'string'],
            'category_id'       => ['nullable', 'integer', 'exists:categories,id'],
            'brand_id'          => ['nullable', 'integer', 'exists:brands,id'],
            'original_price'    => ['nullable', 'integer'],
            'price_range'       => ['nullable', 'array'],
            'price_range.*'     => ['nullable', 'integer', 'min:0'],
            'date_start'        => ['nullable', 'date'],
            'date_end'          => ['nullable', 'date'],
            'status'            => ['nullable', 'integer', Rule::in(array_keys(Product::getStatusLabels()))],
            'order_by'          => ['nullable', 'string', Rule::in($orderByList)]
        ]);

        $priceRangeRaw = 'MIN(price) as min_price,
                            MAX(price) as max_price,
                            CASE
                                WHEN MIN(price) != MAX(price) THEN CONCAT(MIN(price), "~", MAX(price))
                                ELSE MIN(price)
                            END as price_range';

        $priceRangeQuery = DB::table('stocks')->select(['product_id'])
                                                ->selectRaw($priceRangeRaw)
                                                ->groupBy('product_id');

        $categoryProductQuery = DB::table('category_product')
                                    ->select(['product_id'])
                                    ->groupBy('product_id');

        $query = DB::table('products as p')->select(['p.*', 'b.name as brand', 'price_range'])
                    ->selectRaw('GROUP_CONCAT(c.name SEPARATOR "\n") as category, MIN(c.id) as category_id') //MIN(c.id) as category_id 專門用來給種類排序的
                    ->leftJoin('category_product as cp', 'p.id', '=', 'cp.product_id')
                    ->leftJoin('categories as c', 'cp.category_id', '=', 'c.id')
                    ->leftJoin('brands as b', 'p.brand_id', '=', 'b.id')
                    ->groupBy('p.id');


        foreach ($validatedData as $attribute => $value) {

            if ($value == NULL) {

                continue;

            }

            switch ($attribute) {

                case 'name':

                    $query->where([
                        ['p.name', 'like', sprintf('%%%s%%', $value)]
                    ]);

                    break;

                case 'category_id':

                    $categoryProductQuery->where([
                        [$attribute, '=', $value]
                    ]);

                    $query->joinSub($categoryProductQuery, 'cpq', 'p.id', '=', 'cpq.product_id');

                    break;
                case 'brand_id':
                case 'original_price':
                case 'status':

                    $query->where([
                        [$attribute, '=', $value]
                    ]);

                    break;

                case 'price_range':

                    if (isset($value['min']) && $value['min'] != NULL) {

                        $priceRangeQuery->where([
                            ['price', '>=', $value['min']]
                        ]);

                    }

                    if (isset($value['max']) && $value['max'] != NULL) {

                        $priceRangeQuery->where([
                            ['price', '<=', $value['max']]
                        ]);

                    }

                    break;

                case 'date_start':

                    $query->where([
                        ['p.created_at', '>=', $value]
                    ]);

                    break;

                case 'date_end':

                    $query->where([
                        ['p.created_at', '<=', sprintf('%s 23:59:59', $value)]
                    ]);

                    break;

                case 'order_by':

                    if ($value != 'price_range') {

                        $query->orderBy($value, $request->get('is_asc') ? 'asc' : 'desc');

                    } else {

                        if ($request->get('is_asc')) {

                            $query->orderBy('pr.min_price', 'asc');

                        } else {

                            $query->orderBy('pr.max_price', 'desc');

                        }

                    }

                    break;

            }

        }

        $query->joinSub($priceRangeQuery, 'pr', 'p.id', '=', 'pr.product_id');

        return $query->get();
    }

}
