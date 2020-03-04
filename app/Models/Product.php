<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    CONST STATUS_ON = 1;
    CONST STATUS_OFF = 2;

    protected $fillable = ['name', 'brand_id', 'original_price', 'description', 'status'];

    public function images()
    {
        return $this->hasMany('App\Models\Image');
    }

    public function categories()
    {
        return $this->belongsToMany('App\Models\Category');
    }

    public function stocks()
    {
        return $this->hasMany('App\Models\Stock');
    }

    public function brand()
    {
        return $this->belongsTo('App\Models\Brand');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }

    public function getFirstImageAttribute()
    {
        return $this->images()->where('is_first_image', TRUE)->first();
    }

    public static function getStatusLabels()
    {
        return [
            self::STATUS_ON     => '上架',
            self::STATUS_OFF    => '下架'
        ];
    }

    public function getAttributeLabelsForShow(array $except = [])
    {
        return [
            'name'              => '商品名稱',
            'brand_id'          => '品牌',
            'original_price'    => '價格(原價)',
            'description'       => '簡介',
            'created_at'        => '創建日期',
            'updated_at'        => '更新日期',
            'status'            => '狀態'
        ];
    }
}
