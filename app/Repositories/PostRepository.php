<?php
namespace App\Repositories;

use App\Http\Resources\PostCollection;
use App\Models\Post;
use App\Services\FileService;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class PostRepository extends BaseResourceRepository
{
    /**
     * Initialize a new instance of the PostRepository class.
     */
    public function __construct(FileService $fileService)
    {
        parent::__construct(new Post(), $fileService);
    }

    /**
     * Return a paginated list of posts that match the given search query
     * and sort order.
     *
     * @param int $size The number of items to return per page.
     * @param array $queryParams An array of query parameters which may
     * include the search query, sort field, and sort order.
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function paginatedList($size, array $queryParams): PostCollection
    {
        $searchParam = $this->getSearchQuery($queryParams);
        $sort = $this->getSortField($queryParams);
        $order = $this->getOrderBy($queryParams);
        $posts = $this->model
            ->search($searchParam)
            ->ofOrder($sort, $order)
            ->paginate($size);

        return new PostCollection($posts);
    }

    /**
     * Handle additional logic before saving a post.
     */
    public function save(array $input): Post | JsonResource
    {
        $input['user_id'] = Auth::id();
        empty($input['featured']) ? $input['featured'] = 0 : $input['featured']           = 1;
        $input['status'] == 'published' ? $input['published_at'] = now() : $input['published_at'] = null;

        return parent::save($input);
    }

    /**
     * Handle additional logic before updating a post.
     */
    public function update(array $input, int $id): Post | JsonResource
    {
        if (! empty($input['status']) && $input['status'] === 'published') {
            $input['published_at'] = now();
        }

        return parent::update($input, $id);
    }
}
