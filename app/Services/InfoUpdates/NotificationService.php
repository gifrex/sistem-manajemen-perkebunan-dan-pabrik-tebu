<?php

namespace App\Services\InfoUpdates;

use App\Models\Notification;
use App\Repositories\InfoUpdates\NotificationRepository;
use App\Events\NotificationSent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected NotificationRepository $repository;

    public function __construct(NotificationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Mark notification as read by user
     */
    public function markAsRead(int $notificationId, string|int $userId): bool
    {
        try {
            $notification = $this->repository->findById($notificationId);
            
            if (!$notification) {
                return false;
            }

            $readBy = is_array($notification->readby) 
                ? $notification->readby 
                : (json_decode($notification->readby, true) ?? []);
            
            // Check if already read
            if (in_array($userId, $readBy)) {
                return false;
            }
            
            // Add user
            $readBy[] = $userId;
            
            $updated = $this->repository->update($notification, [
                'readby' => $readBy,
                'updatedat' => now()
            ]);

            if ($updated) {
                $this->broadcastToUser($userId);
            }

            return $updated;

        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read', [
                'notification_id' => $notificationId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead(string|int $userId): int
    {
        try {
            $user = \App\Models\User::find($userId);
            
            if (!$user) {
                return 0;
            }

            $userCompanies = $user->userCompanies;
            
            if ($userCompanies->isEmpty()) {
                $userCompanyArray = $user->companycode ? explode(',', $user->companycode) : [];
            } else {
                $userCompanyArray = $userCompanies->pluck('companycode')->toArray();
            }
            
            $userCompanyArray = array_filter($userCompanyArray);
            $idjabatan = $user->idjabatan;

            // Get unread notifications
            $notifications = $this->repository->getForUser($userId, 1000, true);
            
            $count = 0;
            foreach ($notifications as $notification) {
                if ($this->markAsRead($notification->notification_id, $userId)) {
                    $count++;
                }
            }

            return $count;

        } catch (\Exception $e) {
            Log::error('Failed to mark all notifications as read', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create manual notification and broadcast
     */
    public function createManualNotification(array $data): bool
    {
        DB::beginTransaction();
        
        try {
            $companyCodes = $data['companycodes'];
            $targetJabatan = $data['target_jabatan'] ?? null;

            foreach ($companyCodes as $companycode) {
                $this->repository->create([
                    'companycode' => $companycode,
                    'target_jabatan' => $targetJabatan,
                    'title' => $data['title'],
                    'body' => $data['body'],
                    'priority' => $data['priority'],
                    'action_url' => $data['action_url'] ?? null,
                    'icon' => 'bell',
                    'notification_type' => 'manual',
                    'status' => 'active',
                    'readby' => [], // ✅ FIX: Array, bukan json_encode([])
                    'inputby' => Auth::user()->userid ?? 'system',
                    'createdat' => now(),
                    'updatedat' => now()
                ]);
            }

            DB::commit();
            $this->broadcastToTargetUsers($companyCodes, $targetJabatan);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create manual notification', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update manual notification
     */
    public function updateManualNotification(int $notificationId, array $data): bool
    {
        try {
            $notification = $this->repository->findById($notificationId);

            if (!$notification) {
                throw new \Exception('Notification not found');
            }

            if ($notification->notification_type !== 'manual') {
                throw new \Exception('Only manual notifications can be edited');
            }

            $updateData = [
                'companycode' => implode(',', $data['companycodes']),
                'title' => $data['title'],
                'body' => $data['body'],
                'priority' => $data['priority'],
                'action_url' => $data['action_url'] ?? null,
                'updatedat' => now()
            ];

            if (!empty($data['target_jabatan'])) {
                $updateData['target_jabatan'] = implode(',', $data['target_jabatan']);
            } else {
                $updateData['target_jabatan'] = null;
            }

            return $this->repository->update($notification, $updateData);

        } catch (\Exception $e) {
            Log::error('Failed to update notification', [
                'notification_id' => $notificationId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Delete notification (soft delete)
     */
    public function deleteNotification(int $notificationId): bool
    {
        try {
            $notification = $this->repository->findById($notificationId);

            if (!$notification) {
                throw new \Exception('Notification not found');
            }

            return $this->repository->softDelete($notification);

        } catch (\Exception $e) {
            Log::error('Failed to delete notification', [
                'notification_id' => $notificationId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create support ticket notification
     */
    public function createForSupportTicket($ticket): Notification
    {
        try {
            $notification = $this->repository->create([
                'companycode' => $ticket->companycode,
                'reference_type' => 'support_ticket',
                'reference_id' => $ticket->ticket_id,
                'title' => 'New Support Ticket',
                'body' => "Ticket #{$ticket->ticket_number}: {$ticket->subject}",
                'priority' => $ticket->priority ?? 'medium',
                'action_url' => route('usermanagement.support-ticket.index'),
                'icon' => 'ticket',
                'notification_type' => 'support_ticket',
                'status' => 'active',
                'readby' => json_encode([]),
                'inputby' => 'system',
                'createdat' => now(),
                'updatedat' => now()
            ]);

            // Broadcast to support team
            $this->broadcastToTargetUsers([$ticket->companycode], '7,10');

            return $notification;

        } catch (\Exception $e) {
            Log::error('Failed to create support ticket notification', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    // ============================================================================
    // BROADCAST METHODS
    // ============================================================================

    /**
     * Broadcast notification to specific user
     */
    public function broadcastToUser(string|int $userId): void
    {
        try {
            $unreadCount = $this->repository->getUnreadCount($userId);

            broadcast(new NotificationSent($userId, $unreadCount));
            
            Log::info('Notification broadcast sent', [
                'user_id' => $userId,
                'unread_count' => $unreadCount
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to broadcast notification', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Broadcast to multiple users based on companies and jabatan
     */
    public function broadcastToTargetUsers(array $companyCodes, $targetJabatan = null): void
    {
        try {
            $users = $this->repository->getTargetUsers($companyCodes, $targetJabatan);

            foreach ($users as $user) {
                $this->broadcastToUser($user->userid);
            }

            Log::info('Broadcast sent to multiple users', [
                'user_count' => $users->count(),
                'companies' => $companyCodes,
                'jabatan' => $targetJabatan
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to broadcast to target users', [
                'error' => $e->getMessage()
            ]);
        }
    }
}