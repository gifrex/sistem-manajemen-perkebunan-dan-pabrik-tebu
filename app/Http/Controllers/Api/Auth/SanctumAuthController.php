<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class SanctumAuthController extends Controller
{
    /**
     * Login with Sanctum token
     * POST /api/auth/login
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'userid' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('userid', $validated['userid'])->first();

        if (!$user) {
            return response()->json([
                'status' => 0,
                'description' => 'Username or password is incorrect'
            ], 404);
        }

        if (!password_verify($validated['password'], $user->password)) {
            return response()->json([
                'status' => 0,
                'description' => 'Username or password is incorrect'
            ], 401);
        }

        if (!$user->isactive) {
            return response()->json([
                'status' => 0,
                'description' => 'User is inactive'
            ], 403);
        }

        // Generate token
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'status' => 1,
            'description' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'userid' => $user->userid,
                'name' => $user->name,
                'companycode' => $user->companycode,
                'idjabatan' => $user->idjabatan
            ]
        ], 200);
    }

    /**
     * Logout (revoke current token)
     * POST /api/auth/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 1,
            'description' => 'Logged out successfully'
        ], 200);
    }

    /**
     * Get authenticated user info
     * GET /api/auth/me
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'status' => 1,
            'data' => [
                'userid' => $user->userid,
                'name' => $user->name,
                'companycode' => $user->companycode,
                'idjabatan' => $user->idjabatan,
                'divisionid' => $user->divisionid
            ]
        ], 200);
    }
}