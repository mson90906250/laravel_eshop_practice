<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartStorage extends Model
{
    protected $table = 'cart_storage';

    protected $fillable = ['user_id'];
}
