<?php
namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test authenticated user can create a post.
     */
    public function test_authenticated_user_can_create_post(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/posts', [
            'title'       => 'Test Post',
            'slug'        => 'test-post',
            'content'     => 'This is a test post.',
            'excerpt'     => 'Testing',
            'status'      => 'published',
            'is_featured' => false,
        ]);

        // Assert correct response structure
        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'title',
                    'slug',
                    'content',
                    'image',
                    'excerpt',
                    'status',
                    'is_featured',
                    'published_at',
                    'author' => [
                        'name',
                        'email',
                    ],
                    'created_at',
                    'updated_at',
                ],
            ]);

        // Assert specific values
        $response->assertJson([
            'status'  => 201,
            'message' => 'Successfully created post',
            'data'    => [
                'title'       => 'Test Post',
                'slug'        => 'test-post',
                'content'     => 'This is a test post.',
                'excerpt'     => 'Testing',
                'status'      => 'published',
                'is_featured' => false,
                'author'      => [
                    'name'  => $user->name,
                    'email' => $user->email,
                ],
            ],
        ]);

        // Ensure the post exists in the database
        $this->assertDatabaseHas('posts', [
            'title'       => 'Test Post',
            'slug'        => 'test-post',
            'content'     => 'This is a test post.',
            'excerpt'     => 'Testing',
            'status'      => 'published',
            'is_featured' => false,
        ]);
    }

    /**
     * Test unauthenticated user cannot create a post.
     */
    public function test_unauthenticated_user_cannot_create_post(): void
    {
        $response = $this->postJson('/api/posts', [
            'title'   => 'Unauthorized Post',
            'content' => 'Should fail',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test user can retrieve all posts.
     */
    public function test_user_can_get_posts(): void
    {
        Post::factory()->count(3)->create();

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data'       => [
                    '*' => [ // Array of posts
                        'id',
                        'title',
                        'slug',
                        'content',
                        'image',
                        'excerpt',
                        'status',
                        'is_featured',
                        'published_at',
                        'author' => [
                            'name',
                            'email',
                        ],
                        'created_at',
                        'updated_at',
                    ],
                ],
                'pagination' => [ // Pagination details
                    'totalItems',
                    'itemsPerPage',
                    'currentPage',
                    'lastPage',
                    'nextPageUrl',
                    'prevPageUrl',
                ],
            ]);

        // Ensure database contains 3 posts
        $this->assertDatabaseCount('posts', 3);
    }

    /**
     * Test authenticated user can delete their own post.
     */
    public function test_authenticated_user_can_delete_own_post(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status'  => 200,
                'message' => 'Successfully deleted post',
            ]);

        // Check if deleted_at is set instead of checking missing row, since we're using SoftDeletes
        $this->assertDatabaseHas('posts', [
            'id'         => $post->id,
            'deleted_at' => now(),
        ]);
    }

    public function test_authenticated_user_can_create_published_post(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/posts', [
            'title'       => 'Published Post',
            'slug'        => 'published-post',
            'content'     => 'This is a test post.',
            'excerpt'     => 'Testing',
            'status'      => 'published',
            'is_featured' => false,
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'status' => 'published',
            ])
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'title',
                    'slug',
                    'content',
                    'image',
                    'excerpt',
                    'status',
                    'is_featured',
                    'published_at',
                    'author' => [
                        'name',
                        'email',
                    ],
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('posts', [
            'title'        => 'Published Post',
            'status'       => 'published',
            'published_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }
}
