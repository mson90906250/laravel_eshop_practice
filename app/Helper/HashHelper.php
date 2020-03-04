<?php

namespace App\Helper;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;

class HashHelper {

    public static function makeApiToken($userId, $timestamp)
    {
        $apiTokenKey = Config::get('custom.api_token_key');

        return Hash::make(sprintf('%d%s%s', $userId, $apiTokenKey, $timestamp));
    }

    public static function checkApiToken($token, $userId, $timestamp)
    {
        return $token === static::makeApiToken($userId, $timestamp);
    }

}
