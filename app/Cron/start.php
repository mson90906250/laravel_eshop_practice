<?php

require_once '../../vendor/autoload.php';
require_once '../../vendor/workerman/workerman/Autoloader.php';

// laravel 爲了使用facade
$app = require_once '../../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
//


use Workerman\Worker;

foreach (glob('Workers/*.php') as $worker) {

    require_once $worker;

}

Worker::runAll();



