<?php

namespace App\Http\Controllers;

use App\Http\Resources\PhoneNumberApiResource;
use App\Models\PhoneNumber;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class PhoneNumberController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $query = PhoneNumber::query()
                ->with(['updatedBy']);

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
                          ->orWhere('phone', 'like', "%{$search}%")
                          ->orWhere('notes', 'like', "%{$search}%");
                    } else {
                        $q->where('title', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%")
                          ->orWhere('notes', 'like', "%{$search}%");
                    }
                });
            }

            // Add filtering by phone number
            if ($request->filled('phone')) {
                $phone = $request->input('phone');
                $query->where('phone', 'like', "%{$phone}%");
            }

            // Add filtering by updated_by
            if ($request->filled('updated_by')) {
                $updatedBy = $request->input('updated_by');
                $query->where('updated_by', $updatedBy);
            }

            // Add filtering by has_notes (phone numbers with notes)
            if ($request->filled('has_notes')) {
                $hasNotes = $request->boolean('has_notes');
                if ($hasNotes) {
                    $query->whereNotNull('notes')->where('notes', '!=', '');
                } else {
                    $query->where(function ($q) {
                        $q->whereNull('notes')->orWhere('notes', '');
                    });
                }
            }

            // Add filtering by phone number format (mobile, landline, etc.)
            if ($request->filled('phone_format')) {
                $phoneFormat = $request->input('phone_format');
                switch ($phoneFormat) {
                    case 'mobile':
                        $query->where('phone', 'like', '%+6%')->orWhere('phone', 'like', '%01%');
                        break;
                    case 'landline':
                        $query->where('phone', 'like', '%03%')->orWhere('phone', 'like', '%04%')->orWhere('phone', 'like', '%05%')->orWhere('phone', 'like', '%06%')->orWhere('phone', 'like', '%07%')->orWhere('phone', 'like', '%08%')->orWhere('phone', 'like', '%09%');
                        break;
                    case 'international':
                        $query->where('phone', 'like', '%+%');
                        break;
                }
            }

            // Add sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $limit = (int) $request->input('limit', 50);
            $phoneNumbers = $query->limit($limit)->get();

            return $this->successResponse(
                PhoneNumberApiResource::collection($phoneNumbers),
                'Phone numbers retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve phone numbers',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
