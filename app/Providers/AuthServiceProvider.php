<?php

namespace App\Providers;

use App\Common\ApiTokenGuard;
use App\Common\ApiUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // 註冊custom_api的auth
        Auth::extend('custom_token', function () {

            $request = request();

            $provider = new ApiUserProvider($request->get('user_id'), $request->get('timestamp'));

            return new ApiTokenGuard($provider, $request);

        });
    }
}
