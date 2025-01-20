<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Auth;

class ChatListController extends Controller
{
    public function index()
    {
        // ログインユーザーに関連するチャットルームを取得
        $chat_rooms = ChatRoom::with(['admin', 'latestMessage'])
            ->where('user_id', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->get();

        // 未読メッセージの確認
        $unreadMessages = ChatMessage::where('user_id', '!=', Auth::id())
            ->where('read_flag', 0)
            ->whereIn('chat_room_id', $chat_rooms->pluck('id'))
            ->exists();

        return view('user.chat.chat_list', compact('chat_rooms', 'unreadMessages'));
    }
}
