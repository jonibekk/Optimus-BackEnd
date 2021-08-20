<?php

namespace App\Http\Controllers;

use App\AppService\UsersService;
use App\Dto\ResponseDto;
use App\Dto\UserDto;
use App\Models\Team;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @param  int  $teamId
     * @return \Illuminate\Http\Response
     */
    public function getAllUsersInTeam($teamId)
    {
        $response = new ResponseDto();
        try {
            $users = Team::find($teamId)->user;
            $response->success = true;
            $response->data = $users;
        } catch (Exception $e) {
            $response->success = false;
            $response->message = $e->getMessage();
            return response($response->toArray(), 500);
        }

        return response($response->toArray(), 200);
    }

    
    /**
     * Get me data
     *
     * @return \Illuminate\Http\Response
     */
    public function meData()
    {
        $user = auth()->user();
        $response = new ResponseDto();
        $response->success = true;
        $response->data = $user->only(
            [
                'id',
                'first_name',
                'last_name',
                'avatar_url',
                'bio',
                'current_team_id',
                'email',
                'email_verified',
                'timezone'
            ]
        );

        return response($response->toArray(), 200);
    }


    /**
     * Display the specified resource.
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getUser($id)
    {
        $userService = new UsersService();
        $response = new ResponseDto();

        $user = $userService->getUserById($id);
        if (empty($user)) {
            $response->success = false;
            $response->message = "User does not exist!";
            return response($response->toArray(), 400);
        }

        $response->success = true;
        $response->data = $user;
        return response($response->toArray(), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateUser(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|min:1|max:64',
            'last_name' => 'required|string|min:1|max:64',
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

        $userService = new UsersService();
        $response = new ResponseDto();
        $userData = new UserDto();

        $userData->firstName = $request['first_name'];
        $userData->lastName = $request['last_name'];
        $userData->bio = isset($request['bio']) ? $request['bio'] : '';
        $userData->avatar = $hasAvatar ? $request['avatar'] : null;

        try {
            $success = $userService->updateUserData($id, $userData, $hasAvatar);
            $response->success = $success;
            
            if (!$success) {
                $response->message = "Something went wrong!";
                return response($response->toArray(), 500);
            }
            $response->message = "User updated successfully!";
        } catch (Exception $e) {
            $response->success = false;
            $response->message = "Something went wrong!";
            return response($response->toArray(), 500);
        }

        return response($response->toArray(), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyUser($id)
    {
        $userService = new UsersService();
        $response = new ResponseDto();

        $success = $userService->softDeleteUser($id);
        if (!$success) {
            $response->success = false;
            $response->message = 'Something went wrong!';
            return response($response->toArray(), 500);
        }

        $response->success = $success;
        $response->message = "User deleted successfully!";

        return response($response->toArray(), 200);
    }

    /**
     * Change teams.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function changeCurrentTeam(Request $request)
    {
        $userService = new UsersService();
        $response = new ResponseDto();

        $success = $userService->updateUserCurrentTeam(auth()->id(), $request->user['current_team_id']);
        if (!$success) {
            $response->success = false;
            $response->message = 'Something went wrong!';
            return response($response->toArray(), 500);
        }

        $response->success = $success;
        $response->message = "Current team changed!";

        return response($response->toArray(), 200);
    }

    /**
     * Get my teams.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getMyTeams(Request $request)
    {
        $userService = new UsersService();
        $response = new ResponseDto();

        $teams = $userService->getTeamsByUserId(auth()->id());
        $response->success = true;        
        $response->data = $teams;        

        return response($response->toArray(), 200);
    }

    /**
     * Logout.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $response = new ResponseDto();
        // $token = $request->bearerToken();
        // $cookie = Cookie::forget('jwt_token');
        auth()->invalidate(true);
        $response->success = true;
        $response->message = 'Logged out succesfully!';

        return response($response->toArray(), 200);
    }
}
