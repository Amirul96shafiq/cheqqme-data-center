<?php

namespace App\Http\Controllers;

use App\Models\PhoneNumber;
use Illuminate\Http\Request;

class PhoneNumberController extends Controller
{
    public function index(Request $request)
    {
        $query = PhoneNumber::query()
            ->with(['updatedBy']);

        $limit = (int) $request->input('limit', 50);
        $phoneNumbers = $query->limit($limit)->get();

        return response()->json(['phoneNumbers' => $phoneNumbers]);
    }
}
