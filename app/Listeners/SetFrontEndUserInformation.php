<?php

namespace App\Listeners;

use App\Models\CartStorage;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Darryldecode\Cart\Facades\CartFacade as Cart;

class SetFrontEndUserInformation
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
       if ($event->guard === 'web') {

            //設定user的購物車
            $user = Auth::guard('web')->user();

            if (CartStorage::where('user_id', $user->id)->first()) {

                $cartItems = unserialize(CartStorage::where('user_id', $user->id)->first()->cart_data);

                $sessionKey = Cookie::get('cart_cookie');

                if (!$sessionKey) {

                    $sessionKey = sprintf('cart_%s',Hash::make(sprintf('%d_%s', $user->id, $user->name)));

                    $cart = Cart::session($sessionKey);

                    $cart->add($cartItems);

                } else {

                    $cart = Cart::session($sessionKey);

                    $cart->add($cartItems);

                }

                Cookie::queue('cart_cookie', $sessionKey, 20);

            }

       }

    }

}
