<?php

namespace App\AppService;

use App\Dto\TeamMemberDto;
use App\Dto\UserDto;
use App\Enums\FileUpload\FileUploadType;
use App\Enums\Teams\TeamMemberType;
use App\Models\Team;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersService
{
    // Get User by ID
    public function getUserById(int $id)
    {
        return User::find($id) ?? [];
    }

    // Get all Users in a team
    public function getUsersInTeam(int $teamId)
    {
        return Team::find($teamId)->user ?? [];
    }

    // Get User by Email
    public function getUserByEmail(string $email)
    {
        $user = User::where('email', $email)->first() || null;

        return $user;
    }

    // Store new user
    public function storeUser(UserDto $userData) 
    {
        $newUser = new User([
            'first_name' => $userData->firstName,
            'last_name' => $userData->lastName,
            'current_team_id' => $userData->defaultTeamId ?? 0,
            'email' => strtolower($userData->email),
            'password' => Hash::make($userData->password),
            'email_verified' => $userData->emailVarified ? $userData->emailVarified : false,
            'bio' => $userData->bio,
            'timezone' => $userData->timezone,
            'avatar_url' => $userData->avatarUrl
        ]);
        
        $newUser->save();

        return $newUser;
    }

    // Invite existing user to the team
    public function inviteUserToTeam(TeamMemberDto $teamMember, int $invitationId)
    {
        $teamService = new TeamsService();
        $emailService = new EmailsService();

        DB::beginTransaction();
        try {
            $teamService->storeTeamMember($teamMember);
            $emailService->deleteInvitationById($invitationId);
            $this->updateUserCurrentTeam($teamMember->userId, $teamMember->teamId);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            return false;
        }

        return true;
    }

    // Store new user
    public function storeInvitedUser(UserDto $userData) 
    {
        $teamService = new TeamsService();
        $emailService = new EmailsService();

        DB::beginTransaction();
        if($user = $this->storeUser($userData)) {
            $teamMember = new TeamMemberDto();
            $teamMember->userId = $user['id'];
            $teamMember->teamId = $user['current_team_id'];
            $teamMember->memberType = TeamMemberType::REGULAR;

            if($teamService->storeTeamMember($teamMember) && $emailService->deleteInvitationByEmail($user['email'])) {
                DB::commit();
            } else {
                DB::rollBack();
                return false;
            }
        } else {
            DB::rollBack();
            return false;
        }

        return true;
    }
    
    // Update user fullname
    public function updateUserData(int $id, UserDto $userData, $hasAvatar) 
    {
        $fileUploadService = new FileUploadService();

        $user = User::find($id);
        if (empty($user)) {
            return false;
        }

        if ($hasAvatar) {
            // Upload user avatar to bucket.
            $imageFileData = $fileUploadService->uploadToS3(FileUploadType::IMAGE_USER_AVATAR, $id, $userData->avatar);
            $user['avatar_url'] = $imageFileData->fileUrl;
        }

        $user['first_name'] = $userData->firstName;
        $user['last_name'] = $userData->lastName;
        $user['bio'] = $userData->bio;

        return $user->save();
    }
    
    // Update user fullname
    public function varifyEmail(int $id) 
    {
        $user = User::find($id);
        if (empty($user)) {
            return false;
        }

        $user['email_verified'] = true;
        return $user->save();
    }
    
    // Update user email
    public function updateUserEmail(int $id, string $email) 
    {
        //
    }
    
    // Update user password
    public function updateUserPassword(int $id, string $password) 
    {
        //
    }
    
    // Update user default team
    public function updateUserCurrentTeam(int $id, int $newCurrentTeamId): bool
    {
        $teamService = new TeamsService();

        $user = User::find($id);
        if (empty($user)) {
            return false;
        }

        $status = false;
        if ($teamService->userExistsInTeam($id, $newCurrentTeamId)) {
            $user['current_team_id'] = $newCurrentTeamId;
            $status = $user->save();
        }
        if (auth()->id() === $id) {
            auth()->setUser($user);
        }
        
        return $status;
    }

    // Varify email address
    public function varifyEmailAddress(int $id) 
    {
        //
    }

    // Get teams belongs to user
    public function getTeamsByUserId($id)
    {
        return User::find($id)->team ?? [];
    }
    
    // SoftDelete user
    public function softDeleteUser(int $id): bool
    {
        $user = User::find($id);
        if (empty($user)) {
            return false;
        }
        $user['del_flg'] = true;
        $user['deleted_at'] = now();

        return $user->save();
    }

}