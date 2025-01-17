<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use Illuminate\Http\Request;
use Auth;

class ChatListController extends Controller
{
    public function index()
    {
        $chat_rooms = ChatRoom::with(['user', 'latestMessage'])
            ->where('admin_id', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.chat.chat_list', compact('chat_rooms'));
    }
}
