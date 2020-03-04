<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model implements CustomModelInterface{

    CONST PAYMENT_STATUS_NOT_PAID = 1;
    CONST PAYMENT_STATUS_HAS_PAID_NOT_AUTHORIZED = 2;
    CONST PAYMENT_STATUS_HAS_PAID_AND_AUTHORIZED = 3;
    CONST PAYMENT_STATUS_HAS_PAID_AND_CONFIRMED = 4;
    CONST PAYMENT_STATUS_REFUNDED = 5;

    CONST ORDER_STATUS_PROCESSING = 1;
    CONST ORDER_STATUS_PICKING = 2;
    CONST ORDER_STATUS_SHIPPING = 3;
    CONST ORDER_STATUS_CANCEL = 4;
    CONST ORDER_STATUS_COMPLETE = 5;

    protected static $orderByList = ['order_number', 'order_status', 'created_at', 'total'];
    protected $fillable = ['order_number', 'user_id', 'total', 'shipping_fee', 'coupon_discount', 'order_status', 'payment_status', 'city', 'district', 'address', 'payment_method'];


    public static function getPaymentStatusList()
    {
        return [
            self::PAYMENT_STATUS_NOT_PAID => '未付款',
            self::PAYMENT_STATUS_HAS_PAID_NOT_AUTHORIZED => '已付款, 未授權',
            self::PAYMENT_STATUS_HAS_PAID_AND_AUTHORIZED => '已付款, 已授權', //未請款
            self::PAYMENT_STATUS_HAS_PAID_AND_CONFIRMED => '已付款, 已請款',
            self::PAYMENT_STATUS_REFUNDED => '已還款'
        ];
    }

    public static function getOrderStatusList()
    {
        return [
            self::ORDER_STATUS_PROCESSING => '處理中',
            self::ORDER_STATUS_PICKING => '檢貨中',
            self::ORDER_STATUS_SHIPPING => '運送中',
            self::ORDER_STATUS_CANCEL => '取消',
            self::ORDER_STATUS_COMPLETE => '完成',
        ];
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function stocks()
    {
        return $this->belongsToMany('App\Models\Stock')->withPivot('quantity');
    }

    public function paymentMethod()
    {
        return $this->belongsTo('App\Models\PaymentMethod', 'payment_method', 'id');
    }

    public function getOrderStatusLabelAttribute()
    {
        return static::getOrderStatusList()[$this->order_status];
    }

    public function getPaymentStatusLabelAttribute()
    {
        return static::getPaymentStatusList()[$this->payment_status];
    }

    public function getPaymentMethodLabelAttribute()
    {
        return $this->paymentMethod->name;
    }

    public function getFullAddressAttribute()
    {
        return sprintf('%s%s%s', $this->city, $this->district, $this->address);
    }

    public static function getOrderByList()
    {
        return self::$orderByList;
    }

    public static function getAttributeLabelsForShow(array $except = [])
    {
        return [
            'order_number'              => '訂單號',
            'nickname'                  => '用戶名稱',
            'order_status_label'        => '訂單狀態',
            'payment_status_label'      => '付款狀態',
            'payment_method_label'      => '支付方法',
            'full_address'              => '地址',
            'total'                     => '訂單總額',
            'shipping_fee'              => '運費',
            'coupon_discount'           => '優惠折抵',
            'data'                      => '其他資訊',
            'created_at'                => '創建日期',
            'updated_at'                => '更新日期',
        ];
    }


}
