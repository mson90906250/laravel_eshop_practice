<?php

namespace App\Providers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Collective\Html\FormFacade as Form;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (App::environment('local') && env('APP_URL') == 'http://localhost') {
            Event::listen('Illuminate\Database\Events\QueryExecuted', function ($query) {
                // filter oauth ones
                if (!Str::contains($query->sql, 'oauth')) {
                    Log::debug($query->sql . ' - ' . serialize($query->bindings));
                }
            });
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
       // form component
       Form::component('bsSelect', 'components.form.select', ['label', 'name', 'value' => '', 'options', 'prompt' => '全部', 'direction' => 'horizontal']);
       Form::component('bsText', 'components.form.text', ['label', 'name', 'value' => '', 'direction' => 'horizontal']);
    }
}
