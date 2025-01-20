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

        // 最後のIDを取得して+1する
        $lastId = ChatMessage::max('id') ?? 0;

        // UTCの現在時刻を取得し、Asia/Tokyoに変換
        $now = \Carbon\Carbon::now()->timezone('Asia/Tokyo');

        ChatMessage::create([
            'id' => $lastId + 1,
            'chat_room_id' => $room_id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'read_flag' => 0,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
            'created_at' => $now->setTimezone('UTC'),  // UTCに戻して保存
            'updated_at' => $now->setTimezone('UTC')   // UTCに戻して保存
        ]);

        return redirect()->route('user.chat.room', ['room_id' => $room_id]);
    }
}
