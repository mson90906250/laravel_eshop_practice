<?php

namespace App\Cron\Workers;

use App\Models\User;
use Workerman\Timer;
use App\Models\Order;
use Workerman\Worker;
use App\Models\Report;
use Illuminate\Support\Facades\Redis;

$makeDailyReportWorker = new Worker();

$makeDailyReportWorker->name = 'makeDailyReportWorker';

$makeDailyReportWorker->onWorkerStart = function ($worker) {

    echo  getMessage($worker, '開始記錄每日統計');

    Timer::add(1, function () use ($worker) {

        //確認要記錄的日期是否已經存在
        $yesterdayTimestamp = strtotime('yesterday');

        $timeDiff = strtotime(date('Y-m-d 23:59:59')) - time();

        if (Report::where('recorded_time', '=', $yesterdayTimestamp)->exists()) {

            echo getMessage($worker, sprintf('當天記錄已存在: %s', date('Y-m-d', $yesterdayTimestamp)));

            sleep($timeDiff);

            return;

        }

        //當天註冊人數
        $dateStart = date('Y-m-d', $yesterdayTimestamp);

        $dateEnd = sprintf('%s 23:59:59', $dateStart);

        $totalRegistered = User::whereBetween('created_at', [$dateStart, $dateEnd])->count();

        $totalRevenue = Order::where('order_status', '=', Order::ORDER_STATUS_COMPLETE)
                                    ->whereBetween('created_at', [$dateStart, $dateEnd])->sum('total');

        $reportQuery = Report::create([
            'recorded_time' => $yesterdayTimestamp,
            'registered_number' => $totalRegistered,
            'revenue' => $totalRevenue
        ]);

        if ($reportQuery) {

            echo getMessage($worker, sprintf('成功記錄 %s 的統計', $dateStart));

            sleep($timeDiff);

            return;

        } else {

            echo getMessage($worker, sprintf('%s 的統計記錄失敗', $dateStart));

            return;

        }

    });

};

function getMessage(Worker $worker, $message)
{
    return sprintf('%s->%s %s', $worker->name, $message, PHP_EOL);
}

