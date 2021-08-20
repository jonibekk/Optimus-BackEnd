<?php

namespace App\AppService;

use App\Dto\GoalDto;
use App\Enums\Goals\GoalMemberType;
use App\Models\Goal;
use App\Models\GoalMembers;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class GoalsService
{
    // Get all goals
    public function getAll(int $teamId)
    {
        return Goal::with([
            'user:id,avatar_url,first_name,last_name,email,bio,timezone',
            'member:id,avatar_url,first_name,last_name',
            'kr'
        ])->where('team_id', $teamId)->get();
    }

    // Get all goals
    public function getGoalWithKrAndActions(int $goalId)
    {
        return Goal::with([
            'user:id,avatar_url,first_name,last_name,email,bio,timezone',
            'member:id,avatar_url,first_name,last_name',
            'kr',
            'kr.user:id,avatar_url,first_name,last_name',
            'action',
            'action.keyResult:id,name',
            'action.attachedFile',
            'action.user:id,avatar_url,first_name,last_name'
        ])->where('id', $goalId)->get();
    }

    // Get user's goals
    public function getAllGoalsBelongToUser(int $userId, int $teamId)
    {
        return Goal::where('team_id', $teamId)->where('user_id', $userId)->get() ?? null;
    }

    // Get user's goals including subscribed
    public function getGoalsIncludeSubscribed(int $userId, int $teamId)
    {
        $goals = User::find($userId)->goal()->where('goal_members.team_id', $teamId)->get() ?? null;

        return $goals;
    }

    // Check goal exists in a team or not.
    public function isGoalInTeam(int $goalId, int $teamId)
    {
        $goal = Goal::where('id', $goalId)->where('team_id', $teamId)->first() ?? null;

        return $goal;
    }

    // Check goal exists in a team or not.
    public function isUserGoalMember(int $userId, int $goalId)
    {
        $member = GoalMembers::where('id', $goalId)->where('user_id', $userId)->first() ?? null;

        return $member;
    }

    // Update Goal progress.
    public function updateGoalProgress(int $goalId, float $newProgress): bool
    {
        $goal = Goal::where('id', $goalId)->first();
        $goal['progress'] = $newProgress;

        return $goal->save();
    }

    // Create a goal
    public function createGoal(GoalDto $goalData, ?array $members)
    {
        DB::beginTransaction();
        try {
            $newGoal = $this->storeGoal($goalData);
            $this->subscribeToGoal($newGoal['id'], $goalData->userId, $goalData->teamId, GoalMemberType::GOAL_OWNER);

            if ($members && sizeof($members) > 0) {
                foreach ($members as $key => $value) {
                    # Add goal members...
                    # $this->subscribeToGoal($newGoal['id'], $value, $goalData->teamId, GoalMemberType::GOAL_MEMBER);
                }
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            throw new Exception($ex);
        }

        return $newGoal;
    }

    // Store new goal
    public function storeGoal(GoalDto $goalData)
    {
        $newGoal = new Goal([
            'user_id' => $goalData->userId,
            'team_id' => $goalData->teamId,
            'name' => $goalData->name,
            'color' => $goalData->color,
            'description' => $goalData->description ?? null,
            'due_date' => $goalData->dueDate ? Carbon::createFromTimestamp($goalData->dueDate) : null,
        ]);

        $newGoal->save();

        return $newGoal;
    }

    // Create new goal
    public function updateGoal(int $goalId, GoalDto $goalData)
    {
        $goal = Goal::findOrFail($goalId)->first();
        if(empty($goal)) { return false; }

        $goal['name'] = $goalData->name;
        $goal['description'] = $goalData->description;
        $goal['color'] = $goalData->color;

        return $goal->save();
    }

    // Create new goal
    public function subscribeToGoal(int $goalId, int $userId, int $teamId, int $memberType)
    {
        if (!$userId && !$teamId) { return false; }

        $goalMember = new GoalMembers([
            'user_id' => $userId,
            'team_id' => $teamId,
            'goal_id' => $goalId,
            'member_type' => $memberType,
        ]);

        return $goalMember->save();
    }

    // Delete a goal
    public function softDeleteGoal(int $goalId)
    {
        $goal = Goal::findOrFail($goalId)->first();
        if(empty($goal)) { return false; }

        $goal['del_flg'] = true;
        $goal['deleted_at'] = now();

        return $goal->save();
    }

    // Delete a goal
    public function hardDeleteGoal(int $goalId)
    {
        return Goal::findOrFail($goalId)->delete();
    }

}