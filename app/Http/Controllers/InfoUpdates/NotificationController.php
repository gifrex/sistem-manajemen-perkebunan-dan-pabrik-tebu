<?php

namespace App\Http\Controllers\InfoUpdates;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Company;
use App\Repositories\InfoUpdates\NotificationRepository;
use App\Services\InfoUpdates\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class NotificationController extends Controller
{
    protected NotificationRepository $repository;
    protected NotificationService $service;

    public function __construct(
        NotificationRepository $repository,
        NotificationService $service
    ) {
        $this->repository = $repository;
        $this->service = $service;

        View::share([
            'navbar' => 'Info & Updates',
            'routeName' => route('info-updates.notifications.index'),
        ]);
    }

    // ============================================================================
    // USER VIEWS
    // ============================================================================

    public function index()
    {
        $title = 'Notifications';
        $userId = Auth::user()->userid;
        
        // ✅ No N+1: Single query with map
        $notifications = $this->repository->getForUser($userId, 1000, false);
        $notifCount = $notifications->count();
        $unreadCount = $notifications->where('is_read', false)->count();

        return view('info-updates.notifications.index', compact(
            'title',
            'notifications',
            'notifCount',
            'unreadCount'
        ));
    }

    public function getDropdownData()
    {
        try {
            $userId = auth()->id();
            
            // ✅ No N+1: Repository handles everything in single query
            $data = $this->repository->getDropdownData($userId);
            
            return response()->json($data);
            
        } catch (\Exception $e) {
            Log::error('Notification dropdown error: ' . $e->getMessage());
            
            return response()->json([
                'notifications' => [],
                'unread_count' => 0
            ]);
        }
    }

    public function getUnreadCount()
    {
        try {
            $userId = auth()->id();
            
            // ✅ No N+1: Single optimized query
            $unreadCount = $this->repository->getUnreadCount($userId);
            
            return response()->json([
                'unread_count' => $unreadCount
            ]);
            
        } catch (\Exception $e) {
            Log::error('Notification count error: ' . $e->getMessage());
            
            return response()->json([
                'unread_count' => 0
            ]);
        }
    }

    public function markAsRead($id)
    {
        try {
            $userId = Auth::user()->userid;

            // ✅ Service handles business logic + broadcast
            $success = $this->service->markAsRead($id, $userId);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found or already read'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read', [
                'notification_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark as read'
            ], 500);
        }
    }

    public function markAllAsRead()
    {
        try {
            $userId = Auth::user()->userid;
            
            // ✅ Service handles bulk operation + broadcast
            $count = $this->service->markAllAsRead($userId);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
                'count' => $count
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to mark all notifications as read', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all as read'
            ], 500);
        }
    }

    // ============================================================================
    // ADMIN MANAGEMENT
    // ============================================================================

    public function adminIndex()
    {
        if (!hasPermission('infoupdates.notification.view')) {
            abort(403, 'Unauthorized action.');
        }

        $title = 'Notification Management';
        $search = request('search');
        $perPage = request('perPage', 15);

        // ✅ No N+1: Eager load supportTicket relationship
        $result = $this->repository->getAllForAdmin([
            'search' => $search
        ], $perPage);

        // ✅ Single query for companies
        $companies = Company::orderBy('name')->get();

        return view('info-updates.notifications.admin-index', compact(
            'title', 
            'result', 
            'companies', 
            'perPage'
        ));
    }

    public function create()
    {
        if (!hasPermission('infoupdates.notification.create')) {
            abort(403, 'Unauthorized action.');
        }

        $title = 'Create Notification';
        
        // ✅ Single query each
        $companies = Company::orderBy('name')->get();
        $jabatan = \App\Models\MasterData\Jabatan::orderBy('namajabatan')->get();

        return view('info-updates.notifications.create', compact(
            'title', 
            'companies', 
            'jabatan'
        ));
    }

    public function store(Request $request)
    {
        if (!hasPermission('infoupdates.notification.create')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'companycodes' => 'required|array',
            'companycodes.*' => 'exists:company,companycode',
            'target_jabatan' => 'nullable|array',
            'target_jabatan.*' => 'integer|exists:jabatan,idjabatan',
            'title' => 'required|string|max:200',
            'body' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'action_url' => 'nullable|url'
        ]);

        try {
            $notificationData = [
                'companycodes' => $validated['companycodes'],
                'title' => $validated['title'],
                'body' => $validated['body'],
                'priority' => $validated['priority'],
                'action_url' => $validated['action_url'] ?? null
            ];

            if (!empty($validated['target_jabatan'])) {
                $filteredJabatan = array_filter($validated['target_jabatan']);
                $notificationData['target_jabatan'] = implode(',', $filteredJabatan);
            }

            // ✅ Service handles creation + broadcast
            $this->service->createManualNotification($notificationData);

            return redirect()->route('info-updates.notifications.admin.index')
                           ->with('success', 'Notification berhasil dibuat dan dikirim');

        } catch (\Exception $e) {
            Log::error('Failed to create notification', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Gagal membuat notification: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        if (!hasPermission('infoupdates.notification.edit')) {
            abort(403, 'Unauthorized action.');
        }

        // ✅ Single query
        $notification = $this->repository->findById($id);
        
        if (!$notification) {
            return redirect()->route('info-updates.notifications.admin.index')
                           ->with('error', 'Notification tidak ditemukan');
        }
        
        if ($notification->notification_type !== 'manual') {
            return redirect()->route('info-updates.notifications.admin.index')
                           ->with('error', 'Hanya manual notification yang bisa diedit');
        }

        $title = 'Edit Notification';
        
        // ✅ Single query each
        $companies = Company::orderBy('name')->get();
        $jabatan = \App\Models\MasterData\Jabatan::orderBy('namajabatan')->get();

        return view('info-updates.notifications.edit', compact(
            'title', 
            'notification', 
            'companies', 
            'jabatan'
        ));
    }

    public function update(Request $request, $id)
    {
        if (!hasPermission('infoupdates.notification.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'companycodes' => 'required|array',
            'companycodes.*' => 'exists:company,companycode',
            'target_jabatan' => 'nullable|array',
            'target_jabatan.*' => 'integer|exists:jabatan,idjabatan',
            'title' => 'required|string|max:200',
            'body' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'action_url' => 'nullable|url'
        ]);

        try {
            $updateData = [
                'companycodes' => $validated['companycodes'],
                'title' => $validated['title'],
                'body' => $validated['body'],
                'priority' => $validated['priority'],
                'action_url' => $validated['action_url'] ?? null,
            ];

            if (!empty($validated['target_jabatan'])) {
                $updateData['target_jabatan'] = $validated['target_jabatan'];
            } else {
                $updateData['target_jabatan'] = null;
            }

            // ✅ Service handles update logic
            $this->service->updateManualNotification($id, $updateData);

            return redirect()->route('info-updates.notifications.admin.index')
                           ->with('success', 'Notification berhasil diperbarui');

        } catch (\Exception $e) {
            Log::error('Failed to update notification', [
                'notification_id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Gagal memperbarui notification: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        if (!hasPermission('infoupdates.notification.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            // ✅ Service handles soft delete
            $this->service->deleteNotification($id);

            return redirect()->route('info-updates.notifications.admin.index')
                           ->with('success', 'Notification berhasil dihapus');

        } catch (\Exception $e) {
            Log::error('Failed to delete notification', [
                'notification_id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('info-updates.notifications.admin.index')
                           ->with('error', 'Gagal menghapus notification: ' . $e->getMessage());
        }
    }

    // ============================================================================
    // SYSTEM NOTIFICATION HELPER (untuk dipanggil dari controller lain)
    // ============================================================================

    public static function notifyNewSupportTicket($ticket)
    {
        try {
            // ✅ Use service via container
            $service = app(NotificationService::class);
            $service->createForSupportTicket($ticket);
            
            Log::info('Support ticket notification created', [
                'ticket_id' => $ticket->ticket_id,
                'ticket_number' => $ticket->ticket_number
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create support ticket notification', [
                'ticket_id' => $ticket->ticket_id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }
}