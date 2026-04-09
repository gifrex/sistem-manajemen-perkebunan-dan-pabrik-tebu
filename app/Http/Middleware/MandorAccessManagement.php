<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MandorAccessManagement
{
    public function handle(Request $request, Closure $next)
    {
        // Skip middleware untuk route login/logout
        $currentPath = $request->path();
        if (in_array($currentPath, ['login', 'logout']) || !auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();
        
        // Jika user adalah mandor (idjabatan = 999)
        if ($user->idjabatan == 999) {
            $allowedPaths = [
                'mandor',
                'logout', 
                'api/mandor', // API routes
                'chat/send',
                'chat/messages',
                'notifications/unread-count',
                'notifications/read',
                'set-session',
            ];
            
            $isAllowed = false;
            foreach ($allowedPaths as $allowedPath) {
                if (str_starts_with($currentPath, $allowedPath)) {
                    $isAllowed = true;
                    break;
                }
            }
            
            if (!$isAllowed) {
                return redirect()->route('mandor.index')
                    ->with('success', 'Selamat datang, ' . $user->name);
            }
        }
        
        // Jika user adalah absen approver (idjabatan = 998)
        else if ($user->idjabatan == 998) {
            $allowedPaths = [
                'approver',
                'logout',
                'api/approver', // API routes
                'chat/send',
                'chat/messages',
                'notifications/unread-count',
                'notifications/read',
                'set-session',
            ];
            
            $isAllowed = false;
            foreach ($allowedPaths as $allowedPath) {
                if (str_starts_with($currentPath, $allowedPath)) {
                    $isAllowed = true;
                    break;
                }
            }
            
            if (!$isAllowed) {
                return redirect()->route('approver.index')
                    ->with('success', 'Selamat datang, ' . $user->name . ' (Approver)');
            }
        }

        // Jika user biasa mencoba akses halaman khusus
        else if (str_starts_with($currentPath, 'mandor')) {
            return redirect()->route('home')
                ->with('error', 'Akses ditolak. Halaman khusus untuk Mandor.');
        }
        else if (str_starts_with($currentPath, 'approver')) {
            return redirect()->route('home')
                ->with('error', 'Akses ditolak. Halaman khusus untuk Approver.');
        }

        return $next($request);
    }
}