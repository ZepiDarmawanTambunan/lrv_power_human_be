<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateResponsibilityRequest;
use App\Http\Requests\UpdateResponsibilityRequest;
use App\Models\Responsibility;
use Exception;
use Illuminate\Http\Request;

class ResponsibilityController extends Controller
{
    public function fetch(Request $request)
    {
        try {
            $id = $request->input('id');
            $name = $request->input('name');
            $limit = $request->input('limit', 10);

            $responsibilitiesQuery = Responsibility::query();
            // powerhuman.com/api/responsibility?id=1
            if ($id) {
                $responsibility = $responsibilitiesQuery->findOrFail($id);
                if ($responsibility) {
                    return ResponseFormatter::success($responsibility, 'Responsibility found');
                }
                return ResponseFormatter::error('Responsibility not found', 404);
            }

            // powerhuman.com/api/responsibility
            $responsibilities = $responsibilitiesQuery->where('role_id', $request->role_id);

            // powerhuman.com/api/responsibility?name=responsibility1
            if ($name) {
                $responsibilities->where('name', 'like', '%' . $name . '%');
            }

            return ResponseFormatter::success(
                $responsibilities->paginate($limit),
                "Responsibilitys found"
            );
        } catch (\Throwable $err) {
            return ResponseFormatter::error($err->getMessage(), 500);
        }
    }

    public function create(CreateResponsibilityRequest $request)
    {
        try {
            $requestData = $request->validated();
            $responsibility = Responsibility::create($requestData);
            if (!$responsibility) {
                throw new Exception('Responsibility not created');
            }
            return ResponseFormatter::success($responsibility, 'Responsibility Created');
        } catch (\Throwable $err) {
            return ResponseFormatter::error($err->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $responsibility = Responsibility::find($id);
            $responsibility->delete();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // menangani model not found
            return ResponseFormatter::error('Responsibility not found', 500);
        } catch (\Throwable $err) {
            // menangani error lainnya seperti masalah server
            return ResponseFormatter::error($err->getMessage(), 500);
        }
    }
}
