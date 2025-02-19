<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\BaseResourceRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * The base controller class for all controllers that have CRUD operations.
 */
class BaseResourceApiController extends Controller
{
    /**
     * Default page size for pagination.
     */
    const DEFAULT_PAGE_SIZE = 25;

    protected BaseResourceRepository $repository;

    /**
     * Set the repository to use for the controller.
     */
    public function __construct(BaseResourceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a paginated listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $size = $request->input('size', self::DEFAULT_PAGE_SIZE);
        $data = $this->repository->paginatedList($size, $request->all());

        return response()->json($data, 200);
    }

    /**
     * Create a new resource.
     */
    public function save(Request $request): JsonResponse
    {
        try {
            $data = $this->repository->save($request->all());

            return successResponse("Successfully created {$this->getModelName()}", $data, 201);
        } catch(\Exception $e) {
            Log::error($e->getMessage());
            return internalErrorResponse();
        }
    }

    /**
     * Retrieve a resource by its ID.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->repository->find($id);

        if (!$data) {
            return notFoundResponse(ucfirst($this->getModelName()) . " not found");
        }

        return successResponse("Successfully retrieved {$this->getModelName()}", $data);
    }

    /**
     * Update a resource by its ID.
     */
    public function patch(Request $request, int $id): JsonResponse
    {
        try {
            $data = $this->repository->update($request->all(), $id);
            if(!$data) {
                return notFoundResponse(ucfirst($this->getModelName()) . " not found");
            }
            return successResponse("Successfully updated {$this->getModelName()}", $data);
        } catch(\Exception $e) {
            Log::error($e->getMessage());
            return internalErrorResponse();
        }
    }

    /**
     * Remove a resource by its ID.
     */
    public function destroy(int $id): JsonResponse
    {
        if (!$this->repository->find($id)) {
            return notFoundResponse(ucfirst($this->getModelName()) . " not found");
        }

        $this->repository->delete($id);

        return successResponse("Successfully deleted {$this->getModelName()}", []);
    }

    /**
     * Get the model name dynamically.
     */
    protected function getModelName(): string
    {
        return strtolower(class_basename($this->repository->getModel()));
    }
}
