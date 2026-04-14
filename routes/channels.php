<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

// ✅ Get base path from request
$basePath = request()->getBasePath(); // Will be '/tebu/public' in local, '' in production

// Register broadcast routes with dynamic prefix
Broadcast::routes([
    'middleware' => ['web', 'auth'],
    'prefix' => ltrim($basePath, '/'), // Remove leading slash
]);

// Private channel per user untuk notifications
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->userid === (int) $userId;
});

// Public channel untuk chat
Broadcast::channel('chat', function ($user) {
    return ['id' => $user->userid, 'name' => $user->name];
});