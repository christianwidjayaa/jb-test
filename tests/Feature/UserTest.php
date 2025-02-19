<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can register.
     *
     * @return void
     */
    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name'                  => 'John Doe',
            'email'                 => 'johndoe@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => [
                        'name',
                        'email',
                    ],
                    'access_token',
                    'token_type',
                ],
            ])
            ->assertJsonPath('data.user.name', 'John Doe')
            ->assertJsonPath('data.user.email', 'johndoe@example.com')
            ->assertJsonPath('data.token_type', 'Bearer');

        // Ensure the user is stored in the database
        $this->assertDatabaseHas('users', [
            'email' => 'johndoe@example.com',
        ]);
    }

    /**
     * Test user registration with invalid data.
     */
    public function test_user_cannot_register_with_invalid_data(): void
    {
        $response = $this->postJson('/api/register', [
            'name'                  => '',
            'email'                 => 'invalid-email',
            'password'              => 'short',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_login(): void
    {
        // Create a user in the database
        $user = \App\Models\User::factory()->create([
            'email'    => 'johndoe@example.com',
            'password' => bcrypt('password'), // Ensure password is hashed
        ]);

        // Attempt login
        $response = $this->postJson('/api/login', [
            'email'    => 'johndoe@example.com',
            'password' => 'password',
        ]);

        // Assert correct response structure
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'email_verified_at',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                    ],
                    'access_token',
                    'token_type',
                ],
            ])
            ->assertJsonPath('data.user.email', 'johndoe@example.com')
            ->assertJsonPath('data.token_type', 'Bearer');

        // Ensure the user is stored in the database
        $this->assertDatabaseHas('users', [
            'email' => 'johndoe@example.com',
        ]);
    }

    /**
     * Test user login with incorrect credentials.
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email'    => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test authenticated user can access profile.
     */
    public function test_authenticated_user_can_get_their_profile(): void
    {
        // Create a user
        $user = \App\Models\User::factory()->create([
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => bcrypt('password'),
        ]);

        // Authenticate the user
        \Laravel\Sanctum\Sanctum::actingAs($user);

        // Send GET request
        $response = $this->getJson('/api/user');

        // Assert response structure
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'id',
                         'name',
                         'email',
                         'email_verified_at',
                         'created_at',
                         'updated_at',
                     ],
                 ])
                 ->assertJsonPath('status', 200)
                 ->assertJsonPath('message', 'User retrieved successfully')
                 ->assertJsonPath('data.name', 'John Doe')
                 ->assertJsonPath('data.email', 'johndoe@example.com');
    }

    /**
     * Test user can logout and revoke token.
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200);
    }
}
