<?php

namespace App\Http\Controllers;

use App\Http\Resources\DocumentApiResource;
use App\Models\Document;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $query = Document::query()
                ->with(['project', 'updatedBy']);

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
                          ->orWhere('type', 'like', "%{$search}%")
                          ->orWhere('url', 'like', "%{$search}%")
                          ->orWhere('notes', 'like', "%{$search}%");
                    } else {
                        $q->where('title', 'like', "%{$search}%")
                          ->orWhere('type', 'like', "%{$search}%")
                          ->orWhere('url', 'like', "%{$search}%")
                          ->orWhere('notes', 'like', "%{$search}%");
                    }
                });
            }

            // Add filtering by type
            if ($request->filled('type')) {
                $type = $request->input('type');
                $query->where('type', $type);
            }

            // Add filtering by project_id
            if ($request->filled('project_id')) {
                $projectId = $request->input('project_id');
                $query->where('project_id', $projectId);
            }

            // Add filtering by updated_by
            if ($request->filled('updated_by')) {
                $updatedBy = $request->input('updated_by');
                $query->where('updated_by', $updatedBy);
            }

            // Add filtering by has_file_path (documents with actual files)
            if ($request->filled('has_file_path')) {
                $hasFilePath = $request->boolean('has_file_path');
                if ($hasFilePath) {
                    $query->whereNotNull('file_path');
                } else {
                    $query->whereNull('file_path');
                }
            }

            // Add filtering by has_url (documents with URLs)
            if ($request->filled('has_url')) {
                $hasUrl = $request->boolean('has_url');
                if ($hasUrl) {
                    $query->whereNotNull('url');
                } else {
                    $query->whereNull('url');
                }
            }

            // Add sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $limit = (int) $request->input('limit', 50);
            $documents = $query->limit($limit)->get();

            return $this->successResponse(
                DocumentApiResource::collection($documents),
                'Documents retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve documents',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
