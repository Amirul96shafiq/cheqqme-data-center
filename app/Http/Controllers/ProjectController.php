<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectApiResource;
use App\Models\Project;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $query = Project::query()
                ->with(['client', 'documents', 'importantUrls', 'updatedBy']);

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
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%")
                            ->orWhere('notes', 'like', "%{$search}%");
                    } else {
                        $q->where('title', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%")
                            ->orWhere('notes', 'like', "%{$search}%");
                    }
                });
            }

            // Add filtering by status
            if ($request->filled('status')) {
                $status = $request->input('status');
                $query->where('status', $status);
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

            // Add filtering by has_documents
            if ($request->filled('has_documents')) {
                $hasDocuments = $request->boolean('has_documents');
                if ($hasDocuments) {
                    $query->has('documents');
                } else {
                    $query->doesntHave('documents');
                }
            }

            // Add filtering by has_important_urls
            if ($request->filled('has_important_urls')) {
                $hasImportantUrls = $request->boolean('has_important_urls');
                if ($hasImportantUrls) {
                    $query->has('importantUrls');
                } else {
                    $query->doesntHave('importantUrls');
                }
            }

            // Add sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $limit = (int) $request->input('limit', 50);
            $projects = $query->limit($limit)->get();

            return $this->successResponse(
                ProjectApiResource::collection($projects),
                'Projects retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve projects',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
