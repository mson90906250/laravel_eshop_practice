<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UserInfoController extends Controller {

    public function getUserInfo()
    {
        $user = Auth::guard('custom_api')->user();

        return $user->toJson();
    }

}
