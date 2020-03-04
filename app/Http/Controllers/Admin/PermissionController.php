<?php

namespace App\Http\Controllers\Admin;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Logics\PermissionLogic;;
use App\Http\Controllers\Common\CustomController;

class PermissionController extends CustomController
{
    CONST ALL_PERMISSION_LIST = 'ALL_PERMISSION_LIST';

    public function index(Request $request)
    {
        $permissionLogic = resolve(PermissionLogic::class);

        $allPermissionList = $permissionLogic->getAllPermissions($request);

        $allPermissionList = $this->paginate($allPermissionList, 10)
                                ->withPath(route('admin.permission.index'))
                                ->appends(Request::capture()->except('page'));

        //轉成非關聯陣列
        $data = [];

        foreach ($allPermissionList as $permission) {

           $data[] = $permission->toArray();

        }

        return view('admin.permission.index', [
            'permissionList' => $allPermissionList,
            'data' => json_encode($data),
            'controllerList' => collect($permissionLogic->getControllerList()),
            'actionList' => collect($permissionLogic->getActionList())
        ]);
    }

    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'id'    => ['required', 'array'],
            'id.*'  => ['required', 'integer', 'exists:permissions,id'],
            'status' => ['required', 'integer', Rule::in(array_keys(Permission::getStatusLabels()))]
        ], [
            'id.required' => '請勾選要開啓或關閉的權限'
        ]);

        Permission::whereIn('id', $validatedData['id'])
            ->update(['status' => $validatedData['status']]);

        return redirect(route('admin.permission.index'))->with('status', '權限更新成功');
    }


}
