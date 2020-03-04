<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    CONST STATUS_ON = 1;
    CONST STATUS_OFF = 2;

    CONST TYPE_PERCENT = 1;
    CONST TYPE_NUMBER = 2;

    protected $fillable = ['title', 'code', 'remain', 'value_type', 'status', 'value', 'required_value', 'start_time', 'end_time'];

    public static function getStatusLabels()
    {
        return [
            self::STATUS_OFF => '關閉',
            self::STATUS_ON  => '啓用'
        ];
    }

    public static function getTypeLabels()
    {
        return [
            self::TYPE_PERCENT => '%d OFF',
            self::TYPE_NUMBER  => '折抵 %d'
        ];
    }

    public static function getTypeLabelsForShow()
    {
        return [
            self::TYPE_PERCENT => '折抵 %數',
            self::TYPE_NUMBER  => '折抵數值'
        ];
    }

    public static function getDiscountValue(Coupon $coupon)
    {
        switch ($coupon->value_type) {

            case self::TYPE_PERCENT:

                return sprintf('-%d%%', $coupon->value);

                break;

            case self::TYPE_NUMBER:

                return sprintf('-%d', $coupon->value);

                break;

        }
    }
}
