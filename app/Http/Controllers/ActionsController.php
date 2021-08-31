<?php

namespace App\Http\Controllers;

use App\AppService\ActionsService;
use App\Dto\ActionDto;
use App\Dto\ResponseDto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Exception;

class ActionsController extends Controller
{
    /**
     *
     * @param  int  $teamId
     * @param  int  $userId
     * @return \Illuminate\Http\Response
     */
    public function getUserActions(int $teamId, int $userId)
    {
        $actionService = new ActionsService();
        $response = new ResponseDto();

        try {
            $response->data = $actionService->getUserActions($teamId, $userId);
            $response->success = true;
        } catch (Exception $ex) {
            $response->success = false;
            $response->message = $ex->getMessage();
            return response($response->toArray(), 500);
        }

        return response($response->toArray(), 200);
    }

    /**
     *
     * @param  int  $krId
     * @return \Illuminate\Http\Response
     */
    public function getKrActions(int $krId)
    {
        $actionService = new ActionsService();
        $response = new ResponseDto();

        try {
            $response->data = $actionService->getKrActions($krId);
            $response->success = true;
        } catch (Exception $ex) {
            $response->success = false;
            $response->message = $ex->getMessage();
            return response($response->toArray(), 500);
        }

        return response($response->toArray(), 200);
    }

    /**
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getPostDetails(int $id)
    {
        $actionService = new ActionsService();
        $response = new ResponseDto();

        try {
            $response->data = $actionService->getPostDetails($id);
            $response->success = true;
        } catch (Exception $ex) {
            $response->success = false;
            $response->message = $ex->getMessage();
            return response($response->toArray(), 500);
        }

        return response($response->toArray(), 200);
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $goalId
     * @param  int $krId
     * @return \Illuminate\Http\Response
     */
    public function create(Request  $request, $goalId, $krId)
    {
        $newAction = $request->all();
        $response = new ResponseDto();
        $actionData = new ActionDto();
        $actionService = new ActionsService();

        $validator = Validator::make($newAction, [
            'body' => 'required|min:0|max:2000',
            'kr_current_value' => 'required|numeric',
            'kr_added_value' => 'required|numeric',
            'images_length' => 'required|integer|min:1|max:3',
        ]);
        if ($validator->fails()) {
            $response->success = false;
            $response->data = $validator->getMessageBag();
            return response($response->toArray(), 400);
        }

        $images = [];
        for ($i = 0; $i < $newAction['images_length']; $i++) {
            $validator = Validator::make($newAction, ['images' . $i => 'required|mimes:jpeg,png']);
            $images[$i] = $request->file('images' . $i);
            if ($validator->fails()) {
                $response->success = false;
                $response->data = $validator->getMessageBag();
                return response($response->toArray(), 400);
            }
        }
        
        $actionData->body = $newAction['body'];
        $actionData->krCurrentValue = $newAction['kr_current_value'];
        $actionData->krNewValue = $newAction['kr_added_value'];
        $actionData->images = $images;
        try {
            $response->success = $actionService->createAction($goalId, $krId, $actionData, $images);
            $response->message = $response->success ? "Action created successfully!" : "Something went wrong?";
        } catch (Exception $ex) {
            $response->success = false;
            $response->message = $ex->getMessage();
            return response($response->toArray(), 500);
        }

        return response($response->toArray(), 200);
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $action = $request->action;
        $response = new ResponseDto();
        $actionService = new ActionsService();

        $validator = Validator::make($action, ['body' => 'required|min:0|max:2000']);
        if ($validator->fails()) {
            $response->success = false;
            $response->data = $validator->getMessageBag();
            return response($response->toArray(), 400);
        }

        try {
            $response->success = $actionService->updateAction($id, $action['body']);
            $response->message = $response->success ? "Action updated successfully!" : "Something went wrong?";
        } catch (Exception $ex) {
            $response->success = false;
            $response->message = $ex->getMessage();
            return response($response->toArray(), 500);
        }

        return response($response->toArray(), 200);
    }

    /**
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $response = new ResponseDto();
        $actionService = new ActionsService();

        try {
            $response->success = $actionService->softDeleteAction($id);
            $response->message = $response->success ?
                'Action deleted!' :
                "Action deos not exist";
        } catch (Exception $ex) {
            $response->success = false;
            $response->message = $ex->getMessage();
            return response($response->toArray(), 500);
        }

        return response($response->toArray(), 200);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function postLike(Request $request, $id)
    {
        $response = new ResponseDto();
        $actionService = new ActionsService();

        $validator = Validator::make($request->all(), [
            'status' => [
                'required',
                Rule::in(['like', 'dislike'])
            ]
        ]);
        if ($validator->fails()) {
            $response->success = false;
            $response->data = $validator->getMessageBag();
            return response($response->toArray(), 400);
        }

        $userId = auth()->id();
        $status = $request['status'];
        $likeId = $request['like_id'];

        try {
            // return response(['like' => $actionService->likeActionPost($id, $userId, $status, $likeId)], 500);
            $response->success = $actionService->likeActionPost($id, $userId, $status, $likeId);

            if ($response->success === false) {
                $response->message = 'Something went wrong! Please, try again.';
                return response($response->toArray(), 500);
            }

            $response->message = 'Liked successfully!';
        } catch (Exception $e) {
            $response->success = false;
            $response->message = $e->getMessage();
            return response($response->toArray(), 500);
        }

        return response($response->toArray(), 200);
    }
}
