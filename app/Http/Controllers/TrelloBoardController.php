<?php

namespace App\Http\Controllers;

use App\Http\Resources\TrelloBoardApiResource;
use App\Models\TrelloBoard;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class TrelloBoardController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $query = TrelloBoard::query()
                ->with(['updatedBy', 'creator']);

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
                          ->orWhere('name', 'like', "%{$search}%")
                          ->orWhere('url', 'like', "%{$search}%")
                          ->orWhere('notes', 'like', "%{$search}%");
                    } else {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('url', 'like', "%{$search}%")
                          ->orWhere('notes', 'like', "%{$search}%");
                    }
                });
            }

            // Add filtering by show_on_boards
            if ($request->filled('show_on_boards')) {
                $showOnBoards = $request->boolean('show_on_boards');
                $query->where('show_on_boards', $showOnBoards);
            }

            // Add filtering by created_by
            if ($request->filled('created_by')) {
                $createdBy = $request->input('created_by');
                $query->where('created_by', $createdBy);
            }

            // Add filtering by updated_by
            if ($request->filled('updated_by')) {
                $updatedBy = $request->input('updated_by');
                $query->where('updated_by', $updatedBy);
            }

            // Add sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $limit = (int) $request->input('limit', 50);
            $trelloBoards = $query->limit($limit)->get();

            return $this->successResponse(
                TrelloBoardApiResource::collection($trelloBoards),
                'Trello boards retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve Trello boards',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
