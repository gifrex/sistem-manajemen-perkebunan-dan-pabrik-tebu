{{-- resources/views/info-updates/notifications/index.blade.php --}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $title }}</x-slot:navbar>

    <div x-data="notificationPage()" x-init="init()" class="mx-auto py-4 bg-white rounded-md shadow-md">
        
        <!-- Header with Stats -->
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">All Notifications</h2>
                        <p class="text-sm text-gray-600">{{ $notifCount }} total notifications</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    @if($unreadCount > 0)
                    <button @click="markAllAsRead()" 
                            class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors">
                        Mark all as read
                    </button>
                    @endif
                    @can('infoupdates.notification.view')
                    <a href="{{ route('info-updates.notifications.admin.index') }}" 
                       class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 transition-colors">
                        Manage Notifications
                    </a>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Notification List -->
        <div class="px-4 py-4">
            @if($notifications->isEmpty())
                <div class="py-12 text-center">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No notifications yet</h3>
                    <p class="text-sm text-gray-500">You're all caught up!</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($notifications as $notif)
                    <div @click="handleNotificationClick({{ $notif->notification_id }}, '{{ $notif->action_url }}')"
                         class="border rounded-lg p-4 transition-all duration-150 cursor-pointer {{ $notif->is_read ? 'bg-white hover:bg-gray-50 border-gray-200' : 'bg-blue-50 hover:bg-blue-100 border-blue-200' }}">
                        <div class="flex items-start space-x-4">
                            
                            <!-- Icon -->
                            <div class="flex-shrink-0">
                                @if($notif->icon === 'ticket')
                                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                    </svg>
                                </div>
                                @else
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    </svg>
                                </div>
                                @endif
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2">
                                            <h3 class="text-sm font-semibold text-gray-900">{{ $notif->title }}</h3>
                                            @if(!$notif->is_read)
                                            <span class="w-2 h-2 bg-blue-600 rounded-full"></span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-700 mt-1">{{ $notif->body }}</p>
                                        
                                        <!-- Meta Info -->
                                        <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                                            <span class="flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $notif->createdat->diffForHumans() }}
                                            </span>
                                            
                                            @if($notif->priority === 'high')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                High Priority
                                            </span>
                                            @elseif($notif->priority === 'medium')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Medium
                                            </span>
                                            @endif

                                            <span class="text-gray-400">•</span>
                                            <span>{{ $notif->companycode }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Arrow -->
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <script>
    function notificationPage() {
        return {
            init() {
                // Any initialization if needed
            },

            async handleNotificationClick(notificationId, actionUrl) {
                // Mark as read
                try {
                    await fetch(`/info-updates/notifications/${notificationId}/read`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                } catch (error) {
                    console.error('Failed to mark notification as read:', error);
                }

                // Redirect if action_url exists
                if (actionUrl) {
                    window.location.href = actionUrl;
                } else {
                    // Reload to update read status
                    window.location.reload();
                }
            },

            async markAllAsRead() {
                if (!confirm('Mark all notifications as read?')) {
                    return;
                }

                try {
                    const response = await fetch('/info-updates/notifications/mark-all-read', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        window.location.reload();
                    }
                } catch (error) {
                    console.error('Failed to mark all as read:', error);
                    alert('Failed to mark all notifications as read');
                }
            }
        }
    }
    </script>
</x-layout>