<?php

namespace App\Providers;

use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class PaymentMethodServiceProvider extends ServiceProvider {

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //判斷request是否來自console
        if (!$this->app->runningInConsole()) {

            //從表中載入可用的PaymentMethod
            $paymentMethodList = DB::table('payment_methods')
                                    ->select(['name'])
                                    ->where([
                                        ['status', '=', PaymentMethod::STATUS_ON]
                                    ])
                                    ->get();

            //註冊可用的PaymentMethod
            if ($paymentMethodList->isNotEmpty()) {

                foreach ($paymentMethodList as $paymentMethod) {

                    $this->app->bind($paymentMethod->name, function () use ($paymentMethod) {

                        $class = sprintf('\App\Logics\PaymentMethod\%s', $paymentMethod->name);

                        return new $class;

                    });

                }

            }

        }

    }

}
