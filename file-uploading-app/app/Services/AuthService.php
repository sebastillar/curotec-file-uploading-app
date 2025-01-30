<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\UserRepository;
use App\Services\Interfaces\AuthServiceInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function register(array $data): array
    {
        try {
            $user = $this->userRepository->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => $user,
                'token' => $token
            ];
        } catch (\Exception $e) {
            \Log::error('Registration error:', [
                'message' => $e->getMessage(),
                'email' => $data['email']
            ]);
            throw $e;
        }
    }

    public function login(array $credentials): array
    {
        try {
            $user = $this->userRepository->findByEmail($credentials['email']);

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => $user,
                'token' => $token
            ];
        } catch (\Exception $e) {
            \Log::error('Login error:', [
                'message' => $e->getMessage(),
                'email' => $credentials['email']
            ]);
            throw $e;
        }
    }

    public function logout(): void
    {
        try {
            auth()->user()->tokens()->delete();
        } catch (\Exception $e) {
            \Log::error('Logout error:', [
                'message' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            throw $e;
        }
    }

    public function getCurrentUser()
    {
        return auth()->user();
    }
}
