<?php

namespace App\Http\Controllers;

use App\AppService\TeamsService;
use App\Dto\ResponseDto;
use App\Dto\TeamDto;
use App\Enums\Teams\TeamServiceStatus;
use App\Enums\Teams\TeamServiceUseType;
use App\Models\Team;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TeamsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    
    // Get current team
    public function currentTeam()
    {
        return Team::find(auth()->user()->current_team_id);
    }

    // Create team
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:1|max:255|unique:teams',
            'timezone' => 'required|between:-12.0,12.0',
            'image' => 'required|mimes:jpeg,png,svg',
            'invites' => 'required|array',
        ]);

        
        if ($validator->fails()) {
            return response($validator->getMessageBag()->toArray(), 400);
        }
        
        $teamService = new TeamsService();
        $response = new ResponseDto();
        $teamData = new TeamDto();
        
        $teamData->name = $request['name'];
        $teamData->timezone = $request['timezone'];
        $teamData->uploadedImage = $request['image'];
        $teamData->serviceUseType = TeamServiceUseType::FREE_TRIAL;
        $teamData->serviceStatus = TeamServiceStatus::ACTIVE;
        
        try {
            $response->data = $teamService->creatTeamWithMember(auth()->id(), $teamData, $request['invites']) ?? null;
            $response->success = $response->data !== null;
            if (!$response->success) {
                $response->message = 'Something went wrong!';
                return response($response->toArray(), 500);
            }
        } catch (Exception $ex) {
            $response->success = false;
            $response->message = $ex->getMessage();
            return response($response->toArray(), 500);
        }

        $response->message = 'Team created successfully!';
        return response($response->toArray(), 200);
    }

    // Create team
    public function updateTeamInfo(Request $request, $teamId)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response($validator->getMessageBag()->toArray(), 400);
        }

        $hasAvatar = false;
        if (isset($request['avatar'])) {
            $hasAvatar = true;
            $validator = Validator::make($request->all(), [
                'avatar' => 'required|mimes:jpeg,png,svg'
            ]);
            if ($validator->fails()) {
                return response($validator->getMessageBag()->toArray(), 400);
            }
        }

        $teamService = new TeamsService();
        $response = new ResponseDto();
        $teamData = new TeamDto();

        $teamData->name = $request['name'];
        $teamData->uploadedImage = $hasAvatar ? $request['avatar'] : null;

        try {
            $teamService->updateTeam($teamData, $teamId, $hasAvatar);
            $response->success = true;
            $response->message = 'Team updated successfully!';
        } catch(Exception $e) {
            $response->success = false;
            $response->message = $e->getMessage();
            return response($response->toArray(), 500);
        }

        return response($response->toArray(), 200);
    }

    // Change team subscription plan
    public function changeSubsPlan(Request $request, $teamId)
    {
        $validator = Validator::make($request->team, [
            'service_use_type' => [
                'required',
                Rule::in(
                    [
                        TeamServiceUseType::FREE_TRIAL,
                        TeamServiceUseType::BASIC_PLAN,
                        TeamServiceUseType::PREMIUM_PLAN,
                        TeamServiceUseType::GOLD_PLAN
                    ]
                )
            ]
        ]);

        if ($validator->fails()) {
            return response($validator->getMessageBag()->toArray(), 400);
        }

        $teamService = new TeamsService();
        $response = new ResponseDto();

        $success = $teamService->changeSubsPlan($teamId, $request->team['service_use_type']);
        $response->success = $success;
        if (!$success) {
            $response->message = 'Something went wrong!';
            return response($response->toArray(), 500);
        }

        $response->message = 'Team updated successfully!';
        return response($response->toArray(), 200);
    }

    /**
     * Get my teams.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getUsersByTeam($id)
    {
        $teamService = new TeamsService();
        $response = new ResponseDto();

        try {
            $users = $teamService->getUsersByTeamId($id);
            $invited = $teamService->getPendingUsersInTeam($id) ?? [];
            $response->success = true;
            $response->data = [
                'members' => $users,
                'pending' => $invited
            ];
        } catch (Exception $e) {
            $response->success = false;
            $response->message = $e->getMessage();
            return response($response->toArray(), 500);
        }

        return response($response->toArray(), 200);
    }
}
