<?php

namespace App\Http\Controllers\Front;


use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\Common\CustomController;

class UserController extends CustomController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('front.user.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        return view('front.user.user_detail', [
            'user' => Auth::guard('web')->user()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        $user = Auth::guard('web')->user();

        return view('front.user.edit', ['user' => $user]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update()
    {
        $validatedValue = Request::validate([
            'nickname'      => ['required', 'string'],
            'first_name'    => ['nullable', 'string'],
            'last_name'     => ['nullable', 'string'],
            'email'         => ['required', 'email:rfc', function ($attribute, $value, $fail) {

                $hasBeenUsed = User::where([
                    [$attribute, '=', $value],
                    ['id', '!=', Auth::guard('web')->user()->id]
                ])
                ->exists();

                //驗證信箱是否已被使用
                if ($hasBeenUsed) {

                    $fail(sprintf('%s已被使用', $attribute));

                }


            }],
            'phone_number'  => ['nullable', 'regex:/^09[0-9]{8}$/'],
            'city'          => ['nullable', 'string', Rule::in(array_keys(Config::get('custom.city_list')))],
            'district'      => ['nullable', 'string', Rule::requiredIf(function () {

                $input = Request::get('city');

                $districts = Config::get('custom.city_list');

                return isset($districts[$input]);

            })],
            'address'       => ['nullable', 'string', Rule::requiredIf(function () {

                $input = Request::get('city');

                return boolval($input);
            })],
        ]);

        $user = Auth::guard('web')->user();

        if ($user->update($validatedValue)) {

            return redirect(route('user.show'))->with('status', '更新成功');

        }

    }

}
