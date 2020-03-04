<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomController extends Controller {

    /**
     *
     * @param array|Collection      $items
     * @param int   $perPage
     * @param int  $page
     * @param array $options
     *
     * @return LengthAwarePaginator
     */
    public function paginate($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

        $items = $items instanceof Collection ? $items : Collection::make($items);

        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    /**
     * 取得表格需要用到的非關聯陣列
     *
     */
    public function getUnassociatedArray(iterable $data)
    {
        $newData = [];

        foreach ($data as $item) {

            $newData[] = $item;

        }

        return $newData;
    }

    protected function getReturnData(bool $status, $message, $data = NULL)
    {
        $returnData = [
            'status'    => $status,
            'message'   => $message,
        ];

        if ($data) {

            $returnData['data'] = $data;

        }

        return json_encode($returnData);
    }

}
