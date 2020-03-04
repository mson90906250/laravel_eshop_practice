<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;

class IndexController extends Controller
{
    public function welcome()
    {
        return view('front.welcome', [
            'title' => "Welcome To <br> Mark's Shop",
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Dolorum hic qui asperiores, quasi laboriosam blanditiis, provident ipsa, veritatis, recusandae repudiandae ipsum sequi perferendis repellendus perspiciatis enim obcaecati rem fuga esse!'
        ]);
    }

}
