<?php

namespace App\Http\Controllers;

use App\AppService\MeService;
use App\Dto\ResponseDto;
use Exception;
use Illuminate\Http\Request;

class MeController extends Controller
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getFeedData(Request  $request)
    {
        $meService = new MeService();
        $response = new ResponseDto();

        try {
            $response->data = $meService->loadFeedActions();
            $response->success = true;
        } catch (Exception $ex) {
            $response->message = $ex->getMessage();
            $response->success = false;

            return response($response->toArray(), 500);
        }

        return response($response->toArray(), 200);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getTeamsBelongToUser(Request  $request)
    {
        $meService = new MeService();
        $response = new ResponseDto();

        try {
            $response->data = $meService->getTeamsBelongToUser(auth()->id());
            $response->success = true;
        } catch (Exception $ex) {
            $response->message = $ex->getMessage();
            $response->success = false;

            return response($response->toArray(), 500);
        }

        return response($response->toArray(), 200);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getHomeFeedWidgets(Request  $request)
    {
        $meService = new MeService();
        $response = new ResponseDto();

        try {
            $response->data = $meService->getHomeFeedWidgets();
            $response->success = true;
        } catch (Exception $ex) {
            $response->message = $ex->getMessage();
            $response->success = false;

            return response($response->toArray(), 500);
        }

        return response($response->toArray(), 200);
    }
}
