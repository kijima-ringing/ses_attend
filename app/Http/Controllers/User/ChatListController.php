<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use Illuminate\Http\Request;
use Auth;

class ChatListController extends Controller
{
    public function index()
    {
        // ログインユーザーに関連するチャットルームを取得
        $chat_rooms = ChatRoom::with(['user', 'latestMessage'])
            ->where('user_id', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('user.chat.chat_list', compact('chat_rooms'));
    }
}
