<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use Exception;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function fetch(Request $request)
    {
        try {
            $id = $request->input('id');
            $name = $request->input('name');
            $email = $request->input('email');
            $role_id = $request->input('role_id');
            $team_id = $request->input('team_id');
            $company_id = $request->input('company_id');
            $limit = $request->input('limit', 10);
            // $with_teams = $request->input('with_teams', false);
            // $with_roles = $request->input('with_roles', false);

            $employeesQuery = Employee::with(['team', 'role']);
            // powerhuman.com/api/employee?id=1
            if ($id) {
                $employee = $employeesQuery->findOrFail($id);
                if ($employee) {
                    return ResponseFormatter::success($employee, 'Employee found');
                }
                return ResponseFormatter::error('Employee not found', 404);
            }

            // powerhuman.com/api/employee
            $employees = $employeesQuery;

            // powerhuman.com/api/employee?name=employee1
            if ($name) {
                $employees->where('name', 'like', '%' . $name . '%');
            }

            if ($email) {
                $employees->where('email', $email);
            }

            if ($team_id) {
                $employees->where('team_id', $team_id);
            }

            if ($role_id) {
                $employees->where('role_id', $role_id);
            }

            if ($company_id) {
                $employees->whereHas('team', function ($query) use ($company_id){
                    $query->where('company_id', $company_id);
                });
            }

            // if ($with_roles) {
            //     $employees->with('role');
            // }

            // if ($with_teams) {
            //     $employees->with('team');
            // }

            return ResponseFormatter::success(
                $employees->paginate($limit),
                "Employees found"
            );
        } catch (\Throwable $err) {
            return ResponseFormatter::error($err->getMessage(), 500);
        }
    }

    public function create(CreateEmployeeRequest $request)
    {
        try {
            $requestData = $request->validated();
            if ($request->hasFile('photo')) {
                // cara ini yg benar
                $file = $request->file('photo');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('public/photos', $fileName);
                $requestData['photo'] = $fileName;
            }
            $employee = Employee::create($requestData);
            if (!$employee) {
                throw new Exception('Employee not created');
            }

            return ResponseFormatter::success($employee, 'Employee Created');
        } catch (\Throwable $err) {
            return ResponseFormatter::error($err->getMessage(), 500);
        }
    }

    public function update(UpdateEmployeeRequest $request, $id)
    {
        try {
            $employee = Employee::findOrFail($id);
            $requestData = $request->validated();
            if ($request->hasFile('photo')) {
                $requestData['photo'] = $request->file('photo')->store('public/photos');
            } else {
                $requestData['photo'] = $employee->photo;
            }
            $employee->update($requestData);
            return ResponseFormatter::success($employee, 'Employee Updated');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // menangani model not found
            return ResponseFormatter::error('Employee not found', 500);
        } catch (\Throwable $err) {
            // menangani error lainnya seperti masalah server
            return ResponseFormatter::error($err->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $employee = Employee::find($id);
            $employee->delete();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // menangani model not found
            return ResponseFormatter::error('Employee not found', 500);
        } catch (\Throwable $err) {
            // menangani error lainnya seperti masalah server
            return ResponseFormatter::error($err->getMessage(), 500);
        }
    }
}
