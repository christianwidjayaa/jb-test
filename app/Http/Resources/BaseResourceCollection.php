<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BaseResourceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'pagination' => $this->getPaginationData(),
        ];
    }

    /**
     * Get pagination metadata if available.
     */
    protected function getPaginationData(): array
    {
        if ($this->resource instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            return [
                'totalItems'   => $this->resource->total(),
                'itemsPerPage' => $this->resource->perPage(),
                'currentPage'  => $this->resource->currentPage(),
                'lastPage'     => $this->resource->lastPage(),
                'nextPageUrl'  => $this->resource->nextPageUrl(),
                'prevPageUrl'  => $this->resource->previousPageUrl(),
            ];
        }

        return [];
    }
}
