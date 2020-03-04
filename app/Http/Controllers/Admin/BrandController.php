<?php

namespace App\Http\Controllers\Admin;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Common\CustomController;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\MessageBag;

class BrandController extends CustomController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $brandList = $this->paginate($this->search($request), 10)
                            ->withPath(route('admin.brand.index'))
                            ->appends($request->capture()->except('page'));

        return view('admin.brand.index', [
            'brandList' => $brandList
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.brand.create');
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
            'name' => ['required', 'string', 'unique:brands,name']
        ]);

        if (!Brand::create($validatedData)) {

            return redirect()->back()->withErrors(new MessageBag(['創建失敗']));

        }

        return redirect(route('admin.brand.index'))->with('status', '創建成功');

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function edit(Brand $brand)
    {
        return view('admin.brand.edit', [
            'brand' => $brand
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Brand $brand)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', function ($attribute, $value, $fail) use ($brand) {

                if ($brand->name !== $value
                        && Brand::where([
                            ['id', '!=', $brand->id],
                            ['name', '=', $value]
                        ])->exists()) {

                   $fail('此名稱已被使用');

                }

            }]
        ]);

        if (!$brand->update($validatedData)) {

            return redirect()->back()->withErrors(new MessageBag(['修改失敗']));

        }

        return redirect(route('admin.brand.index'))->with('status', sprintf('修改成功: %s', $brand->name));
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
            'id.*'  => ['required', 'integer', 'exists:brands,id']
        ]);

        //若該品牌尚有上架的商品時不能刪除
        $query = Brand::whereNotIn('id', function ($builder) {

            $builder->select('brand_id')
                    ->from('products')
                    ->where([
                        ['status', '=', Product::STATUS_ON]
                    ]);

        });

        $result =  $query->whereIn('id', $validatedData['id'])->delete();

        if ($result != count($validatedData['id'])) {

            return redirect()->back()->withErrors(new MessageBag(['有商品在架上的品牌不能刪除']));

        }

        return redirect()->back()->with('status', '刪除成功');

    }

    protected function search(Request $request)
    {
        $orderByList = ['id'];

        $validatedData = $request->validate([
            'name'                  => ['nullable', 'string'],
            'order_by'              => ['nullable', 'string', Rule::in($orderByList)],
            'hasProducts'           => ['nullable', 'integer', Rule::in(array_keys(Brand::getHasProductsLabels()))],
            'hasProductsInStore'    => ['nullable', 'integer', Rule::in(array_keys(Brand::getHasProductsInStoreLabels()))]
        ]);

        $query = Brand::query();

        foreach ($validatedData as $attribute => $value) {

            if ($value == NULL) {

                continue;

            }

            switch ($attribute) {

                case 'name':

                    $query->where([
                        [$attribute, 'like', sprintf('%%%s%%', $value)]
                    ]);

                    break;

                case 'order_by':

                    $query->orderBy($value, $request->get('is_asc') ? 'asc' : 'desc');

                    break;

                case 'hasProducts':

                    if ($value == Brand::HAS_PRODUCTS) {

                        $query->whereHas('products', function (Builder $builder) {

                            $builder->where([
                                ['id', '>=', 1]
                            ]);

                        });

                    } else {

                        $query->whereNotIn('id', function (QueryBuilder $builder) {

                            $builder->select('brand_id')->from('products');

                        });

                    }

                    break;

                case 'hasProductsInStore':

                    $query->whereHas('products', function (Builder $builder) use ($value) {

                        $builder->where('status', '=', $value);

                    });

                    break;
            }

        }

        return $query->get();
    }
}
