<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Auth;

class ChatRoomController extends Controller
{
    public function show($room_id)
    {
        $chat_room = ChatRoom::with(['admin', 'messages.user'])
            ->where('id', $room_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('user.chat.chat_room', compact('chat_room'));
    }

    public function sendMessage(Request $request, $room_id)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $chat_room = ChatRoom::where('id', $room_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $lastId = ChatMessage::max('id') ?? 0;
        $now = \Carbon\Carbon::now()->timezone('Asia/Tokyo');

        $message = ChatMessage::create([
            'id' => $lastId + 1,
            'chat_room_id' => $room_id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'read_flag' => 0,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
            'created_at' => $now->setTimezone('UTC'),
            'updated_at' => $now->setTimezone('UTC')
        ]);

        return response()->json(['success' => true]);
    }

    public function checkNewMessages(Request $request, $room_id)
    {
        $lastMessageId = $request->input('last_message_id');

        $chat_room = ChatRoom::where('id', $room_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $newMessages = ChatMessage::where('chat_room_id', $room_id)
            ->where('id', '>', $lastMessageId)
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'is_current_user' => $message->user_id == Auth::id(),
                    'created_at' => \Carbon\Carbon::parse($message->created_at)
                        ->timezone('Asia/Tokyo')
                        ->format('Y-n-j H:i')
                ];
            });

        return response()->json(['messages' => $newMessages]);
    }
}
