<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'middleware' => 'auth:custom_api',
], function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    //userInfo
    Route::post('/userInfo', 'UserInfoController@getUserInfo')->name('api.userInfo.getUserInfo');

});

//area
Route::get('/cities', 'AreaController@getCityList')->name('api.area.getCityList');

Route::get('/districts', 'AreaController@getCityDistricts')->name('api.area.getCityDistricts');

//comment
Route::post('/getMoreComments', 'CommentController@getMoreComments')->name('api.comment.getMoreComments');

