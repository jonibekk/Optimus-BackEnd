<?php

namespace App\AppService;

use App\Models\ActionPosts;
use App\Models\TeamMembers;
use App\Models\User;
use App\Models\Goal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class MeService 
{
    public function loadFeedActions()
    {
        $user = auth()->user();

        try {
            $data = ActionPosts::
                with([
                    'user:id,first_name,last_name,avatar_url',
                    'goal:id,name,description,color,progress,completed',
                    'keyResult:id,name,unit,currency_type,current_value,target_value,progress',
                    'attachedFile:id,filename,file_ext,file_url,file_size,file_type',
                ])
                ->where('team_id', $user['current_team_id'])
                ->orderBy('created_at', 'desc')->get();
        } catch (Exception $ex) {
            throw new Exception($ex);
        }

        return $data;
    }

    public function getTeamsBelongToUser(int $userId) {

        $teamsService = new TeamsService();

        try {
            $teams = User::find($userId)->team;
            foreach ($teams as $key => $team) {
                $teamMember = TeamMembers::where('team_id', $team['id'])->where('user_id', $userId)->first();
                $teams[$key]['alias'] = $teamMember;
            }
            $data = [
                'my_teams' => $teams,
                'current_team' => $teamsService->getCurrentTeam() ?? null
            ];
        } catch (Exception $ex) {
            throw new Exception($ex);
        }
        
        return $data;
    }

    public function getHomeFeedWidgets() {

        $user = auth()->user();
        $startDate = Carbon::now()->subDays(360);
        $endDate = Carbon::now();

        try {
            $activityHeatMap = ActionPosts::select(
                [
                    DB::raw('DATE(created_at) AS date'),
                    DB::raw('COUNT(id) AS count'),
                ]
            )
                ->where('user_id', $user['id'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('date')
                ->orderBy('date', 'ASC')
                ->get()
                ->toArray();
            $actionsWidget = ActionPosts::select('id')
                ->where('user_id', $user['id'])->count();
            $goalsWidget = Goal::select('id')
                ->where('user_id', $user['id'])->count();

            $data = [
                'heatmap' => $activityHeatMap,
                'actions' => $actionsWidget,
                'goals'   => $goalsWidget
            ];
        } catch (Exception $ex) {
            throw new Exception($ex);
        }

        return $data;
    }
}