<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class AuthController extends Controller
{
    /**
     * Register a new user and return an authentication token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|min:8',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password), 
            ]);

            // Generate authentication token
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json(['token' => $token, 'message' => 'User registered successfully'], 201);
        } catch (Exception $e) {
            Log::error("Registration failed: " . $e->getMessage());
            return response()->json(['message' => 'User registration failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Authenticate a user and return an authentication token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            if (!Auth::attempt($request->only('email', 'password'))) {
                Log::warning("Failed login attempt for: {$request->email}");
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            $user = Auth::user();
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json(['token' => $token, 'message' => 'Login successful']);
        } catch (Exception $e) {
            Log::error("Login failed: " . $e->getMessage());
            return response()->json(['message' => 'Login failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Logout the authenticated user by revoking their token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            $user->currentAccessToken()->delete();
            return response()->json(['message' => 'Logged out successfully']);
        } catch (Exception $e) {
            Log::error("Logout failed: " . $e->getMessage());
            return response()->json(['message' => 'Logout failed', 'error' => $e->getMessage()], 500);
        }
    }
}
