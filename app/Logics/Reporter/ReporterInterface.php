<?php
namespace App\Logics\Reporter;

use Illuminate\Http\Request;

interface ReporterInterface
{
    public function report(Request $request);
}
