<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\User;
use Illuminate\Http\Request;
use Auth;

class ChatListController extends Controller
{
    public function index()
    {
        // admin_flagが0のユーザーを取得
        $users = User::where('admin_flag', 0)->get();

        // 各ユーザーに対応するチャットルームを取得
        $chat_rooms = ChatRoom::with(['user', 'latestMessage'])
            ->where('admin_id', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.chat.chat_list', compact('users', 'chat_rooms'));
    }
}
