<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

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
   ]
  ], 200, [], JSON_PRETTY_PRINT);
 }
}
