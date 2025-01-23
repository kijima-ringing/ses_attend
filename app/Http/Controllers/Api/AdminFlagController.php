<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminFlagController extends Controller
{
    public function check()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                Log::error('User not authenticated in AdminFlagController');
                return response()->json([
                    'error' => 'User not authenticated',
                    'admin_flag' => '0'
                ], 401);
            }

            Log::info('Admin flag check', [
                'user_id' => $user->id,
                'admin_flag' => $user->admin_flag
            ]);

            return response()->json([
                'admin_flag' => (string)$user->admin_flag
            ]);

        } catch (\Exception $e) {
            Log::error('Error in AdminFlagController', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Internal server error',
                'admin_flag' => '0'
            ], 500);
        }
    }
} 