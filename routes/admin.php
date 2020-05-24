<?php

use Illuminate\Support\Facades\Route;

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

Route::group([

    'middleware' => 'auth:admin'

], function () {

    Route::get('/', 'IndexController@index')->name('admin.index.index');

    //admin
    Route::get('/administrator', 'AdminController@index')->name('admin.admin.index');

    Route::get('/administrator/create', 'AdminController@create')->name('admin.admin.create');

    Route::post('/administrator/create', 'AdminController@store')->name('admin.admin.store');

    Route::get('/administrator/{admin}', 'AdminController@show')->name('admin.admin.show');

    Route::get('/administrator/{admin}/edit', 'AdminController@edit')->name('admin.admin.edit');

    Route::put('/administrator/{admin}/edit', 'AdminController@update')->name('admin.admin.update');

    Route::put('/administrator/edit', 'AdminController@updateStatus')->name('admin.admin.updateStatus');

    Route::delete('/administrator/delete', 'AdminController@destroy')->name('admin.admin.destroy');

    //permission
    Route::get('/permission', 'PermissionController@index')->name('admin.permission.index');

    Route::put('/permission/edit', 'PermissionController@update')->name('admin.permission.update');

    Route::get('/permission_group', 'PermissionGroupController@index')->name('admin.permissionGroup.index');

    //permission_group
    Route::get('/permission_group/{permissionGroup?}', 'PermissionGroupController@show')->name('admin.permissionGroup.show')->where(['permissionGroup' => '[0-9]+']);

    Route::get('/permission_group/{permissionGroup}/edit', 'PermissionGroupController@edit')->name('admin.permissionGroup.edit')->where(['permissionGroup' => '[0-9]+']);

    Route::put('/permission_group/{permissionGroup}/edit', 'PermissionGroupController@update')->name('admin.permissionGroup.update')->where(['permissionGroup' => '[0-9]+']);

    Route::get('/permission_group/create', 'PermissionGroupController@create')->name('admin.permissionGroup.create');

    Route::post('/permission_group/create', 'PermissionGroupController@store')->name('admin.permissionGroup.store');

    Route::put('/permission_group/edit', 'PermissionGroupController@updateStatus')->name('admin.permissionGroup.updateStatus');

    Route::delete('/permission_group/delete', 'PermissionGroupController@destroy')->name('admin.permissionGroup.destroy');

    //user
    Route::get('/user', 'UserController@index')->name('admin.user.index');

    Route::get('/user/{user}', 'UserController@show')->name('admin.user.show');

    //order
    Route::get('/order', 'OrderController@index')->name('admin.order.index');

    Route::get('/order/{order}', 'OrderController@show')->name('admin.order.show');

    Route::get('/order/{order}/edit', 'OrderController@edit')->name('admin.order.edit');

    Route::put('/order/{order}/edit', 'OrderController@update')->name('admin.order.update');

    Route::get('/order/{order}/cancel', 'OrderController@cancel')->name('admin.order.cancel');

    //payment_method
    Route::get('/payment_method', 'PaymentMethodController@index')->name('admin.paymentMethod.index');

    Route::put('/payment_method', 'PaymentMethodController@updateStatus')->name('admin.paymentMethod.updateStatus');

    //product
    Route::get('/product', 'ProductController@index')->name('admin.product.index');

    Route::get('/product/{product}', 'ProductController@show')->name('admin.product.show')->where(['product' => '[0-9]+']);

    Route::get('/product/create', 'ProductController@create')->name('admin.product.create');

    Route::post('/product/create', 'ProductController@store')->name('admin.product.store');

    Route::get('/product/{product}/edit', 'ProductController@edit')->name('admin.product.edit');

    Route::put('/product/{product}/edit', 'ProductController@update')->name('admin.product.update');

    Route::put('/product/edit', 'ProductController@updateStatus')->name('admin.product.updateStatus');

    Route::delete('/product/delete', 'ProductController@destroy')->name('admin.product.destroy');

    Route::post('/product/storeImage', 'ProductController@storeImage')->name('admin.product.storeImage');

    Route::post('/product/deleteImage', 'ProductController@deleteImage')->name('admin.product.deleteImage');

    Route::post('/product/deleteStock', 'ProductController@deleteStock')->name('admin.product.deleteStock');

    //brand
    Route::get('/brand', 'BrandController@index')->name('admin.brand.index');

    Route::get('/brand/create', 'BrandController@create')->name('admin.brand.create');

    Route::post('/brand/create', 'BrandController@store')->name('admin.brand.store');

    Route::get('/brand/{brand}/edit', 'BrandController@edit')->name('admin.brand.edit');

    Route::put('/brand/{brand}/edit', 'BrandController@update')->name('admin.brand.update');

    Route::delete('/brand/delete', 'BrandController@destroy')->name('admin.brand.destroy');

    //category
    Route::get('/category', 'CategoryController@index')->name('admin.category.index');

    Route::get('/category/create', 'CategoryController@create')->name('admin.category.create');

    Route::post('/category/create', 'CategoryController@store')->name('admin.category.store');

    Route::get('/category/{category}/edit', 'CategoryController@edit')->name('admin.category.edit');

    Route::put('/category/{category}/edit', 'CategoryController@update')->name('admin.category.update');

    Route::delete('/category/delete', 'CategoryController@destroy')->name('admin.category.destroy');

    //comment
    Route::get('/comment', 'CommentController@index')->name('admin.comment.index');

    Route::delete('/comment/delete', 'CommentController@destroy')->name('admin.comment.destroy');

    //coupon
    Route::get('/coupon', 'CouponController@index')->name('admin.coupon.index');

    Route::get('/coupon/create', 'CouponController@create')->name('admin.coupon.create');

    Route::post('/coupon/create', 'CouponController@store')->name('admin.coupon.store');

    Route::get('/coupon/{coupon}/edit', 'CouponController@edit')->name('admin.coupon.edit');

    Route::put('/coupon/{coupon}/edit', 'CouponController@update')->name('admin.coupon.update');

    Route::put('/coupon/updateStatus', 'CouponController@updateStatus')->name('admin.coupon.updateStatus');

    Route::delete('/coupon/delele', 'CouponController@destroy')->name('admin.coupon.destroy');

    //shippingFee
    Route::get('/shipping_fee', 'ShippingFeeController@index')->name('admin.shippingFee.index');

    Route::get('/shipping_fee/create', 'ShippingFeeController@create')->name('admin.shippingFee.create');

    Route::post('/shipping_fee/create', 'ShippingFeeController@store')->name('admin.shippingFee.store');

    Route::get('/shipping_fee/{shippingFee}/edit', 'ShippingFeeController@edit')->name('admin.shippingFee.edit');

    Route::put('/shipping_fee/{shippingFee}/edit', 'ShippingFeeController@update')->name('admin.shippingFee.update');

    Route::put('/shipping_fee/updateStatus', 'ShippingFeeController@updateStatus')->name('admin.shippingFee.updateStatus');

    Route::delete('/shipping_fee/delete', 'ShippingFeeController@destroy')->name('admin.shippingFee.destroy');
});


//login
Route::get('login', 'Auth\LoginController@showLoginForm')->name('admin.login.showLoginForm');

Route::post('login', 'Auth\LoginController@login')->name('admin.login.login');

Route::post('logout', 'Auth\LoginController@logout')->name('admin.login.logout');
