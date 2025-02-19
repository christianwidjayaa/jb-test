<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseResourceApiController;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Repositories\AuthRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseResourceApiController
{
    protected AuthRepository $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        parent::__construct($authRepository);
        $this->authRepository = $authRepository;
    }

    /**
     * Register a new user and return a response.
     *
     * @param RegisterRequest $request The validated registration request.
     * @return JsonResponse The JSON response with user data or an error message.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $data = $this->authRepository->register($request->all());
            return successResponse('User registered successfully!', [
                'user' => $data['user'],
                'access_token' => $data['access_token'],
                'token_type' => 'Bearer',
            ], 201);
        } catch (ValidationException $e) {
            return validationErrorResponse($e->getMessage(), $e->errors());
        } catch (\Exception $e) {
            return internalErrorResponse('Registration failed. Please try again later.');
        }
    }

    /**
     * Authenticate user and return the token.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $data = $this->authRepository->login($request->only('email', 'password'));

            return successResponse('Login successful', [
                'user' => $data['user'],
                'access_token' => $data['access_token'],
                'token_type' => 'Bearer',
            ]);
        } catch (ValidationException $e) {
            return unauthorizedResponse($e->getMessage());
        }
    }

    /**
     * Retrieve the authenticated user.
     *
     * @return JsonResponse A JSON response containing the authenticated user.
     */

    public function user(): JsonResponse
    {
        return successResponse('User retrieved successfully', $this->authRepository->getUser());
    }

    /**
     * Logout the authenticated user and invalidate the current access token.
     *
     * @return JsonResponse A JSON response indicating the logout was successful.
     */
    public function logout(): JsonResponse
    {
        $this->authRepository->logout();

        return successResponse('Logout successful');
    }
}
