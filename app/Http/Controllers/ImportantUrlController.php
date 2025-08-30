<?php

namespace App\Http\Controllers;

use App\Models\ImportantUrl;
use Illuminate\Http\Request;

class ImportantURLController extends Controller
{
    public function index(Request $request)
    {
        $query = ImportantUrl::query()
            ->with(['client', 'project', 'updatedBy']);

        $limit = (int) $request->input('limit', 50);
        $importantUrls = $query->limit($limit)->get();

        return response()->json(['importantUrls' => $importantUrls]);
    }
}
