<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    CONST CACHE_CATEGORY_OPTIONS = 'CACHE_CATEGORY_OPTIONS';
    CONST CACHE_TOTAL_PRODUCTS = 'CACHE_TOTAL_PRODUCTS:%d'; //%d 為該category的id;

    protected $fillable = ['name', 'parent_id'];

    public $timestamps = FALSE;

    public function products()
    {
        return $this->belongsToMany('App\Models\Product');
    }

    public function subCategories()
    {
        return $this->hasMany('App\Models\Category', 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo('App\Models\Category', 'parent_id');
    }


    /**
     * 取得包含所有子類別的products
     *
     * @return void
     */
    public function totalProducts()
    {
        $totalProducts = Cache::remember(sprintf(static::CACHE_TOTAL_PRODUCTS, $this->id), 300, function () {

            $data = collect();

            foreach ($this->subCategories as $subCategory) {
                $data = $data->merge($subCategory->products);
            }

            $excludedIds = $data->pluck('id');

            $parentProducts = $this->products->whereNotIn('id', $excludedIds);

            $data = $data->merge($parentProducts);

            return $data;

        });

        return $totalProducts;
    }

    public static function getSelectOptions(bool $onlyParent = FALSE)
    {
        $list = [];

        $query = self::query();

        if ($onlyParent) {

            $query->where([
                ['parent_id', '=', NULL]
            ]);

        }

        foreach ($query->select(['id', 'name'])->get() as $category) {

            $list[$category->id] = $category->name;

        }

        return $list;
    }
}
