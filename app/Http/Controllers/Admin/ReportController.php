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
    protected $reporter;

    public function __construct(ReporterInterface $reporter)
    {
        $this->reporter = $reporter;
    }

    public function report(Request $request)
    {
        return $this->reporter->report($request);
    }

}
