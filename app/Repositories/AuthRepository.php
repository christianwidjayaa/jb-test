<?php

namespace App\Repositories;

use App\Jobs\SendWelcomeEmail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Exception;

class AuthRepository extends BaseResourceRepository
{
    /**
     * AuthRepository constructor.
     *
     * @param User $user The User model instance.
     */
    public function __construct(User $user)
    {
        $this->model = $user;
    }

    /**
     * Register a new user and return the auth token.
     *
     * @param array $data The registration data.
     * @return array The user and authentication token.
     */
    public function register(array $data): array
    {
        try {
            // Wrap in a transaction to ensure atomicity
            $user = DB::transaction(function () use ($data) {
                return $this->save([
                    'name'     => $data['name'],
                    'email'    => $data['email'],
                    'password' => bcrypt($data['password']),
                ]);
            });

            // Dispatch welcome email asynchronously
            SendWelcomeEmail::dispatch($user->email, $user->name);

            return [
                'user'         => $user,
                'access_token' => $user->createToken('auth_token')->plainTextToken,
            ];
        } catch (Exception $e) {
            Log::error("User registration failed for email: {$data['email']}", [
                'exception' => $e->getMessage(),
            ]);

            throw new Exception("Registration failed. Please try again later.");
        }
    }

    /**
     * Authenticate user and return the authentication token.
     *
     * @param array $credentials The login credentials.
     * @return array The user and authentication token.
     * @throws ValidationException If credentials are invalid.
     */
    public function login(array $credentials): array
    {
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }

        $user = Auth::user();

        return [
            'user'         => $user,
            'access_token' => $user->createToken('auth_token')->plainTextToken,
        ];
    }

    /**
     * Retrieve the authenticated user.
     *
     * @return User The authenticated user.
     */
    public function getUser(): User
    {
        return Auth::user();
    }

    /**
     * Logout user and revoke the current access token.
     */
    public function logout(): void
    {
        Auth::user()->currentAccessToken()->delete();
    }
}
