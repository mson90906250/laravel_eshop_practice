<?php

namespace App\Models;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use Notifiable;

    CONST SUPER_ADMIN_ID = 1;

    CONST STATUS_ON = 1;
    CONST STATUS_OFF = 2;

    protected $table = 'admins';
    protected $fillable = ['username', 'password', 'status'];

    public function permission_groups()
    {
        return $this->belongsToMany('App\Models\PermissionGroup');
    }

    public function GetAvailablePermissionsAttribute()
    {
        return Permission::whereHas('permission_groups', function (Builder $query) {
            $query->whereIn('permission_groups.id', $this->permission_groups()->pluck('permission_groups.id')->toArray());
        })->get();
    }

    public static function getStatusLabels()
    {
        return [
            static::STATUS_ON => '開啓',
            static::STATUS_OFF => '關閉'
        ];
    }

}
