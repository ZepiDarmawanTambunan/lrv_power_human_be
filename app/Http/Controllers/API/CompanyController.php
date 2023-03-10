<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function fetch(Request $request)
    {
        try {
            $id = $request->input('id');
            $name = $request->input('name');
            $limit = $request->input('limit', 10);

            $companiesQuery = Company::with(['users'])->whereHas('users', function ($query) {
                $query->where('user_id', Auth::id());
            });

            // powerhuman.com/api/company?id=42
            if ($id) {
                $company = $companiesQuery->find($id);
                if ($company) {
                    return ResponseFormatter::success($company, 'Company found');
                }
                return ResponseFormatter::error('Company not found', 404);
            }

            // powerhuman.com/api/company
            $companies = $companiesQuery;

            // powerhuman.com/api/company?name=jayasentosa
            if ($name) {
                $companies->where('name', 'like', '%' . $name . '%');
            }

            return ResponseFormatter::success(
                $companies->paginate($limit),
                "Companies found"
            );
        } catch (\Throwable $err) {
            return ResponseFormatter::error($err->getMessage(), 500);
        }
    }

    public function create(CreateCompanyRequest $request)
    {
        try {
            $requestData = $request->validated();
            if ($request->hasFile('logo')) {
                $requestData['logo'] = $request->file('logo')->store('public/logos');
            }

            $company = Company::create($requestData);

            if (!$company) {
                throw new Exception('Company not created');
            }

            $user = User::findOrFail(Auth::id());
            $user->companies()->attach($company->id); // [company_id, user_id]
            $company->load('users');

            return ResponseFormatter::success($company, 'Company Created');
        } catch (\Throwable $err) {
            return ResponseFormatter::error($err->getMessage(), 500);
        }
    }

    public function update(UpdateCompanyRequest $request, $id)
    {
        try {
            $company = Company::findOrFail($id);
            $requestData = $request->validated();
            if ($request->hasFile('logo')) {
                $requestData['logo'] = $request->file('logo')->store('public/logos');
            } else {
                $requestData['logo'] = $company->logo;
            }
            $company->update($requestData);
            return ResponseFormatter::success($company, 'Company Updated');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // menangani model not found
            return ResponseFormatter::error('company not found', 500);
        } catch (\Throwable $err) {
            // menangani error lainnya seperti masalah server
            return ResponseFormatter::error($err->getMessage(), 500);
        }
    }
}
