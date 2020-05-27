<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Common\CustomController;
use App\Models\WishList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\MessageBag;

class WishListController extends CustomController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::guard('web')->user();

        $wishList = $this->paginate($user->wishList, 5)->withPath(route('wishList.index'))
                        ->appends(Request::capture()->except(['page']));

        return view('front.user.wish_list', [
            'wishList' => $wishList
        ]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            $user = Auth::guard('web')->user();

            $newWish = new WishList();

            $newWish->user_id = $user->id;

            $newWish->product_id = $request->id;

            $newWish->save();

        } catch (\Exception $e) {

            $errors = new MessageBag(['該商品已存在於願望清單中']);

            return redirect()->back()->withErrors($errors);

        }

        return redirect()->back()->with('status', '加入願望清單成功');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        WishList::destroy($id);

        return redirect()->back()->with('status', '成功移出願望清單');
    }
}
