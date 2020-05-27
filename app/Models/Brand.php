<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Brand extends Model
{
    CONST CACHE_BRAND_OPTIONS = 'CACHE_BRAND_OPTIONS';

    CONST HAS_PRODUCTS_IN_STORE = Product::STATUS_ON;
    CONST NO_PRODUCTS_IN_STORE = Product::STATUS_OFF;

    CONST HAS_PRODUCTS = 1;
    CONST NO_PRODUCTS = 2;

    protected $fillable = ['name'];

    public function products()
    {
        return $this->hasMany('App\Models\Product');
    }

    public static function getSelectOptions()
    {
        $brandList = [];

        foreach (self::select(['id', 'name'])->get() as $brand) {

            $brandList[$brand->id] = $brand->name;

        }

        return $brandList;
    }

    public static function getHasProductsLabels()
    {
        return [
            static::HAS_PRODUCTS   => '有',
            static::NO_PRODUCTS    => '沒有'
        ];
    }

    public static function getHasProductsInStoreLabels()
    {
        return [
            static::HAS_PRODUCTS_IN_STORE   => '有',
            static::NO_PRODUCTS_IN_STORE    => '沒有'
        ];
    }
}
