<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Common\CustomController;

class UserController extends CustomController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $userList = $this->paginate($this->search($request), 10)
                            ->withPath(route('admin.user.index'))
                            ->appends(Request::capture()->except('page'));

        return view('admin.user.index', [
            'userList' => $userList
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return view('admin.user.show', [
            'user' => $user
        ]);
    }

    protected function search(Request $request)
    {
        $orderByList = ['nickname', 'email', 'id'];

        $validatedData = $request->validate([
            'nickname'  => ['nullable', 'string'],
            'email'     => ['nullable', 'string'],
            'order_by'  => ['nullable', 'string', Rule::in($orderByList)],
        ]);

        $userQuery = User::query();

        foreach ($validatedData as $attribute => $value) {

            switch ($attribute) {

                case 'nickname':

                    $userQuery->where($attribute, 'like', sprintf('%%%s%%', $value));

                    break;

                case 'email':

                    $userQuery->where($attribute, 'like', sprintf('%%%s%%', $value));

                    break;

                case 'order_by':

                    $userQuery->orderBy($value, $request->get('is_asc') ? 'asc' : 'desc');

                    break;

            }

        }

        return $userQuery->get();
    }
}
