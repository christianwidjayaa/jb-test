<?php
namespace App\Http\Controllers\Api\Posts;

use App\Http\Controllers\Api\BaseResourceApiController;
use App\Http\Requests\PostRequest;
use App\Repositories\PostRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends BaseResourceApiController
{
    public function __construct(PostRepository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Create a new post resource.
     *
     * @param PostRequest $request
     *
     * @return JsonResponse
     */
    public function store(PostRequest $request)
    {
        return parent::save($request);
    }

    /**
     * Partially update a post by its ID.
     *
     * @param PostRequest $request The incoming request instance.
     * @param int         $id      The ID of the post to update.
     *
     * @return JsonResponse A JSON response containing the updated resource.
     */
    public function update(Request $request, $id)
    {
        return parent::patch($request, $id);
    }
}
