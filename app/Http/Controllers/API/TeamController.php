<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Team;
use Exception;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function fetch(Request $request)
    {
        try {
            $id = $request->input('id');
            $name = $request->input('name');
            $limit = $request->input('limit', 10);

            $teamsQuery = Team::withCount('employees');
            // powerhuman.com/api/team?id=1
            if ($id) {
                $team = $teamsQuery->findOrFail($id);
                if ($team) {
                    return ResponseFormatter::success($team, 'Team found');
                }
                return ResponseFormatter::error('Team not found', 404);
            }

            // powerhuman.com/api/team
            $teams = $teamsQuery->where('company_id', $request->company_id);

            // powerhuman.com/api/team?name=team1
            if ($name) {
                $teams->where('name', 'like', '%' . $name . '%');
            }

            return ResponseFormatter::success(
                $teams->paginate($limit),
                "Teams found"
            );
        } catch (\Throwable $err) {
            return ResponseFormatter::error($err->getMessage(), 500);
        }
    }

    public function create(CreateTeamRequest $request)
    {
        try {
            $requestData = $request->validated();
            if ($request->hasFile('icon')) {
                $requestData['icon'] = $request->file('icon')->store('public/icons');
            }
            $team = Team::create($requestData);
            if (!$team) {
                throw new Exception('Team not created');
            }

            return ResponseFormatter::success($team, 'Team Created');
        } catch (\Throwable $err) {
            return ResponseFormatter::error($err->getMessage(), 500);
        }
    }

    public function update(UpdateTeamRequest $request, $id)
    {
        try {
            $team = Team::findOrFail($id);
            $requestData = $request->validated();
            if ($request->hasFile('icon')) {
                $requestData['icon'] = $request->file('icon')->store('public/icons');
            } else {
                $requestData['icon'] = $team->icon;
            }
            $team->update($requestData);
            return ResponseFormatter::success($team, 'Team Updated');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // menangani model not found
            return ResponseFormatter::error('Team not found', 500);
        } catch (\Throwable $err) {
            // menangani error lainnya seperti masalah server
            return ResponseFormatter::error($err->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $team = Team::find($id);
            $team->delete();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // menangani model not found
            return ResponseFormatter::error('Team not found', 500);
        } catch (\Throwable $err) {
            // menangani error lainnya seperti masalah server
            return ResponseFormatter::error($err->getMessage(), 500);
        }
    }
}
