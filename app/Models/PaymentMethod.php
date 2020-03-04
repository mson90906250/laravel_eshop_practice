<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    CONST STATUS_ON = 1;
    CONST STATUS_OFF = 2;

    public $timestamps = FALSE;

    public static function getStatusLabelList()
    {
        return [
            self::STATUS_OFF => '停用',
            self::STATUS_ON  => '啓用'
        ];
    }
}
