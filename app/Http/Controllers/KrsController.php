<?php

namespace App\Http\Controllers;

use App\AppService\KrsService;
use App\Dto\KrDto;
use App\Dto\ResponseDto;
use App\Enums\Goals\KrCurrencyType;
use App\Enums\Goals\KrUnit;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class KrsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $userId
     * @return \Illuminate\Http\Response
     */
    public function allKrs(Request $request, $userId)
    {
        $krService = new KrsService();
        $response = new ResponseDto();

        try {
            $krs = $krService->getAll($userId);
            $response->success = $krs !== null;
            $response->data = $krs ?? [];
        } catch (Exception $ex) {
            $response->success = false;
            $response->message = $ex->getMessage();
            return response($response->toArray(), 500);
        }

        return response($response->toArray(), 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $userId
     * @param int $goalId
     * @return \Illuminate\Http\Response
     */
    public function getMyKrsBelongInGoal(Request $request, $userId, $goalId)
    {
        $krService = new KrsService();
        $response = new ResponseDto();

        try {
            $krs = $krService->getMyKrsBelongInGoal($userId, $goalId);
            $response->success = $krs !== null;
            $response->data = $krs ?? [];
        } catch (Exception $ex) {
            $response->success = false;
            $response->message = $ex->getMessage();
            return response($response->toArray(), 500);
        }

        return response($response->toArray(), 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $goalId
     * @return \Illuminate\Http\Response
     */
    public function getAllKrsInGoal(Request $request, $goalId)
    {
        $krService = new KrsService();
        $response = new ResponseDto();

        try {
            $krs = $krService->getAllKrsInGoal($goalId);
            $response->success = $krs !== null;
            $response->data = $krs ?? [];
        } catch (Exception $ex) {
            $response->success = false;
            $response->message = $ex->getMessage();
            return response($response->toArray(), 500);
        }

        return response($response->toArray(), 200);
    }

    /**
     * Creating a new resource.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param int $goalId
     * @return \Illuminate\Http\Response
     */
    public function create(Request  $request, $goalId)
    {
        $newKr = $request->all();
        $response = new ResponseDto();

        $validator = Validator::make($newKr, [
            "name" => 'required|string|min:1',
            "goal_id" => [
                'required',
                Rule::in([$goalId])
            ],
            "unit" => [
                'required',
                Rule::in([
                    KrUnit::KR_NUMERIC,
                    KrUnit::KR_CURRENCY,
                    KrUnit::KR_PERSENTAGE,
                    KrUnit::KR_TRUE_FALSE,
                ])
            ],
            "start_value" => 'required|numeric|min:0',
            "target_value" => 'required|numeric|min:0',
        ]);

        if (!$validator->fails() && $newKr['unit'] === KrUnit::KR_CURRENCY) {
            $validator = Validator::make($newKr, [
                "currency_type" => [
                    'required',
                    Rule::in([
                        KrCurrencyType::TYPE_USD,
                        KrCurrencyType::TYPE_JPY,
                        KrCurrencyType::TYPE_KRW,
                        KrCurrencyType::TYPE_CNY,
                        KrCurrencyType::TYPE_EUR,
                    ])
                ]
            ]);
        }
        if (!$validator->fails() && $newKr['unit'] === KrUnit::KR_TRUE_FALSE) {
            $validator = Validator::make($newKr, [
                "target_value" => 'required|numeric|min:0|max:1'
            ]);
        }
        if ($validator->fails()) {
            $response->success = false;
            $response->data = $validator->getMessageBag();
            return response($response->toArray(), 400);
        }
        
        $krService = new KrsService();

        $krData = new KrDto();
        $krData->userId = auth()->id();
        $krData->goalId = $goalId;
        $krData->krUnit = $newKr['unit'];
        $krData->krCurrencyType = (intval($newKr['unit']) === KrUnit::KR_CURRENCY) ? $newKr['currency_type'] : -1;
        $krData->startValue = $newKr['start_value'];
        $krData->targetValue = $newKr['target_value'];
        $krData->name = $newKr['name'];

        try {
            $response->success = $krService->createKr($krData);
            $response->message = $response->success ? "Key-result created successfully!" : "Goal does not exist!";
        } catch (Exception $ex) {
            $response->success = false;
            $response->message = $ex->getMessage();
            return response($response->toArray(), 500);
        }

        return response($response->toArray(), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $kr = $request->kr;
        $response = new ResponseDto();

        $validator = Validator::make($kr, [
            "name" => 'required|string|min:1',
            "target_value" => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            $response->success = false;
            $response->data = $validator->getMessageBag();
            return response($response->toArray(), 400);
        }

        $krService = new KrsService();
        try {
            $response->success = $krService->updateKr($id, $kr['name'], $kr['target_value']);
            $response->message = $response->success ? "Key-result updated successfully!" : "Something went wrong?";
        } catch (Exception $ex) {
            $response->success = false;
            $response->data = $ex->getMessage();
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
    public function delete($id)
    {
        $response = new ResponseDto();
        $krService = new KrsService();

        try {
            $response->success = $krService->softDeleteKr($id);
            $response->message = $response->success ? 
                'Key-result, including all actions related to this key-result deleted!' : 
                "Key-result deos not exist";
        } catch (Exception $ex) {
            $response->success = false;
            $response->message = $ex->getMessage();
            return response($response->toArray(), 500);
        }

        return response($response->toArray(), 200);
    }
}
