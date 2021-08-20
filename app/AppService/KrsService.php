<?php

namespace App\AppService;

use App\Dto\KrDto;
use App\Models\Goal;
use App\Models\KeyResults;
use Exception;
use Illuminate\Support\Facades\DB;

class KrsService
{
    // Get all user's krs 
    public function getAll(int $userId)
    {
        return KeyResults::where('user_id', $userId)
            ->where('del_flg', false)
            ->get() ?? null;
    }

    // Get all user's krs 
    public function getKrByID(int $krId)
    {
        return KeyResults::where('id', $krId)->first() ?? null;
    }

    // Update Kr progress.
    public function updateKrProgress(int $krId, float $currentValue, float $newProgress): bool
    {
        $kr = KeyResults::where('id', $krId)->first();
        $kr['progress'] = $newProgress;
        $kr['current_value'] = $currentValue;

        return $kr->save();
    }

    // Get user's Krs belonging to the Goal
    public function getMyKrsBelongInGoal(int $userId, int $goalId)
    {
        return KeyResults::where('user_id', $userId)
            ->where('goal_id', $goalId)
            ->where('del_flg', false)
            ->get() ?? null;
    }

    // Get user's Krs belonging to the Goal
    public function getAllKrsInGoal(int $goalId)
    {
        return KeyResults::with('user:id,first_name,last_name,email,avatar_url')
            ->where('goal_id', $goalId)
            ->get() ?? null;
    }

    // Create a Kr
    public function createKr(KrDto $krData)
    {
        $goalService = new GoalsService();
        $user = auth()->user();

        DB::beginTransaction();
        try {
            $goal = $goalService->isGoalInTeam($krData->goalId, $user['current_team_id']);
            if ($goal !== null) {
                $newKr = new KeyResults([
                    'user_id' => $krData->userId,
                    'goal_id' => $krData->goalId,
                    'name' => $krData->name,
                    'unit' => $krData->krUnit,
                    'start_value' => $krData->startValue,
                    'target_value' => $krData->targetValue,
                    'currency_type' => $krData->krCurrencyType,
                ]);
                $newKr->save();
                DB::commit();
            } else {
                return false;
            }
        } catch (Exception $ex) {
            DB::rollBack();
            throw new Exception($ex);
        }

        return true;
    }

    // Update Kr
    public function updateKr(int $id, string $name, float $target)
    {
        if ($this->krExists($id)) {
            $kr = KeyResults::where('id', $id)->first();
            $kr['name'] = $name;
            $kr['target_value'] = $target;

            return $kr->save();
        } else {
            return false;
        }
    }

    // check Kr exists or not
    public function krExists(int $id): bool
    {
        $kr = KeyResults::find($id)->get();
        return !empty($kr);
    }

    // SoftDelete a Kr
    public function softDeleteKr(int $id)
    {
        $kr = KeyResults::where('id', $id)->first();
        if (empty($kr)) {
            return false;
        }

        $kr['del_flg'] = true;
        $kr['deleted_at'] = now();

        return $kr->save();
    }

    // HardDelete a Kr
    public function hardDeleteKr(int $id)
    {
        return KeyResults::where('id', $id)->delete();
    }

}