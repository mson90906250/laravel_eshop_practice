<?php
namespace App\Logics\Reporter;

use DateTime;
use App\Models\User;
use App\Models\Order;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class MonthlyReporter implements ReporterInterface
{
    CONST CACHE_MONTHLY_TOP10_PRODUCTS = 'CACHE_MONTHLY_TOP10_PRODUCTS:%s'; //%s 接年月 ex: 2020-05

    public function report(Request $request)
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
}
