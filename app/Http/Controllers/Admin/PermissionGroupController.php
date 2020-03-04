<?php

namespace App\Http\Controllers\Admin;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Logics\PermissionLogic;
use App\Models\PermissionGroup;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Common\CustomController;

class PermissionGroupController extends CustomController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $permissionGroupList = $this->paginate($this->search($request), 10)
                                ->withPath(route('admin.permissionGroup.index'))
                                ->appends(Request::capture()->except('page'));

        $data = $this->getUnassociatedArray($permissionGroupList->getCollection()->toArray());

        return view('admin.permission_group.index', [
            'permissionGroupList' => $permissionGroupList,
            'data' => json_encode($data)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $exceptControllerList = ['Permission', 'PermissionGroup'];

        $permissionLogic = resolve(PermissionLogic::class);

        $permissionList = [];

        foreach ($permissionLogic->getAllPermissions() as $permission) {

            if (in_array($permission->controller, $exceptControllerList)) {

                continue;

            }

            $permissionList[$permission->controller][$permission->id] = $permission->action;

        }

        return view('admin.permission_group.create', [
            'permissionList' => $permissionList
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
        //拿掉權限設置相關的id
        $exceptIdList = Permission::where('controller', 'Permission')
                            ->orWhere('controller', 'PermissionGroup')
                            ->get()
                            ->pluck('id');

        $validatedData = $request->validate([
            'name'              => ['required', 'string', function ($attribute, $value, $fail) {
                //確認群組名是否重複
                if (PermissionGroup::where('name', $value)->exists()) {

                    $fail('群組名重複, 請更換');

                }

            }],
            'status'            => ['required', 'integer', Rule::in(array_keys(Permission::getStatusLabels()))],
            'permissionId'      => ['required', 'array'],
            'permissionId.*'    => ['required', 'integer', 'exists:permissions,id', function ($attribute, $value, $fail) use ($exceptIdList) {

                if (in_array($value, $exceptIdList->toArray())) {

                    $fail('所選擇的權限不在清單中');

                }

            }]
        ]);

        $newPermissionGroup = new PermissionGroup([
            'name' => $validatedData['name'],
            'status' => $validatedData['status'],
        ]);

        if ($newPermissionGroup->save()) {

            $newPermissionGroup->permissions()->attach($validatedData['permissionId']);

        }

        return redirect(route('admin.permissionGroup.index'))->with('status', '創建權限群組成功');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\PermissonGroup  $permissionGroup
     * @return \Illuminate\Http\Response
     */
    public function show(PermissionGroup $permissionGroup)
    {
        $permissionList = [];

        if ($permissionGroup->has_all_permissions) {

            $permissionLogic = resolve(PermissionLogic::class);

            $permissionList = [];

            foreach ($permissionLogic->getAllPermissions() as $permission) {

                $permissionList[$permission->controller][$permission->id] = $permission->action;

            }

        } else {

            foreach ($permissionGroup->permissions as $permission) {

                $permissionList[$permission->controller][$permission->id] = $permission->action;

            }

        }

        return view('admin.permission_group.show', [
            'permissionGroup' => $permissionGroup,
            'permissionList'  => $permissionList
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\PermissonGroup  $permissionGroup
     * @return \Illuminate\Http\Response
     */
    public function edit(PermissionGroup $permissionGroup)
    {
        $exceptControllerList = ['Permission', 'PermissionGroup'];

        $permissionLogic = resolve(PermissionLogic::class);

        $permissionList = [];

        foreach ($permissionLogic->getAllPermissions() as $permission) {

            if (in_array($permission->controller, $exceptControllerList)) {

                continue;

            }

            $permissionList[$permission->controller][$permission->id] = $permission->action;

        }

        $selfPermissionList = [];

        if (!$permissionGroup->has_all_permissions) {

            foreach ($permissionGroup->permissions as $permission) {

                $selfPermissionList[$permission->controller][$permission->id] = $permission->action;

            }

        }

        return view('admin.permission_group.edit', [
            'permissionGroup'       => $permissionGroup,
            'permissionList'        => $permissionList,
            'selfPermissionList'    => $selfPermissionList,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PermissonGroup  $permissionGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PermissionGroup $permissionGroup)
    {
        $ruleList = [
            'name' => ['required', 'string', function ($attribute, $value, $fail) use($permissionGroup) {

                if ($permissionGroup->name !== $value && PermissionGroup::where('name', $value)->exists()) {

                    $fail('此群組名已被使用 請更換');

                }

            }],
        ];

        if (intval($permissionGroup->id) === PermissionGroup::SUPER_ADMIN_GROUP_ID) {

            $validatedData = $request->validate($ruleList);

        } else {

            //拿掉權限設置相關的id
            $exceptIdList = Permission::where('controller', 'Permission')
                                ->orWhere('controller', 'PermissionGroup')
                                ->get()
                                ->pluck('id');

            $ruleList = array_merge($ruleList, [
                'status'            => ['required', 'integer', Rule::in(array_keys(Permission::getStatusLabels()))],
                'permissionId'      => ['required', 'array'],
                'permissionId.*'    => ['required', 'integer', 'exists:permissions,id', function ($attribute, $value, $fail) use ($exceptIdList) {

                    if (in_array($value, $exceptIdList->toArray())) {

                        $fail('所選擇的權限不在清單中');

                    }

                }],
            ]);

            $validatedData = $request->validate($ruleList);

        }

        foreach ($validatedData as $attribute => $value) {

            switch ($attribute) {

                case 'name':

                    $permissionGroup->name = $value;

                    break;

                case 'status':

                    $permissionGroup->status = $value;

                    break;

                case 'permissionId':

                    $permissionGroup->permissions()->detach();

                    $permissionGroup->permissions()->attach($value);

                    break;

            }

        }

        if ($permissionGroup->save()) {

            return redirect()->back()->with('status', '修改成功');

        }

    }

    public function updateStatus(Request $request)
    {
        $validatedData = $request->validate([
            'id'        => ['required', 'array'],
            'id.*'      => ['required', 'integer', 'exists:permission_groups,id', function ($attribute, $value, $fail) {

                if ($value === PermissionGroup::SUPER_ADMIN_GROUP_ID) {

                    $fail('superadmin不能修改狀態');

                }

            }],
            'status'    => ['required', 'integer', Rule::in(array_keys(PermissionGroup::getStatusLabels()))]
        ], [
            'id.required' => '請選擇要修改的群組(superadmin除外)',
        ]);

        PermissionGroup::whereIn('id', $validatedData['id'])
            ->update(['status' => $validatedData['status']]);

        return redirect()->back()->with('status', '修改群組狀態成功');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $validatedData = $request->validate([
            'id'        => ['required', 'array'],
            'id.*'      => ['required', 'integer', 'exists:permission_groups,id', function ($attribute, $value, $fail) {

                if (intval($value) === PermissionGroup::SUPER_ADMIN_GROUP_ID) {

                    $fail('superadmin不能被刪除');

                }

            }]
        ]);

        PermissionGroup::destroy($validatedData['id']);

        return redirect()->back()->with('status', '刪除群組成功');
    }

    protected function search(Request $request)
    {
        $validatedData = $request->validate([
            'name'      => ['nullable', 'string'],
            'status'    => ['nullable', 'integer', Rule::in(array_keys(PermissionGroup::getStatusLabels()))]
        ]);

        $where = [];

        foreach ($validatedData as $attribute => $value) {

            if ($value === NULL) {

                continue;

            }

            switch ($attribute) {

                case 'name':

                    $where[] = ['name', 'like', sprintf('%%%s%%', $value)];

                    break;

                case 'status':

                    $where[] = ['status', '=', $value];

                    break;

            }
        }

        if (!empty($where)) {

            $permissionGroupList = PermissionGroup::where($where)->get();

        } else {

            $permissionGroupList = PermissionGroup::all();

        }

        return $permissionGroupList;
    }

}
