<?php
namespace App\Repositories;

use App\Http\Resources\BaseResourceCollection;
use App\Services\FileService;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class BaseResourceRepository
{
    protected Model $model;
    protected FileService $fileService;

    public function __construct(Model $model, FileService $fileService)
    {
        $this->model       = $model;
        $this->fileService = $fileService;
    }

    /**
     * Return the underlying model instance.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Retrieve a paginated list of resources, applying search query and sorting dynamically.
     *
     * @param int $size The number of items to return per page.
     * @param array $queryParams An array of query parameters which may include the search query, sort field, and sort order.
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function paginatedList(int $size, array $queryParams)
    {
        $searchParam = $this->getSearchQuery($queryParams);
        $sort        = $this->getSortField($queryParams, 'created_at'); // Default sort by `created_at`
        $order       = $this->getOrderBy($queryParams, 'desc');         // Default order is `desc`
        $query       = $this->model->newQuery();

        // Apply search query only if the model supports it
        if (! empty($searchParam) && method_exists($this->model, 'search')) {
            $query = $query->search($searchParam);
        }

        // Apply sorting and ordering if method exists
        if (method_exists($this->model, 'ofOrder')) {
            $query = $query->ofOrder($sort, $order);
        } else {
            $query->orderBy($sort, $order);
        }

        $data = $query->paginate($size);

        return new BaseResourceCollection($data);
    }

    /**
     * Save a new resource, handling file uploads dynamically.
     */
    public function save(array $input): Model | JsonResource
    {
        try {
            return DB::transaction(function () use ($input) {
                if ($this->hasFileUploads($input)) {
                    $input = $this->handleFileUploads($input);
                }

                $model = $this->model->create($input);

                return $this->resolveResource($model);
            });
        } catch (QueryException $e) {
            return $this->handleException($e, "Failed to create " . class_basename($this->model));
        } catch (Exception $e) {
            return $this->handleException($e, "Unexpected error while creating " . class_basename($this->model));
        }
    }

    /**
     * Find a resource by its ID.
     *
     * @param int $id The ID of the resource to find.
     *
     * @return \Illuminate\Database\Eloquent\Model|null The found resource instance or null if not found.
     */
    public function find($id): Model | JsonResource | null
    {
        return $this->resolveResource($this->model->find($id));
    }

    /**
     * Update an existing resource, handling file uploads dynamically.
     */
    public function update(array $input, int $id): Model | JsonResource | null
    {
        try {
            return DB::transaction(function () use ($input, $id) {
                $data = $this->model->find($id);
                if (! $data) {
                    return null;
                }

                if ($this->hasFileUploads($input)) {
                    $input = $this->handleFileUploads($input, $data);
                }

                $data->update($input);

                return $this->resolveResource($data);
            });
        } catch (QueryException $e) {
            return $this->handleException($e, "Failed to update " . class_basename($this->model));
        } catch (Exception $e) {
            return $this->handleException($e, "Unexpected error while updating " . class_basename($this->model));
        }
    }

    /**
     * Delete a resource and its associated files if applicable.
     */
    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $data = $this->model->find($id);
            if ($data) {
                $this->deleteFiles($data);
                $data->delete();
            }
        });
    }

    /**
     * Check if there are any file uploads in the input.
     */
    protected function hasFileUploads(array $input): bool
    {
        foreach ($input as $value) {
            if ($value instanceof UploadedFile) {
                return true;
            }
        }
        return false;
    }

    /**
     * Handle file uploads dynamically for defined file fields.
     */
    protected function handleFileUploads(array $input, ?Model $existingData = null): array
    {
        $fileFields = method_exists($this->model, 'getFileFields') ? $this->model->getFileFields() : [];

        foreach ($fileFields as $field) {
            if (isset($input[$field]) && $input[$field] instanceof UploadedFile) {
                // Delete old file if updating
                if ($existingData && isset($existingData->{$field})) {
                    $this->fileService->deleteFile($existingData->{$field});
                }

                // Determine storage path from model
                $storagePath = method_exists($this->model, 'getStoragePath')
                ? $this->model->getStoragePath()
                : 'uploads';

                $input[$field] = $this->fileService->uploadFile($input[$field], "{$storagePath}/{$field}");
            }
        }

        return $input;
    }

    /**
     * Delete associated files for a model dynamically.
     */
    protected function deleteFiles(Model $data): void
    {
        $fileFields = method_exists($this->model, 'getFileFields') ? $this->model->getFileFields() : [];

        foreach ($fileFields as $field) {
            if (! empty($data->{$field}) && $this->fileService->fileExists($data->{$field})) {
                $this->fileService->deleteFile($data->{$field});
            }
        }
    }

    /**
     * Extracts the search query from request parameters.
     */
    protected function getSearchQuery(array $queryParams): ?string
    {
        return $queryParams['search'] ?? null;
    }

    /**
     * Extracts the sort field from request parameters.
     */
    protected function getSortField(array $queryParams, string $default = 'id'): string
    {
        return $queryParams['sort'] ?? $default;
    }

    /**
     * Extracts the order direction (asc/desc) from request parameters.
     */
    protected function getOrderBy(array $queryParams, string $default = 'asc'): string
    {
        $order = strtolower($queryParams['order'] ?? $default);

        return in_array($order, ['asc', 'desc']) ? $order : $default;
    }

    /**
     * Resolve the appropriate resource class if it exists, otherwise return the model.
     *
     * @param Model|null $model The model instance.
     * @return mixed The model or the resource class.
     */
    protected function resolveResource(?Model $model): Model | JsonResource | null
    {
        if (! $model) {
            return null;
        }

        $modelName     = class_basename($this->model);
        $resourceClass = "App\\Http\\Resources\\{$modelName}Resource";

        if (class_exists($resourceClass)) {
            return new $resourceClass($model);
        }

        return $model; // Return the model if no resource class exists
    }

    /**
     * Handle exceptions and return an error response.
     *
     * @param Exception $e The exception instance.
     * @param string $message Custom error message.
     * @return array Formatted error response.
     */
    protected function handleException(Exception $e, string $message): array
    {
        return [
            'error'     => true,
            'message'   => $message,
            'exception' => $e->getMessage(),
        ];
    }
}
