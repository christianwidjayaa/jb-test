<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PostCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data'       => PostResource::collection($this->collection),
            'pagination' => $this->getPaginationData(),
        ];
    }

    protected function getPaginationData(): array
    {
        return $this->resource instanceof \Illuminate\Pagination\LengthAwarePaginator
            ? [
                'totalItems'   => $this->resource->total(),
                'itemsPerPage' => $this->resource->perPage(),
                'currentPage'  => $this->resource->currentPage(),
                'lastPage'     => $this->resource->lastPage(),
                'nextPageUrl'  => $this->resource->nextPageUrl(),
                'prevPageUrl'  => $this->resource->previousPageUrl(),
            ]
            : [];
    }
}
