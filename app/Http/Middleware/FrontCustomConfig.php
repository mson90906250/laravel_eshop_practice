<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Front\CartController;
use Closure;
use Illuminate\Support\Facades\View;

class FrontCustomConfig
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
        //設定購物車
        CartController::setCart(); //只要每次進入前臺都會設定cart

        return $next($request);
    }
}
