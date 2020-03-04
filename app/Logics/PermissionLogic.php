<?php

namespace App\Logics;

use App\Models\Permission;
use App\Models\PermissionGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\User as Authenticatable;

class PermissionLogic {

    private $controllerList;
    private $actionList;

    public function getAllPermissions (Request $request = NULL)
    {
        //更新permissions table
        $this->insertNewPermission();

        if ($request) {

            //根據條件篩選permission
            $validatedData = $request->validate([
                'controller'    => ['nullable', 'string', Rule::in(array_values($this->controllerList))],
                'action'        => ['nullable', 'string', Rule::in(array_values($this->actionList))],
                'status'        => ['nullable', 'integer', Rule::in(array_keys(Permission::getStatusLabels()))]
            ]);

            $where = [];

            foreach ($validatedData as $attribute => $value) {

                if ($value === NULL) {

                    continue;

                }

                $where[] = [$attribute, '=', $value];

            }

            return Permission::where($where)->get();


        }

        return Permission::all();
    }

    public function checkPermission(Request $request, Authenticatable $admin = NULL)
    {
        if ($admin == NULL
            || $admin->id === PermissionGroup::SUPER_ADMIN_GROUP_ID
            || $admin->permission_groups()->where('has_all_permissions', TRUE)->exists()) {

            return TRUE;

        }

        preg_match('/\\\\Admin\\\\(\w+)Controller@(\w+)/', Route::currentRouteAction(), $matches);

        //排除logout
        if (Route::currentRouteName() === 'admin.login.logout') {

            return TRUE;

        }

        $result = $admin->available_permissions
                    ->where('controller', $matches[1])
                    ->where('action', $matches[2])
                    ->where('status', Permission::STATUS_ON);

        return !$result->isEmpty();

    }

    /**
     * 將新的permission 存入permissions table
     *
     */
    protected function insertNewPermission()
    {
        $newPermissionList = [];

        //取得最新的permissionList
        foreach ($this->mapControllerToAction()->toArray() as $controller => $actions) {

            foreach ($actions as $action) {

                $newPermissionList[] = [
                    'controller' => $controller,
                    'action' => $action,
                ];

            }

        }

        //與資料庫裏的permission做比較 重複的就unset掉
        $currentPermissionList = Permission::select(['controller', 'action'])
                                    ->get()
                                    ->toArray();


        foreach ($newPermissionList as $key => $item) {

            if (in_array($item, $currentPermissionList)) {

                unset($newPermissionList[$key]);

            }

        }

        if (!empty($newPermissionList)) {

            Permission::insert(
                $newPermissionList
            );

        }
    }

    public function getControllerList()
    {
        return $this->controllerList;
    }

    public function getActionList()
    {
        return $this->actionList;
    }

    protected function mapControllerToAction ()
    {
        $permissionList = [];

        foreach (Route::getRoutes() as $route) {

            if (isset($route->getAction()['controller'])
                && preg_match('/\\\\Admin\\\\(\w+)Controller@(\w+)/', $route->getAction()['controller'], $matches)) {

                $this->controllerList[$matches[1]] = $matches[1];

                $this->actionList[$matches[2]] = $matches[2];

                $permissionList[$matches[1]][] = $matches[2];

            }

        }

        return collect($permissionList);
    }

}
