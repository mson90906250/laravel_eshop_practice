<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'IndexController@welcome')->name('index.welcome');

//shop
Route::get('shop', 'ShopController@index')->name('shop.index');

Route::get('shop/{category}', 'ShopController@index')->name('shop.category')->where('category', '[0-9]+');

Route::get('shop/product/{product}', 'ShopController@show')->name('shop.show')->where('product', '[0-9]+');

//cart
Route::post('shop/cart', 'CartController@store')->name('cart.store'); //加入購物車的功能不需要登入

//login
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login.showLoginForm');

Route::post('login', 'Auth\LoginController@login')->name('login.login');

Route::post('logout', 'Auth\LoginController@logout')->name('login.logout');

//register
Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register.showRegisterForm');

Route::post('register', 'Auth\RegisterController@register')->name('register.register');

//confirmPassword
Route::get('password/confirm', 'Auth\ConfirmPasswordController@showConfirmForm')->name('confirmPassword.showConfirmForm');

Route::post('password/confirm', 'Auth\ConfirmPasswordController@confirm')->name('confirmPassword.confirm');

//forgotPassword
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('forgotPassword.showLinkRequestForm');

Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('forgotPassword.sendResetLinkEmail');

//resetPassword
Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('resetPassword.reset');

Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset'); //laravel本身寫死 所以不能改名

Route::group([

    'middleware' => 'auth:web'

], function () {

    //cart
    Route::get('shop/cart', 'CartController@index')->name('cart.index');

    Route::put('shop/cart', 'CartController@update')->name('cart.update');

    Route::delete('shop/cart', 'CartController@destroy')->name('cart.destroy');

    //coupon
    Route::post('shop/coupon', 'CartController@addCoupon')->name('cart.addCoupon');

    //order
    Route::get('shop/checkout', 'OrderController@create')->name('order.create');

    Route::post('shop/checkout', 'OrderController@store')->name('order.store');

    Route::get('user/order/{order}/confirm/{paymentMethod}', 'OrderController@thirdPartyConfirm')->name('order.thirdPartyConfirm');

    Route::get('user/order/{order}/cancel/{paymentMethod}', 'OrderController@thirdPartyCancel')->name('order.thirdPartyCancel');

    Route::get('user/order/{order}/refund/{paymentMethod}', 'OrderController@thirdPartyRefund')->name('order.thirdPartyRefund');

    //user
    Route::get('user', 'UserController@index')->name('user.index');

    Route::get('user/order', 'OrderController@index')->name('order.index');

    Route::get('user/order/{order}', 'OrderController@show')->name('order.show');

    Route::get('user/info', 'UserController@show')->name('user.show');

    Route::get('user/edit', 'UserController@edit')->name('user.edit');

    Route::put('user/edit', 'UserController@update')->name('user.update');

    //wish_list
    Route::get('wish-list', 'WishListController@index')->name('wishList.index');

    Route::post('wish-list', 'WishListController@store')->name('wishList.store');

    Route::delete('wish-list/{id}', 'WishListController@destroy')->name('wishList.destroy');

    //comment
    Route::post('comment/create', 'CommentController@store')->name('comment.store');

    Route::post('comment/destroy', 'CommentController@destroy')->name('comment.destroy');

});




