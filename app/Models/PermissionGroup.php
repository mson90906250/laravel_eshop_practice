<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionGroup extends Model
{
    CONST STATUS_OFF = 2;
    CONST STATUS_ON = 1;

    CONST SUPER_ADMIN_GROUP_ID = 1;

    protected $fillable = ['name', 'status'];

    public $timestamps = FALSE;

    public function permissions()
    {
        return $this->belongsToMany('App\Models\Permission');
    }

    public static function getStatusLabels()
    {
        return [
            static::STATUS_ON => '開啓',
            static::STATUS_OFF => '關閉'
        ];
    }
}
