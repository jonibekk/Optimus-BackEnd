<?php

namespace App\Http\Controllers;

use App\Dto\ResponseDto;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{

    public function getUserChatRooms(Request $request)
    {
        $responseDto = new ResponseDto();
        $user = auth()->user();

        $responseDto->data = ChatRoom::where('team_id', $user['current_team_id'])->get();
        $responseDto->success = true;

        return response($responseDto->toArray(), 200);
    }

    public function getChatRoomMessages(Request $request, int $roomId)
    {
        $responseDto = new ResponseDto();

        $responseDto->data = ChatMessage::where('chat_room_id', $roomId)
            ->with('user')
            ->orderBy('created_at', 'DESC')
            ->get();
        $responseDto->success = true;

        return response($responseDto->toArray(), 200);
    }

    public function createNewMessage(Request $request, int $roomId)
    {
        $responseDto = new ResponseDto();

        $newMessage = new ChatMessage();
        $newMessage->user_id = auth()->id();
        $newMessage->chat_room_id = $roomId;
        $newMessage->message = $request->message;

        $newMessage->save();

        $responseDto->data = ChatMessage::where('id', $newMessage->id)
            ->with('user')
            ->get();
        $responseDto->success = true;

        return response($responseDto->toArray(), 200);
    }

    public function createChatRoom(Request $request)
    {
        $responseDto = new ResponseDto();

        $validator = Validator::make($request->all(), [
            'team_id' => 'required|numeric|min:1',
            'name' => 'required|string|min:1|max:255',
            'description' => 'required|string|min:1|max:255',
        ]);

        return response($responseDto->toArray(), 200);
    }

    public function updateChatRoom(Request $request, $id)
    {
        //
    }

    public function deleteMessage($id)
    {
        //
    }
}
