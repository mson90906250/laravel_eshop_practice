<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    CONST STATUS_OFF = 2;
    CONST STATUS_ON = 1;

    public $timestamps = FALSE;

    protected $fillable = ['controller', 'action', 'status'];

    public function permission_groups()
    {
        return $this->belongsToMany('App\Models\PermissionGroup');
    }

    public static function getStatusLabels()
    {
        return [
            static::STATUS_ON => '開啓',
            static::STATUS_OFF => '關閉',
        ];
    }
}
