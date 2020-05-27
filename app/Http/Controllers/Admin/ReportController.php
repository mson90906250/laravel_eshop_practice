<?php

namespace App\Http\Controllers\Admin;

use DateTime;
use App\Models\User;
use App\Models\Order;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Common\CustomController;
use Illuminate\Support\Facades\Cache;
use App\Logics\Reporter\ReporterInterface;

class ReportController extends CustomController
{
    CONST CACHE_MONTHLY_TOP10_PRODUCTS = 'CACHE_MONTHLY_TOP10_PRODUCTS:%s'; //%s 接年月 ex: 2020-05
    CONST CACHE_ANNUAL_TOP10_PRODUCTS = 'CACHE_ANNUAL_TOP10_PRODUCTS:%s'; //%s 接年份 ex: 2020

    public function monthlyReport(Request $request)
    {
        $validatedData = $request->validate([
            'date' => ['nullable', 'date']
        ]);

        $time = new DateTime($validatedData['date'] ?? date('Y-m-d'));

        //當月註冊人數
        $monthlyQuery = Report::whereBetween('recorded_time', [strtotime($time->format('Y-m-01')), strtotime($time->format('Y-m-t 23:59:59'))]);

        $monthlyTotalRegistered = $monthlyQuery->sum('registered_number');

        //當月營收
        $monthlyTotalRevenue = $monthlyQuery->sum('revenue');

        //當月每日營收
        $revenuePerDay = $monthlyQuery->get();

        $dayRange = range(1, date('t'));

        $lineChartData = [];

        //如果資料不完整(ex: 當月有一天沒有資料)的話 data會跑掉
        foreach ($dayRange as $day) {

            $report = $revenuePerDay->where('recorded_time', strtotime(date($time->format(sprintf('Y-m-%d', $day)))))->first();

            //取得當天資料
            if ($time->format('d') == $day) {

                $selectedDatetotalRegistered = $report ? $report->registered_number : 0;

                $selectedDateTotalRevenue = $report ? $report->revenue : 0;

            }

            $lineChartData[] = $report ? $report->revenue : 0;

        }

        //如果時間為這個月 要將今日的統計一起納入
        if ($time->format('Y-m') == date('Y-m')) {

            //今日註冊人數
            $todayTotalRegistered = User::whereBetween('created_at', [date('Y-m-d'), date('Y-m-d 23:59:59')])
                                            ->count();

            //今日營收
            $todayTotalRevenue = Order::where('order_status', '=', Order::ORDER_STATUS_COMPLETE)
                                        ->whereBetween('created_at', [date('Y-m-d'), date('Y-m-d 23:59:59')])
                                        ->sum('total');

            $monthlyTotalRegistered += $todayTotalRegistered;

            $monthlyTotalRevenue += $todayTotalRevenue;

             //如果選擇的日期剛好為今天
            if ($time->format('d') == date('d')) {

                $lineChartData[date('d') - 1] = $todayTotalRevenue;

                $selectedDatetotalRegistered = $todayTotalRegistered;

                $selectedDateTotalRevenue = $todayTotalRevenue;

            }

        }

        //當月銷售top 10
        //如果時間非這個月 將cache時間拉長一個月
        $top10Products = Cache::remember(
                                        sprintf(static::CACHE_MONTHLY_TOP10_PRODUCTS, $time->format('Y-m')),
                                        $time->format('Y-m') == date('Y-m') ? 30 : 7 * 86400,
                                        function () use ($time) {

                                            return DB::table('stocks as s')
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

                                        });




        $productNameList = [];

        $productQuantityList = [];

        foreach ($top10Products as $product) {

            $productNameList[] = $product->attribute ? sprintf('%s (%s)', $product->name, $product->attribute) : $product->name;

            $productQuantityList[] = $product->total_quantity;

        }

        return view('admin.report.monthly_report', [
            'selectedDateTotalRegistered' => $selectedDatetotalRegistered,
            'selectedDateTotalRevenue' => $selectedDateTotalRevenue,
            'monthlyTotalRegistered' => $monthlyTotalRegistered,
            'monthlyTotalRevenue' => $monthlyTotalRevenue,
            'dayRangeJson' => json_encode($dayRange),
            'lineChartDataJson' => json_encode($lineChartData),
            'productNameListJson' => json_encode($productNameList),
            'productQuantityListJson' => json_encode($productQuantityList)
        ]);
    }

    public function annualReport(Request $request)
    {
        $validatedData = $request->validate([
            'year' => ['nullable', 'date_format:Y']
        ]);

        $year = new DateTime(sprintf('%d-01-01', $validatedData['year'] ?? date('Y')));

        $timeStart = strtotime($year->format('Y-01-01 00:00:00'));

        $timeEnd = strtotime($year->format('Y-12-31 23:59:59'));

        $reportPerMonth = Report::whereBetween('recorded_time', [$timeStart, $timeEnd])
                                    ->selectRaw('SUM(registered_number) AS total_registered_number, SUM(revenue) AS total_revenue, MONTH(FROM_UNIXTIME(recorded_time)) AS month')
                                    ->groupBy('month')
                                    ->get();

        $monthRange = range(1, 12);

        $selectedYearTotalRegistered = 0;

        $selectedYearTotalRevenue = 0;

        $lineChartData = [];

        foreach ($monthRange as $month) {

            $report = $reportPerMonth->where('month', $month)->first();

            if ($report) {

                $lineChartData[] = $report->total_revenue;

                $selectedYearTotalRevenue += $report->total_revenue;

                $selectedYearTotalRegistered += $report->total_registered_number;

                continue;

            }

            $lineChartData[] = 0;

        }

        //如果時間為今年 要將今日的數據納入
        if ($year->format('Y') == date('Y')) {

            //今日註冊人數
            $todayTotalRegistered = User::whereBetween('created_at', [date('Y-m-d'), date('Y-m-d 23:59:59')])
                                            ->count();

            //今日營收
            $todayTotalRevenue = Order::where('order_status', '=', Order::ORDER_STATUS_COMPLETE)
                                        ->whereBetween('created_at', [date('Y-m-d'), date('Y-m-d 23:59:59')])
                                        ->sum('total');

            $selectedYearTotalRegistered += $todayTotalRegistered;

            $selectedYearTotalRevenue += $todayTotalRevenue;

        }

        //當月銷售top 10
        // 如果時間非這個月 將cache時間拉長一個月
        $top10Products = Cache::remember(
                                        sprintf(static::CACHE_ANNUAL_TOP10_PRODUCTS, $year->format('Y')),
                                        $year->format('Y') == date('Y') ? 30 : 86400 * 30,
                                        function () use ($year) {

                                            return DB::table('stocks as s')
                                                            ->join('order_stock as os', 's.id', '=', 'os.stock_id')
                                                            ->join('orders as o', 'os.order_id', '=', 'o.id')
                                                            ->join('products as p', 's.product_id', '=', 'p.id')
                                                            ->select('p.name', 's.attribute')
                                                            ->selectRaw('SUM(os.quantity) AS total_quantity')
                                                            ->where('o.order_status', '=', Order::ORDER_STATUS_COMPLETE)
                                                            ->whereBetween('o.created_at', [$year->format('Y-01-01 00:00:00'), $year->format('Y-12-31 23:59:59')])
                                                            ->orderBy('total_quantity', 'desc')
                                                            ->groupBy('s.id')
                                                            ->limit(10)
                                                            ->get();

                                        });



        $productNameList = [];

        $productQuantityList = [];

        foreach ($top10Products as $product) {

            $productNameList[] = $product->attribute ? sprintf('%s (%s)', $product->name, $product->attribute) : $product->name;

            $productQuantityList[] = $product->total_quantity;

        }

        return view('admin.report.annual_report', [
            'selectedYearTotalRegistered' => $selectedYearTotalRegistered,
            'selectedYearTotalRevenue' => $selectedYearTotalRevenue,
            'monthRangeJson' => json_encode($monthRange),
            'lineChartDataJson' => json_encode($lineChartData),
            'productNameListJson' => json_encode($productNameList),
            'productQuantityListJson' => json_encode($productQuantityList)
        ]);
    }

}
