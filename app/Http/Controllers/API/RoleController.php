<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function fetch(Request $request)
    {
        try {
            $id = $request->input('id');
            $name = $request->input('name');
            $limit = $request->input('limit', 10);
            $with_responsibilities = $request->input('with_responsibilities', false);

            $rolesQuery = Role::query();
            // powerhuman.com/api/role?id=1
            if ($id) {
                $role = $rolesQuery->with('responsibilities')->findOrFail($id);
                if ($role) {
                    return ResponseFormatter::success($role, 'Role found');
                }
                return ResponseFormatter::error('Role not found', 404);
            }

            // powerhuman.com/api/role
            $roles = $rolesQuery->where('company_id', $request->company_id);

            // powerhuman.com/api/role?name=role1
            if ($name) {
                $roles->where('name', 'like', '%' . $name . '%');
            }

            if ($with_responsibilities) {
                $roles->with('responsibilities');
            }

            return ResponseFormatter::success(
                $roles->paginate($limit),
                "Roles found"
            );
        } catch (\Throwable $err) {
            return ResponseFormatter::error($err->getMessage(), 500);
        }
    }

    public function create(CreateRoleRequest $request)
    {
        try {
            $requestData = $request->validated();
            $role = Role::create($requestData);
            if (!$role) {
                throw new Exception('Role not created');
            }
            return ResponseFormatter::success($role, 'Role Created');
        } catch (\Throwable $err) {
            return ResponseFormatter::error($err->getMessage(), 500);
        }
    }

    public function update(UpdateRoleRequest $request, $id)
    {
        try {
            $role = Role::findOrFail($id);
            $requestData = $request->validated();
            $role->update($requestData);
            return ResponseFormatter::success($role, 'Role Updated');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // menangani model not found
            return ResponseFormatter::error('Role not found', 500);
        } catch (\Throwable $err) {
            // menangani error lainnya seperti masalah server
            return ResponseFormatter::error($err->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $role = Role::find($id);
            $role->delete();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // menangani model not found
            return ResponseFormatter::error('Role not found', 500);
        } catch (\Throwable $err) {
            // menangani error lainnya seperti masalah server
            return ResponseFormatter::error($err->getMessage(), 500);
        }
    }
}
