<?php

use App\Http\Controllers\ActionsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\EmailsController;
use App\Http\Controllers\GoalsController;
use App\Http\Controllers\KrsController;
use App\Http\Controllers\MeController;
use App\Http\Controllers\TeamsController;
use App\Http\Controllers\UsersController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    
    Route::get('/invitation/{token}', [AuthController::class, 'checkValidHash']);
    Route::post('/invitation/{token}', [AuthController::class, 'registerInvitedUser']);
});

Route::group(['middleware' => 'api'], function () {

    // UsersController
    Route::prefix('user')->group(function () {
        Route::get('/team/{teamId}/all-users', [UsersController::class, 'getAllUsersInTeam']);
        Route::get('/me', [UsersController::class, 'meData']);
        Route::get('/me/teams', [UsersController::class, 'getMyTeams']);
        Route::get('/{id}', [UsersController::class, 'getUser']);

        Route::post('/{id}/update', [UsersController::class, 'updateUser']);
        Route::post('/me/change-team', [UsersController::class, 'changeCurrentTeam']);

        Route::delete('/{id}', [UsersController::class, 'destroyUser']);

        Route::post('/logout', [UsersController::class, 'logout']);
    });

    // TeamsController
    Route::prefix('team')->group(function () {
        Route::get('/current-team', [TeamsController::class, 'currentTeam']);
        Route::get('/{id}/users', [TeamsController::class, 'getUsersByTeam']);

        Route::post('/{id}/update', [TeamsController::class, 'updateTeamInfo']);
        Route::post('/{id}/change-plan', [TeamsController::class, 'changeSubsPlan']);
        
        Route::post('/create', [TeamsController::class, 'create']);
    });

    // GoalsController
    Route::prefix('goal')->group(function () {
        Route::get('/team/{teamId}/goals', [GoalsController::class, 'allGoals']);
        Route::get('/team/{teamId}/my-goals', [GoalsController::class, 'getMyGoals']);
        Route::get('/team/{teamId}/my-goals-inc-subs', [GoalsController::class, 'getGoalsIncludeSubscribed']);
        Route::get('/{id}/with-kr-actions', [GoalsController::class, 'getGoalWithKrAndActions']);

        Route::post('/create', [GoalsController::class, 'create']);
        Route::post('/{id}/subscribe', [GoalsController::class, 'subscribe']);

        Route::post('/{id}/update', [GoalsController::class, 'update']);

        Route::delete('/{id}/delete', [GoalsController::class, 'delete']);
    });

    // KrsController
    Route::prefix('kr')->group(function () {
        Route::get('/user/{userId}/all', [KrsController::class, 'allKrs']);
        Route::get('/goal/{goalId}/all', [KrsController::class, 'getAllKrsInGoal']);
        Route::get('/user/{userId}/goal/{goalId}/all', [KrsController::class, 'getMyKrsBelongInGoal']);
 
        Route::post('/goal/{goalId}/create', [KrsController::class, 'create']);

        Route::post('/{id}/update', [KrsController::class, 'update']);

        Route::delete('/{id}/delete', [KrsController::class, 'delete']);
    });

    // ActionsController
    Route::prefix('action')->group(function () {
        Route::get('/team/{teamId}/user/{userId}/all', [ActionsController::class, 'getUserActions']);
        Route::get('/kr/{krId}/all', [ActionsController::class, 'getKrActions']);
        Route::get('/{id}/details', [ActionsController::class, 'getPostDetails']);

        Route::post('/goal/{goalId}/kr/{krId}/create', [ActionsController::class, 'create']);

        Route::post('/upload', [ActionsController::class, 'fileUpload']);

        Route::post('/{id}/update', [ActionsController::class, 'update']);
        Route::post('/{id}/like', [ActionsController::class, 'postLike']);
        Route::post('/{id}/comment', [ActionsController::class, 'postComment']);

        Route::delete('/{id}/delete', [ActionsController::class, 'delete']);
    });
    
    // EmailsController
    Route::prefix('email')->group(function () {
        Route::post('/invite-new-members', [EmailsController::class, 'inviteNewMembers']);
    });

    // ChatController
    Route::prefix('chat')->group(function () {
        Route::get('/rooms', [ChatController::class, 'getUserChatRooms']);
        Route::get('/room/{roomId}/messages', [ChatController::class, 'getChatRoomMessages']);
        
        Route::post('/room/{roomId}/message', [ChatController::class, 'createNewMessage']);
    });

    // MeController
    Route::prefix('me')->group(function () {
        Route::get('/feed', [MeController::class, 'getFeedData']);
        Route::get('/teams', [MeController::class, 'getTeamsBelongToUser']);
        Route::get('/feed-widgets', [MeController::class, 'getHomeFeedWidgets']);
    });
});
