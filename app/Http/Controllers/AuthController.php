<?php

namespace App\Http\Controllers;

use App\AppService\EmailsService;
use App\AppService\TeamsService;
use App\AppService\UsersService;
use App\Dto\ResponseDto;
use App\Dto\TeamMemberDto;
use App\Dto\UserDto;
use App\Enums\Teams\TeamMemberType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Register a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->user, [
            'first_name' => 'required|min:1|max:64',
            'last_name' => 'required|min:1|max:64',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:1',
            'timezone' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response($validator->getMessageBag()->toArray(), 400);
        }

        $userService = new UsersService();
        $response = new ResponseDto();
        $userData = new UserDto();
        
        $userData->firstName = $request->user['first_name'];
        $userData->lastName = $request->user['last_name'];
        $userData->email = $request->user['email'];
        $userData->password = $request->user['password'];
        $userData->timezone = $request->user['timezone'];
        $userData->avatarUrl = $request->user['avatar_url'] ?? null;
        $userData->bio = $request->user['bio'] ?? '';

        try {
            $userService->storeUser($userData);
        } catch (Exception $ex) {
            $response->success = false;
            $response->data = ['message' => $ex->getMessage()];
            return response($response->toArray(), 500);
        }

        $response->success = true;
        $response->data = ['message' => "User created successfully!"];

        return response($response->toArray(), 200);
    }

    /**
     * Check invitation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
    public function checkValidHash(Request $request, $token)
    {
        $emailService = new EmailsService();
        $response = new ResponseDto();

        try {
            $invitationDetails = $emailService->getInvitationDetails($token);

            if($invitationDetails === null) {
                $response->success = false;
                $response->message = "Invitation expired or doesn't exist!";
                return response($response->toArray(), 400);
            }

            $response->data = $invitationDetails;
            $response->success = true;
        } catch (Exception $ex) {
            $response->success = false;
            $response->message = $ex->getMessage();
            return response($response->toArray(), 500);
        }

        return response($response->toArray(), 200);
    }

    /**
     * Register by invitation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
    public function registerInvitedUser(Request $request, $token)
    {
        $emailService = new EmailsService();
        $userService = new UsersService();
        $teamService = new TeamsService();
        $response = new ResponseDto();
        $userData = new UserDto();

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'user_exists' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            $response->success = false;
            $response->data = $validator->getMessageBag()->toArray();
            return response($response->toArray(), 400);
        }

        $invitation = $request['invitation'];
        
        // Validate token
        if (!$emailService->tokenIsValid($token)) {
            $response->success = false;
            $response->message = "Token is Invalid!";

            try {
                $emailService->deleteInvitationById($invitation['id']);
            } catch (Exception $e) {
                return response([$response->toArray()], 400);
            }
            
            return response([$response->toArray()], 400);
        }

        // Check whether user has an account or not: If so -> invite to the team without creating account
        if ($request['user_exists']) {
            $user = $userService->getUserByEmail($invitation['email']);

            if ($teamService->userExistsInTeam($user['id'], $invitation['team_id'])) {
                $response->success = false;
                $response->message = "User is already a member of this team.";
                return response($response->toArray(), 400);
            }

            $teamMember = new TeamMemberDto();
            $teamMember->userId = $user['id'];
            $teamMember->teamId = $invitation['team_id'];
            $teamMember->memberType = TeamMemberType::REGULAR;

            if (!$userService->inviteUserToTeam($teamMember, $invitation['id'])) {
                $response->success = false;
                $response->message = "Coudn't join the team!";
                return response($response->toArray(), 200);
            }

            $response->success = true;
            $response->message = "User joined to the team successfully!";
            return response($response->toArray(), 200);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|min:1|max:64',
            'last_name' => 'required|min:1|max:64',
            'password' => 'required|min:1',
            'timezone' => 'required|between:-12.0,12.0',
        ]);

        if ($validator->fails()) {
            $response->success = false;
            $response->data = $validator->getMessageBag()->toArray();
            return response($response->toArray(), 400);
        }
        
        $userData->email = $invitation['email'];
        $userData->defaultTeamId = $invitation['team_id'];
        $userData->firstName = $request['first_name'];
        $userData->lastName = $request['last_name'];
        $userData->password = $request['password'];
        $userData->bio = $request['bio'] ?? null;
        $userData->timezone = $request['timezone'];
        $userData->avatarUrl = $request['avatar_url'] ?? null;
        $userData->emailVarified = true;

        try {
            $success = $userService->storeInvitedUser($userData);
            if (!$success) {
                $response->success = false;
                $response->message = "Something went wrong!";
                return response($response->toArray(), 500);
            }
        } catch (Exception $ex) {
            $response->success = false;
            $response->message = $ex->getMessage();
            return response($response->toArray(), 500);
        }

        $response->success = $success;
        $response->message = "User created successfully!";

        return response($response->toArray(), 200);
    }

    /**
     * Login user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $response = new ResponseDto();

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return response($validator->getMessageBag()->toArray(), 400);
        }

        $creds = [
            'email' => $request['email'],
            'password' => $request['password']
        ];

        $token_validity = 60 * 24 * 7;

        $this->guard()->factory()->setTTL($token_validity);

        if (!$token = JwtAuth::attempt($creds)) {
            $response->success = true;
            $response->message = 'Incorrect email/password';
            return response($response->toArray(), 400);
        }

        $response->success = true;
        $response->message = 'Logged in successfully!';
        $response->data = ['token' => $token, 'token_validity' => $this->guard()->factory()->getTTL()];
        return response($response->toArray(), 200);
    }

    /**
     * Refresh token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function refresh(Request $request)
    {
        //
    }

    protected function guard() {
        return Auth::guard();
    }
}
