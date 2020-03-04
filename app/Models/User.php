<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements CustomModelInterface
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nickname', 'email', 'password', 'first_name', 'last_name', 'city', 'district', 'address', 'phone_number'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany('App\Models\Order');
    }

    public function wishList()
    {
        return $this->hasMany('App\Models\WishList');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }

    public function getFullAddressAttribute()
    {
        return sprintf('%s%s%s', $this->city, $this->district, $this->address);
    }

    public function getFullNameAttribute()
    {
        return sprintf('%s%s', $this->last_name, $this->first_name);
    }

    public static function getAttributeLabelsForShow()
    {
        return [
            'nickname'      => '暱稱',
            'email'         => '信箱',
            'full_name'     => '姓名',
            'phone_number'  => '電話',
            'full_address'  => '地址',
        ];
    }
}
