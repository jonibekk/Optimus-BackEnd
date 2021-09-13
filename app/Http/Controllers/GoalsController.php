<?php

namespace App\Http\Controllers;

use App\AppService\GoalsService;
use App\Dto\GoalDto;
use App\Dto\ResponseDto;
use App\Enums\Goals\GoalMemberType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GoalsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get All goal in a team $teamId
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $teamId
     * @return \Illuminate\Http\Response
     */
    public function allGoals(Request  $request, $teamId)
    {
        $goalService = new GoalsService();
        $response = new ResponseDto();

        try {
            $response->data = $goalService->getAll($teamId);
            $response->success = true;
        } catch (Exception $ex) {
            $response->success = false;
            $response->message = $ex->getMessage();
            return response($response->toArray(), 500);
        }

        return response($response->toArray(), 200);
    }

    /**
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $goalId
     * @return \Illuminate\Http\Response
     */
    public function getGoalDetails(Request  $request, $goalId)
    {
        $goalService = new GoalsService();
        $response = new ResponseDto();
        $user = auth()->user();

        if (!$goalService->isGoalInTeam(intval($goalId), $user['current_team_id'])) {
            $response->success = false;
            $response->message = 'Goal does not exist!';
            return response($response->toArray(), 400);
        }

        try {
            $response->data = $goalService->getGoalDetails(intval($goalId));
            $response->success = true;
        } catch (Exception $ex) {
            $response->success = false;
            $response->message = $ex->getMessage();
            return response($response->toArray(), 500);
        }

        return response($response->toArray(), 200);
    }

    /**
     * Get All goal in a team $teamId
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $teamId
     * @return \Illuminate\Http\Response
     */
    public function getMyGoals(Request  $request, $teamId)
    {
        $goalService = new GoalsService();
        $response = new ResponseDto();

        $user = auth()->user();

        try {
            $goals = $goalService->getAllGoalsBelongToUser($user['id'], $teamId);
            $response->success = true;
        } catch (Exception $ex) {
            $response->success = false;
            $response->message = $ex->getMessage();
        }
        
        $response->data = $goals ?? null;
        return response($response->toArray(), $response->success ? 200 : 500);
    }

    /**
     * Get All goal in a team $teamId
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $teamId
     * @return \Illuminate\Http\Response
     */
    public function getGoalsIncludeSubscribed(Request  $request, $teamId)
    {
        $goalService = new GoalsService();
        $response = new ResponseDto();

        $user = auth()->user();

        try {
            $goals = $goalService->getGoalsIncludeSubscribed($user['id'], $teamId);
            $response->success = true;
        } catch (Exception $ex) {
            $response->success = false;
            $response->message = $ex->getMessage();
        }
        
        $response->data = $goals ?? null;
        return response($response->toArray(), $response->success ? 200 : 500);
    }

    /**
     * Create a goal
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request  $request)
    {
        $newGoal = $request->all();
        $response = new ResponseDto();
        
        $validator = Validator::make($newGoal, [
            "name" => 'required|string|min:1|max:200',
		    "color" => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return response($validator->getMessageBag()->toArray(), 400);
        }

        if (isset($newGoal['description'])) {
            $validator = Validator::make($newGoal, [
                "description" => 'required|string|min:1|max:300',
            ]);

            if ($validator->fails()) {
                return response($validator->getMessageBag()->toArray(), 400);
            }
        }
        if (isset($newGoal['due_date'])) {
            $validator = Validator::make($newGoal, [
                "due_date" => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response($validator->getMessageBag()->toArray(), 400);
            }
        }
        if (isset($newGoal['members'])) {
            $validator = Validator::make($newGoal, [
                "members" => 'required|array',
            ]);

            if ($validator->fails()) {
                return response($validator->getMessageBag()->toArray(), 400);
            }
        }

        $goalService = new GoalsService();
        $user = auth()->user();
        
        $goalData = new GoalDto();
        $goalData->userId = $user['id'];
        $goalData->teamId = $user['current_team_id'];
        $goalData->name = $newGoal['name'];
        $goalData->color = $newGoal['color'];
        $goalData->dueDate = isset($newGoal['due_date']) ? $newGoal['due_date'] : null;
        $goalData->description = $newGoal['description'] ?? null;

        try {
            $response->data = $goalService->createGoal($goalData, $newGoal['members'] ?? null);
            $response->message = 'Goal created successfully!';
        } catch (Exception $ex) {
            $response->success = false;
            $response->message = $ex->getMessage();
            return response($response->toArray(), 500);
        }

        return response($response->toArray(), 200);
    }

    /**
     * Update a goal
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request  $request, $id)
    {
        $updatedGoal = $request->goal;
        $response = new ResponseDto();

        $validator = Validator::make($updatedGoal, [
            "name" => 'required|string|min:1',
            "color" => 'required|string',
            "description" => 'required|string|min:1|max:255',
        ]);

        if ($validator->fails()) {
            return response($validator->getMessageBag()->toArray(), 400);
        }

        $goalService = new GoalsService();

        $goalData = new GoalDto();
        $goalData->name = $updatedGoal['name'];
        $goalData->description = $updatedGoal['description'];
        $goalData->color = $updatedGoal['color'];

        try {
            $response->success = $goalService->updateGoal($id, $goalData);
            $response->message = 'Goal updated successfully!';
        } catch (Exception $ex) {
            $response->success = false;
            $response->message = $ex->getMessage();
            return response($response->toArray(), 500);
        }

        return response($response->toArray(), 200);
    }

    /**
     * Delete a goal
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(Request  $request, $id)
    {
        $response = new ResponseDto();
        $goalService = new GoalsService();

        try {
            $response->success = $goalService->softDeleteGoal($id);
            $response->message = 'Goal deleted successfully!';
        } catch (Exception $ex) {
            $response->success = false;
            $response->message = $ex->getMessage();
            return response($response->toArray(), 500);
        }

        return response($response->toArray(), 200);
    }

    /**
     * Subscribe to a goal
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function subscribe(Request  $request, $id)
    {
        $response = new ResponseDto();
        $goalService = new GoalsService();

        $userId = $request['userId'];
        $teamId = $request['teamId'];

        try {
            if ($goalService->isGoalInTeam($id, $teamId) && !$goalService->isUserGoalMember($userId, $id)) {
                $response->success = $goalService->subscribeToGoal($id, $userId, $teamId, GoalMemberType::GOAL_MEMBER);
                $response->message = 'Subscribed!';
            } else {
                $response->success = false;
                $response->message = 'Goal not found!';
                return response($response->toArray(), 500);
            }
        } catch (Exception $ex) {
            $response->success = false;
            $response->message = $ex->getMessage();
            return response($response->toArray(), 500);
        }

        return response($response->toArray(), 200);
    }

}
