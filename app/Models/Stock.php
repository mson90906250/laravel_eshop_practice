<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = ['product_id', 'attribute', 'quantity', 'price', 'image_id'];

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

    public function image()
    {
        return $this->belongsTo('App\Models\Image');
    }
}
