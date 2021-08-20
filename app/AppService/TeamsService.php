<?php

namespace App\AppService;

use App\Dto\TeamDto;
use App\Dto\TeamMemberDto;
use App\Enums\FileUpload\FileUploadType;
use App\Enums\Teams\TeamMemberType;
use App\Models\Invitations;
use App\Models\Team;
use App\Models\TeamMembers;
use Exception;
use Illuminate\Support\Facades\DB;

class TeamsService
{
    // Get Team by ID
    public function getTeamById(int $id)
    {
        $team = Team::find($id);

        return $team;
    }

    // Get Team by Name
    public function getTeamByName(string $name)
    {
        $team = Team::where('name', $name)->first();

        return $team;
    }

    // Get Current Team
    public function getCurrentTeam()
    {
        $team = Team::find(auth()->user()->current_team_id);
        $teamMember = TeamMembers::select('id','user_id','member_type')
            ->where('team_id', auth()->user()->current_team_id)
            ->where('user_id', auth()->user()->id)->first();

        $team['me'] = $teamMember;

        return $team;
    }

    // Create Team & Team Member
    public function creatTeamWithMember(int $userId, TeamDto $teamData, array $invites)
    {
        $fileUploadService = new FileUploadService();
        $teamMember = new TeamMemberDto();
        $userService = new UsersService();
        $emailService = new EmailsService();

        DB::beginTransaction();
        try {
            if ($teamId = $this->storeTeam($teamData)) {
                $teamMember->userId = $userId;
                $teamMember->teamId = $teamId;
                $teamMember->memberType = TeamMemberType::OWNER;

                if ($teamData->uploadedImage) {
                    // Upload team avatar to bucket.
                    $imageFileData = $fileUploadService->uploadToS3(FileUploadType::IMAGE_TEAM_AVATAR, $teamId, $teamData->uploadedImage);

                    // Set Team Logo Url
                    $this->updateTeamLogo($imageFileData->fileUrl, $teamId);
                }
                if ($invites && sizeof($invites) > 0) {
                    $emailService->bulkInviteMembers($userId, $teamId, $invites);
                }

                $this->storeTeamMember($teamMember);
                $userService->updateUserCurrentTeam($userId, $teamId);
                DB::commit();

                $returnData = $this->getTeamById($teamId);
            }
        } catch (Exception $ex) {
            DB::rollBack();
            throw new Exception($ex);
        }

        return $returnData;
    }

    // Store new user
    public function storeTeam(TeamDto $teamData): int
    {
        $newTeam = new Team([
            'name' => $teamData->name,
            'timezone' => $teamData->timezone,
            'service_status' => $teamData->serviceStatus,
            'service_use_type' => $teamData->serviceUseType,
            'service_start_date' => now(),
        ]);

        $newTeam->save();

        return $newTeam->id;
    }
    
    // Store new user
    public function storeTeamMember(TeamMemberDto $teamMemberData) 
    {
        $newTeamMember = new TeamMembers([
            'user_id' => $teamMemberData->userId,
            'team_id' => $teamMemberData->teamId,
            'member_type' => $teamMemberData->memberType,
        ]);
        
        return $newTeamMember->save();
    }

    // Change Subscription plan
    public function changeSubsPlan(int $id, int $planType) 
    {
        $team = Team::find($id);
        if (empty($team)) {
            return false;
        }

        $team['service_use_type'] = $planType;

        return $team->save();
    }

    // Store new user
    public function updateTeam(TeamDto $teamData, int $teamId, $hasAvatar = false)
    {
        $fileUploadService = new FileUploadService();
        $team = Team::find($teamId);

        $team['name'] = $teamData->name;
        if ($hasAvatar) {
            // Upload team avatar to bucket.
            $imageFileData = $fileUploadService->uploadToS3(FileUploadType::IMAGE_TEAM_AVATAR, $teamId, $teamData->uploadedImage);
            $team['logo_url'] = $imageFileData->fileUrl;
        }
        
        return $team->save();
    }

    // Store new user
    public function updateTeamLogo(string $logoUrl, int $id)
    {
        $team = Team::find($id);
        if (empty($team)) {
            return false;
        }
        $team['logo_url'] = $logoUrl;
        
        return $team->save();
    }

    // Get users by team id
    public function getUsersByTeamId($teamId)
    {
        $users = TeamMembers::select('id', 'user_id', 'member_type')
            ->with('user:id,avatar_url,first_name,last_name,email')
            ->where('team_id', $teamId)
            ->get();
        return $users;
    }

    // Get users by team id
    public function getPendingUsersInTeam($teamId)
    {
        $invites = Invitations::select('team_id','email')->where('team_id', $teamId)->get();
        return $invites;
    }

    // User exists in the invited Team
    public function userExistsInTeam(int $userId, int $teamId)
    {
        $user = TeamMembers::where('user_id', $userId)->where('team_id', $teamId)->first();

        return !empty($user);
    }
    
    // SoftDelete user
    public function softDeleteUser(int $id): bool
    {
        $team = Team::find($id);
        if (empty($team)) {
            return false;
        }
        $team['del_flg'] = true;
        $team['deleted_at'] = now();

        return $team->save();
    }

}