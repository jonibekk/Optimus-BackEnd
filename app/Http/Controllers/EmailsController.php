<?php

namespace App\Http\Controllers;

use App\AppService\EmailsService;
use App\AppService\TeamsService;
use App\AppService\UsersService;
use App\Dto\ResponseDto;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmailsController extends Controller
{
    /**
     * Invite Users by email list
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function inviteNewMembers(Request $request)
    {
        $emailService = new EmailsService();
        $userService = new UsersService();
        $teamService = new TeamsService();
        $response = new ResponseDto();

        $validator = Validator::make($request->all(), [
            'from_user_id' => 'required|numeric|min:1',
            'team_id' => 'required|numeric|min:1',
            'invites' => 'required|array',
            'invites.*' => 'required|email',
        ]);
        if ($validator->fails()) {
            $response->success = false;
            $response->data = $validator->getMessageBag()->toArray();
            return response($response->toArray(), 400);
        }

        $emails = $request['invites'];
        $fromUserId = $request['from_user_id'];
        $fromTeamId = $request['team_id'];

        $validEmails = [];
        foreach($emails as $email) {
            $user = $userService->getUserByEmail($email);
            if (!empty($user) && $teamService->userExistsInTeam($user['id'], $fromTeamId)) {
                continue;
            } else {
                array_push($validEmails, $email);
            }
        }

        try {
            $emailService->bulkInviteMembers($fromUserId, $fromTeamId, $validEmails);
            $response->success = true;
            $response->message = 'Invitations sent successfully!';
        } catch (Exception $ex) {
            $response->success = false;
            $response->message = $ex->getMessage();
            return response($response->toArray(), 400);
        }

        return response($response->toArray(), 200);
    }

}
