<?php

namespace App\Http\Controllers;

use App\Http\Resources\ImportantUrlApiResource;
use App\Models\ImportantUrl;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class ImportantURLController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $query = ImportantUrl::query()
                ->with(['client', 'project', 'updatedBy']);

            // Add exact ID search (highest priority)
            if ($request->filled('id')) {
                $id = $request->input('id');
                $query->where('id', $id);
            }
            // Add search functionality (if no specific ID is provided)
            elseif ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    // Check if search is numeric (could be an ID)
                    if (is_numeric($search)) {
                        $q->where('id', $search)
                          ->orWhere('title', 'like', "%{$search}%")
                          ->orWhere('url', 'like', "%{$search}%")
                          ->orWhere('notes', 'like', "%{$search}%");
                    } else {
                        $q->where('title', 'like', "%{$search}%")
                          ->orWhere('url', 'like', "%{$search}%")
                          ->orWhere('notes', 'like', "%{$search}%");
                    }
                });
            }

            // Add filtering by project_id
            if ($request->filled('project_id')) {
                $projectId = $request->input('project_id');
                $query->where('project_id', $projectId);
            }

            // Add filtering by client_id
            if ($request->filled('client_id')) {
                $clientId = $request->input('client_id');
                $query->where('client_id', $clientId);
            }

            // Add filtering by updated_by
            if ($request->filled('updated_by')) {
                $updatedBy = $request->input('updated_by');
                $query->where('updated_by', $updatedBy);
            }

            // Add filtering by has_project (URLs with associated projects)
            if ($request->filled('has_project')) {
                $hasProject = $request->boolean('has_project');
                if ($hasProject) {
                    $query->whereNotNull('project_id');
                } else {
                    $query->whereNull('project_id');
                }
            }

            // Add filtering by has_client (URLs with associated clients)
            if ($request->filled('has_client')) {
                $hasClient = $request->boolean('has_client');
                if ($hasClient) {
                    $query->whereNotNull('client_id');
                } else {
                    $query->whereNull('client_id');
                }
            }

            // Add sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $limit = (int) $request->input('limit', 50);
            $importantUrls = $query->limit($limit)->get();

            return $this->successResponse(
                ImportantUrlApiResource::collection($importantUrls),
                'Important URLs retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve important URLs',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
