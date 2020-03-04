<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;

class AreaController extends Controller {

    protected $cityList;

    public function __construct()
    {
        $this->cityList = Config::get('custom.city_list');
    }

    public function getCityList()
    {
        return array_keys($this->cityList);
    }

    public function getCityDistricts()
    {
        $city = Request::get('city');

        return $city ? $this->cityList[$city] : [];
    }

}
