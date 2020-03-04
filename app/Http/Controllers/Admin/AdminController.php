<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Models\PermissionGroup;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Common\CustomController;

class AdminController extends CustomController
{
    // TODO: 修改權限管理:擁有權限管理的管理員不應可以賦予自己superadmin的群組

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // dd($request->all());

        $adminList = $this->paginate($this->search($request), 10)
                        ->withPath(route('admin.admin.index'))
                        ->appends(Request::capture()->except('page'));

        //取得權限群組列表
        $permissionGroupList = [];

        foreach (PermissionGroup::all() as $group) {

            $permissionGroupList[$group->id] = $group->name;

        }

        return view('admin.admin.index', [
            'adminList' => $adminList,
            'permissionGroupList' => $permissionGroupList
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //取得權限群組列表
        $permissionGroupList = [];

        foreach (PermissionGroup::all() as $group) {

            $permissionGroupList[$group->id] = $group->name;

        }

        return view('admin.admin.create', [
            'permissionGroupList' => $permissionGroupList
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'username'              => ['required', 'string', 'unique:admins,username'],
            'password'              => ['required', 'string', 'min:8', function ($attribute, $value, $fail) use ($request) {

                if ($value !== $request->get('confirm_password')) {

                    $fail('密碼與確認密碼不符');

                }

            }],
            'status'                => ['required', 'integer', Rule::in(array_keys(Admin::getStatusLabels()))],
            'permission_group'      => ['required', 'array'],
            'permission_group.*'    => ['required', 'integer', 'exists:permission_groups,id']
        ]);

        $admin = new Admin([
            'username' => $validatedData['username'],
            'password' => Hash::make($validatedData['password']),
            'status'   => $validatedData['status']
        ]);

        if ($admin->save()) {

            $admin->permission_groups()->attach($validatedData['permission_group']);

        }

        return redirect(route('admin.admin.index'))->with('status', '創建管理員成功');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function show(Admin $admin)
    {
        return view('admin.admin.show', [
            'admin' => $admin
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function edit(Admin $admin)
    {
        //取得權限群組列表
        $permissionGroupList = [];

        foreach (PermissionGroup::all() as $group) {

            $permissionGroupList[$group->id] = $group->name;

        }

        return view('admin.admin.edit', [
            'admin' => $admin,
            'permissionGroupList' => $permissionGroupList,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Admin $admin)
    {
        $validatedData = $request->validate([

            'username' => ['required', 'string', function ($attribute, $value, $fail) use ($admin) {

                if ($admin->username !== $value
                    && Admin::where('username', $value)->exists()) {

                    $fail('此名稱已被使用, 請更換');
                }

            }],

            'old_password' => [function ($attribute, $value, $fail) use ($request, $admin) {

                if ($request->get('password') != NULL) {

                    if (Hash::check($value, $admin->password)) {

                        return TRUE;

                    }

                    $fail('密碼錯誤');

                }

                return TRUE;

            }],

            'password' => ['nullable', 'string', 'same:confirm_password'],
            'status'   => [Rule::requiredIf($admin->id !== ADMIN::SUPER_ADMIN_ID), 'integer', Rule::in(array_keys(Admin::getStatusLabels()))],
            'permission_group' => [Rule::requiredIf($admin->id !== ADMIN::SUPER_ADMIN_ID), 'array'],
            'permission_group.*' => [Rule::requiredIf($admin->id !== ADMIN::SUPER_ADMIN_ID), 'integer', 'exists:permission_groups,id'],
        ]);

        foreach ($validatedData as $attribute => $value) {

            if ($value == NULL || empty($value)) {

                continue;

            }

            switch ($attribute) {

                case 'username':

                    $admin->username = $value;

                    break;

                case 'password':

                    $admin->password = Hash::make($value);

                    break;

                case 'status':

                    $admin->status = $value;

                    break;

                case 'permission_group':

                    $admin->permission_groups()->detach();

                    $admin->permission_groups()->attach($value);

                    break;

            }

        }

        if ($admin->save()) {

            //避免修改密碼後 導致logout
            if (Auth::guard('admin')->user()->id === $admin->id) {

                Auth::guard('admin')->login($admin);

            }

            return redirect(route('admin.admin.show', ['admin' => $admin->id]))->with('status', '修改成功');

        } else {

            $errors = new MessageBag(['修改失敗']);

            return redirect()->back()->withErrors($errors);

        }

    }

    public function updateStatus(Request $request)
    {
        $validatedData = $request->validate([
            'id'        => ['required', 'array'],
            'id.*'      => ['required', 'integer', 'exists:admins,id'],
            'status'    => ['required', 'integer', Rule::in(array_keys(Admin::getStatusLabels()))]
        ],[
            'id.*' => '請勾選要修改的管理員'
        ]);

        Admin::whereIn('id', $validatedData['id'])
            ->update(['status' => $validatedData['status']]);

        return redirect()->back()->with('status', '修改狀態成功');
    }

    /**
     * Remove the specified resource from storage.
     *
     */
    public function destroy(Request $request)
    {
        $validatedData = $request->validate([
            'id'        => ['required', 'array'],
            'id.*'      => ['required', 'integer', 'exists:admins,id', function ($attribute, $value, $fail) {

                if (intval($value) === Admin::SUPER_ADMIN_ID) {

                    $fail('所選擇的管理員不允許被刪除');

                }

            }],
        ]);

        Admin::destroy($validatedData['id']);

        return redirect()->back()->with('status', '刪除管理員成功');
    }

    protected function search(Request $request)
    {
        $orderByList = ['username', 'status'];

        $validatedData = $request->validate([
            'username'          => ['nullable', 'string'],
            'status'            => ['nullable', 'integer', Rule::in(array_keys(Admin::getStatusLabels()))],
            'permission_group'  => ['nullable', 'integer', 'exists:permission_groups,id'],
            'order_by'          => ['nullable', 'string', Rule::in($orderByList)],
        ]);

        $adminQuery = Admin::query();

        foreach ($validatedData as $attribute => $value) {

            if ($value === NULL) {

                continue;

            }

            switch ($attribute) {

                case 'username':

                    $adminQuery->where([
                        ['username', 'like', sprintf('%%%s%%', $value)]
                    ]);

                    break;

                case 'status':

                    $adminQuery->where([
                        ['status', '=', $value]
                    ]);

                    break;

                case 'permission_group':

                    $adminQuery->whereHas('permission_groups', function (Builder $query) use ($value) {

                        $query->where([
                            ['permission_groups.id', '=', $value]
                        ]);

                    });

                    break;

                case 'order_by':

                    $adminQuery->orderBy($value, $request->get('is_asc') ? 'asc' : 'desc');

                    break;

            }

        }

        return $adminQuery->get();
    }
}
