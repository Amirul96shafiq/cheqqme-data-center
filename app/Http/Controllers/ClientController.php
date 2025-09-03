<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClientApiResource;
use App\Models\Client;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $query = Client::query()
                ->with(['projects', 'documents', 'importantUrls', 'updatedBy']);

            // Add search functionality
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('pic_name', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('pic_email', 'like', "%{$search}%")
                      ->orWhere('company_email', 'like', "%{$search}%");
                });
            }

            // Add filtering by status or other criteria
            if ($request->has('has_projects')) {
                $hasProjects = $request->boolean('has_projects');
                if ($hasProjects) {
                    $query->has('projects');
                } else {
                    $query->doesntHave('projects');
                }
            }

            // Add sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $limit = (int) $request->input('limit', 50);
            $clients = $query->limit($limit)->get();

            return $this->successResponse(
                ClientApiResource::collection($clients),
                'Clients retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve clients',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
