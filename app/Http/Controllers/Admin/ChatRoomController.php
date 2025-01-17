<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Auth;

class ChatRoomController extends Controller
{
    public function show($room_id)
    {
        $chat_room = ChatRoom::with(['user', 'messages.user'])
            ->where('id', $room_id)
            ->where('admin_id', Auth::id())
            ->firstOrFail();

        return view('admin.chat.chat_room', compact('chat_room'));
    }

    public function sendMessage(Request $request, $room_id)
    {
        $chat_room = ChatRoom::where('id', $room_id)
            ->where('admin_id', Auth::id())
            ->firstOrFail();

        ChatMessage::create([
            'chat_room_id' => $room_id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id()
        ]);

        return redirect()->route('admin.chat.room', ['room_id' => $room_id]);
    }
}
