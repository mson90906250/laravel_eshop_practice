<?php

namespace App\Http\Controllers\Admin;

use DateTime;
use App\Models\User;
use App\Models\Order;
use App\Models\Report;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class IndexController extends Controller
{
    public function index()
    {
        //今日統計
        $time = new DateTime(date('Y-m-d'));

        //今日註冊人數
        $todayTotalRegistered = User::whereBetween('created_at', [$time->format('Y-m-d'), $time->format('Y-m-d 23:59:59')])->count();

        //今日營收
        $todayTotalRevenue = Order::where('order_status', '=', Order::ORDER_STATUS_COMPLETE)
                                    ->whereBetween('created_at', [$time->format('Y-m-d'), $time->format('Y-m-d 23:59:59')])->sum('total');

        $monthlyQuery = Report::whereBetween('recorded_time', [strtotime($time->format('Y-m-01')), strtotime($time->format('Y-m-t 23:59:59'))]);

        //本月註冊人數
        $monthlyTotalRegistered = $monthlyQuery->sum('registered_number') + $todayTotalRegistered;

        //本月營收
        $monthlyTotalRevenue = $monthlyQuery->sum('revenue') + $todayTotalRevenue;

        //本月每日營收
        $revenuePerDay = $monthlyQuery->select('recorded_time', 'revenue')->get();

        $dayRange = range(1, date('t'));

        $lineChartData = [];

        //如果資料不完整(ex: 當月有一天沒有資料)的話 data會跑掉
        foreach ($dayRange as $day) {

            $report = $revenuePerDay->where('recorded_time', strtotime(date(sprintf('Y-m-%d', $day))))->first();

            if ($day == date('d')) {

                $lineChartData[] = $todayTotalRevenue;

                continue;

            }

            $lineChartData[] = $report ? $report->revenue : 0;

        }

        //本月銷售top 10
        $top10Products = DB::table('stocks as s')
                                ->join('order_stock as os', 's.id', '=', 'os.stock_id')
                                ->join('orders as o', 'os.order_id', '=', 'o.id')
                                ->join('products as p', 's.product_id', '=', 'p.id')
                                ->select('p.name', 's.attribute')
                                ->selectRaw('SUM(os.quantity) AS total_quantity')
                                ->where('o.order_status', '=', Order::ORDER_STATUS_COMPLETE)
                                ->whereBetween('o.created_at', [$time->format('Y-m-01 00:00:00'), $time->format('Y-m-t 23:59:59')])
                                ->orderBy('total_quantity', 'desc')
                                ->groupBy('s.id')
                                ->limit(10)
                                ->get();

        $productNameList = [];

        $productQuantityList = [];

        foreach ($top10Products as $product) {

            $productNameList[] = $product->attribute ? sprintf('%s (%s)', $product->name, $product->attribute) : $product->name;

            $productQuantityList[] = $product->total_quantity;

        }

        return view('admin.index', [
            'todayTotalRegistered' => $todayTotalRegistered,
            'todayTotalRevenue' => $todayTotalRevenue,
            'monthlyTotalRegistered' => $monthlyTotalRegistered,
            'monthlyTotalRevenue' => $monthlyTotalRevenue,
            'dayRangeJson' => json_encode($dayRange),
            'lineChartDataJson' => json_encode($lineChartData),
            'productNameListJson' => json_encode($productNameList),
            'productQuantityListJson' => json_encode($productQuantityList)
        ]);
    }
}
