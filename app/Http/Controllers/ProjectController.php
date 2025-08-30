<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::query()
            ->with(['client', 'documents', 'importantUrls', 'updatedBy']);

        $limit = (int) $request->input('limit', 50);
        $projects = $query->limit($limit)->get();

        return response()->json(['projects' => $projects]);
    }
}
