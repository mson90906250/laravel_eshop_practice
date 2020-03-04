<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    CONST REDIS_DB_UNUSED_IMAGE_LIST = 'REDIS_DB_UNUSED_IMAGE_LIST';
    CONST REDIS_DIR_UNUSED_IMAGE_LIST = 'REDIS_DIR_UNUSED_IMAGE_LIST';

    protected $fillable = ['url'];

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }
}
