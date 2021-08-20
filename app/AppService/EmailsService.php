<?php

namespace App\AppService;

use App\Models\Invitations;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;

class EmailsService
{
    // Get all emails
    public function getAll()
    {
        return Invitations::all();
    }

    // Get Invitation Details
    public function getInvitationDetails(string $token)
    {
        $invitation = $this->getInvitationByToken($token);

        if ($invitation === null) {
            return null;
        }

        // get team & inviter details
        $teamDetails = Team::where('id', $invitation['team_id'])->select('name', 'logo_url')->first();
        $inviterDetails = User::where('id', $invitation['from_user_id'])->select('first_name', 'last_name', 'avatar_url')->first();
        $userExists = User::where('email', $invitation['email'])->first() ?? null;

        $data = [
            'team' => $teamDetails,
            'user' => $inviterDetails,
            'invitation' => $invitation,
            'userExists' => empty($userExists) ? false : true
        ];

        return $data;
    }

    // Check invitations expiration
    public function tokenIsValid(string $emailToken): bool
    {
        $invited = Invitations::where('email_token', $emailToken)->first();
        if(empty($invited)) {
            return false;
        }

        if (Carbon::parse($invited['expires_at'])->gt(Carbon::now())) {
            return true;
        }

        return false;
    }

    // Check invitations existance
    public function invitationExists(string $email): bool
    {
        $invited = Invitations::where('email', $email)->get();
        if(empty($invited)) {
            return false;
        }

        return sizeof($invited) > 0;
    }

    // Bulk invite member
    public function bulkInviteMembers(int $fromUserId, int $teamId, array $emails) {
        
        foreach ($emails as $key => $value) {
            $this->updateOrCreateMemberInvitation($fromUserId, $teamId, $value);
        }

        return true;
    }
    
    // Update Token Expiration date
    public function updateOrCreateMemberInvitation(int $fromUserId, int $teamId, string $email)
    {
        
        $invited = Invitations::where('email', $email)->first();
        if (empty($invited)) 
        {
            $hashEmail = hash('sha256', $email);
            $invited = new Invitations([
                'team_id' => $teamId,
                'from_user_id' => $fromUserId,
                'email' => $email,
                'email_token' => $hashEmail,
                'expires_at' => Carbon::now()->addHours(24 * 7)
            ]);
            return $invited->save();
        } 
        else 
        {
            $invited['expires_at'] = Carbon::now()->addHours(24 * 7);
            return $invited->save();
        }

        return $invited;
    }

    // Get Email address by token
    public function getInvitationByToken(string $token)
    {
        $invited = Invitations::where('email_token', $token)->first() ?? [];
        return empty($invited) ? null : $invited;
    }

    // Delete Invitation by email
    public function deleteInvitationByEmail(string $email): bool
    {
        return Invitations::where('email', $email)->delete();
    }

    // Delete Invitation by email
    public function deleteInvitationById(int $id): bool
    {
        return Invitations::where('id', $id)->delete();
    }

}