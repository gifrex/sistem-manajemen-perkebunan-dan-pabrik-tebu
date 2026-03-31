<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiClient;
use Illuminate\Http\Request;

class ApiClientTokenController extends Controller
{
    public function createClient(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'token_name'  => 'required|string|unique:apiclients,token_name',
            'description' => 'nullable|string',
        ]);

        $client = ApiClient::create([
            'name'        => $request->name,
            'token_name'  => $request->token_name,
            'description' => $request->description,
            'is_active'   => true,
        ]);

        $token = $client->createToken($request->token_name)->plainTextToken;

        return response()->json([
            'status'  => 'success',
            'message' => 'Client berhasil dibuat. Simpan token ini, tidak bisa dilihat lagi!',
            'data'    => [
                'client_id'  => $client->id,
                'name'       => $client->name,
                'token_name' => $client->token_name,
                'token'      => $token,
            ]
        ], 201);
    }

    public function listClients()
    {
        $clients = ApiClient::with('tokens:id,tokenable_id,name,last_used_at,created_at')
            ->get(['id', 'name', 'token_name', 'is_active', 'description', 'created_at']);

        return response()->json([
            'status' => 'success',
            'data'   => $clients
        ]);
    }

    public function deactivateClient($id)
    {
        $client = ApiClient::findOrFail($id);
        $client->update(['is_active' => false]);

        return response()->json([
            'status'  => 'success',
            'message' => "Client '{$client->name}' berhasil dinonaktifkan."
        ]);
    }

    public function revokeTokens($id)
    {
        $client = ApiClient::findOrFail($id);
        $client->tokens()->delete();
        $client->update(['is_active' => false]);

        return response()->json([
            'status'  => 'success',
            'message' => "Semua token client '{$client->name}' berhasil dihapus."
        ]);
    }
}