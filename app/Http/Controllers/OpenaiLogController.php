<?php

namespace App\Http\Controllers;

use App\Models\OpenaiLog;
use Illuminate\Http\Request;

class OpenaiLogController extends Controller
{
    // Web UI index (auth middleware is configured on the route)
    public function index(Request $request)
    {
        $query = OpenaiLog::query();
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }
        if ($request->filled('conversation_id')) {
            $query->where('conversation_id', $request->input('conversation_id'));
        }
        if ($request->filled('model')) {
            $query->where('model', $request->input('model'));
        }
        if ($since = $request->input('since')) {
            $query->where('created_at', '>=', $since);
        }
        if ($until = $request->input('until')) {
            $query->where('created_at', '<=', $until);
        }
        $limit = (int) $request->input('limit', 100);
        $logs = $query->orderByDesc('created_at')->limit($limit)->get();

        return response()->json(['logs' => $logs]);
    }

    // API index (token-auth via Sanctum on routes)
    public function apiIndex(Request $request)
    {
        // Scope to the authenticated user if available
        $query = OpenaiLog::query();
        if ($user = $request->user()) {
            $query->where('user_id', $user->id);
        }
        if ($request->filled('conversation_id')) {
            $query->where('conversation_id', $request->input('conversation_id'));
        }
        $limit = (int) $request->input('limit', 50);
        $logs = $query->orderByDesc('created_at')->limit($limit)->get();
        $payload = $logs->map(function ($log) {
            return [
                'id' => $log->id,
                'conversation_id' => $log->conversation_id,
                'model' => $log->model,
                'endpoint' => $log->endpoint,
                'response_text' => json_decode($log->response_text),
                'created_at' => $log->created_at,
            ];
        });

        return response()->json(['logs' => $payload]);
    }

    // Health check endpoint for API consumption
    public function health(Request $request)
    {
        return response()->json(['ok' => true, 'user_id' => $request->user()?->id]);
    }
}
