<?php

namespace App\Http\Middleware;

use Closure;
use App\Logics\PermissionLogic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Front\CartController;
use Illuminate\Support\MessageBag;

class AdminCustomConfig
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $permissionLogic = resolve(PermissionLogic::class);

        $hasPermission = $permissionLogic->checkPermission($request, Auth::guard('admin')->user());

        if ($hasPermission) {

            return $next($request);

        }

        $errors = new MessageBag(['沒有使用此功能的權限']);

        return redirect()->back()->withErrors($errors);

    }
}
