<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ShippingFee extends Model
{
    CONST TYPE_STORE_PICKUP = 1;
    CONST TYPE_HOME_DELIVERY = 2;

    CONST STATUS_ON = 1;
    CONST STATUS_OFF = 2;

    protected $table = 'shipping_fee';

    public $timestamps = FALSE;

    public static function getStatusLabels()
    {
        return [
            self::STATUS_ON => '開啓',
            self::STATUS_OFF => '關閉'
        ];
    }

    public static function getTypeList()
    {
        return [
            self::TYPE_STORE_PICKUP => '超商取貨',
            self::TYPE_HOME_DELIVERY => '宅配到府'
        ];
    }

    /**
     * 根據購物車的subTotal來得到每個運送方式的最佳運費
     */
    public static function getShippingFeeList($subTotal, $type = NULL)
    {
        $query = DB::table('shipping_fee')
                            ->select(['type'])
                            ->selectRaw('MIN(value) as min_value')
                            ->where([
                                ['required_value', '<=', $subTotal],
                                ['status', '=', self::STATUS_ON]
                            ])
                            ->groupBy('type');


        if ($type) {

            $query = $query->where([
                        ['type', '=', $type]
                    ]);

        }

        $queryList = $query->get();

        $shippingFeeList = [];

        array_walk_recursive($queryList, function ($item) use (&$shippingFeeList) {

            $shippingFeeList[$item->type] = $item->min_value;

        });

        return $shippingFeeList;
    }
}
