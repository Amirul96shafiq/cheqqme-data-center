<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        $limit = (int) $request->input('limit', 50);
        $users = $query->limit($limit)->get();

        return response()->json(['users' => $users]);
    }
}
