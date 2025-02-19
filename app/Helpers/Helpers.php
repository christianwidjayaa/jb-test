<?php

use Illuminate\Http\JsonResponse;

/**
 * Return a structured JSON response.
 */
function jsonResponse(
    bool $error,
    int $status,
    ?string $message = null,
    mixed $data = [],
    ?array $errors = null
): JsonResponse {
    return response()->json(array_filter([
        'error'   => $error,
        'status'  => $status,
        'message' => $message,
        'data'    => $data,
        'errors'  => $errors,
    ]), $status);
}

/**
 * Success response with optional message and data.
 */
function successResponse(?string $message = null, mixed $data = [], int $status = 200): JsonResponse
{
    return jsonResponse(false, $status, $message, $data);
}

/**
 * Generic error response with optional error details.
 */
function errorResponse(
    int $status = 500,
    ?string $message = null,
    ?array $errors = null
): JsonResponse {
    return jsonResponse(true, $status, $message ?? 'An error occurred', [], $errors);
}

/**
 * Return a 404 Not Found response.
 *
 * @param string|null $message The error message to return. Defaults to 'Resource not found'.
 * @return JsonResponse A JSON response indicating that the resource was not found.
 */
function notFoundResponse(?string $message = null): JsonResponse
{
    return errorResponse(404, $message ?? 'Resource not found');
}

/**
 * Return a 401 Unauthorized response.
 *
 * @param string|null $message The error message to return. Defaults to 'Unauthorized access'.
 * @return JsonResponse A JSON response indicating unauthorized access.
 */

function unauthorizedResponse(?string $message = null): JsonResponse
{
    return errorResponse(401, $message ?? 'Unauthorized access');
}

/**
 * Return a 422 Unprocessable Entity response with validation error details.
 *
 * @param string|null $message The error message to return. Defaults to 'Validation error'.
 * @param array|null $errors Optional error details to include in the response.
 * @return JsonResponse A JSON response with validation error details.
 */
function validationErrorResponse(?string $message = null, ?array $errors = null): JsonResponse
{
    return errorResponse(422, $message ?? 'Validation error', $errors);
}

/**
 * Return a 500 Internal Server Error response with optional error message and details.
 *
 * @param string|null $message The error message to return. Defaults to 'Internal Error'.
 * @param array|null $errors Optional error details to include in the response.
 * @return JsonResponse The error response.
 */
function internalErrorResponse(?string $message = null, ?array $errors = null): JsonResponse
{
    return errorResponse(500, $message ?? 'Internal Error', $errors);
}
