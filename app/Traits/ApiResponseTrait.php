<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

trait ApiResponseTrait
{
    protected function successResponse($data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => [
                'timestamp' => now()->toISOString(),
                'request_id' => $this->generateRequestId(),
            ]
        ], $status, [], JSON_PRETTY_PRINT);
    }

    protected function errorResponse(string $message = 'Error', int $status = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'meta' => [
                'timestamp' => now()->toISOString(),
                'request_id' => $this->generateRequestId(),
            ]
        ], $status, [], JSON_PRETTY_PRINT);
    }

    protected function paginatedResponse($data, $pagination, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'pagination' => $pagination,
            'meta' => [
                'timestamp' => now()->toISOString(),
                'request_id' => $this->generateRequestId(),
            ]
        ], 200, [], JSON_PRETTY_PRINT);
    }

    private function generateRequestId(): string
    {
        // Try to get request ID from headers first (common in microservices)
        $requestId = request()->header('X-Request-ID') 
            ?? request()->header('X-Correlation-ID')
            ?? request()->header('Request-ID');
        
        // If no request ID in headers, generate one
        if (!$requestId) {
            $requestId = Str::uuid()->toString();
        }
        
        return $requestId;
    }
}
