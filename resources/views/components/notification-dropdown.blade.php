{{-- resources/views/components/notification-dropdown.blade.php --}}
<div x-data="notificationDropdown()" @click.away="open = false" class="relative">
    <button @click="toggleDropdown()" 
       class="relative p-2 rounded-full text-gray-500 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 transition-colors">
        <span class="sr-only">View notifications</span>
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        <!-- Unread badge -->
        <span x-show="unreadCount > 0" 
              x-text="unreadCount > 99 ? '99+' : unreadCount"
              class="absolute -top-1 -right-1 min-w-[18px] h-[18px] bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-1 animate-pulse"></span>
    </button>

    <!-- Dropdown Menu -->
    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 z-50 mt-2 w-80 sm:w-96 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
        
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between bg-gray-50 rounded-t-lg">
            <div class="flex items-center space-x-2">
                <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                <span x-show="unreadCount > 0" 
                      class="px-2 py-0.5 bg-red-100 text-red-800 text-xs font-medium rounded-full"
                      x-text="unreadCount + ' unread'"></span>
            </div>
            <button @click="markAllAsRead()" 
                    x-show="unreadCount > 0"
                    class="text-xs text-blue-600 hover:text-blue-800 hover:underline">
                Mark all read
            </button>
        </div>

        <!-- Notification List -->
        <div class="max-h-96 overflow-y-auto">
            <template x-if="loading">
                <div class="py-8 text-center">
                    <svg class="animate-spin h-6 w-6 text-gray-400 mx-auto" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-sm text-gray-500 mt-2">Loading...</p>
                </div>
            </template>

            <template x-if="!loading && notifications.length === 0">
                <div class="py-8 text-center">
                    <svg class="h-12 w-12 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <p class="text-sm text-gray-500 mt-2">No notifications</p>
                </div>
            </template>

            <template x-if="!loading && notifications.length > 0">
                <div>
                    <template x-for="notif in notifications" :key="notif.notification_id">
                        <div @click="handleNotificationClick(notif)"
                             class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 transition-colors"
                             :class="{ 'bg-blue-50': !notif.is_read }">
                            <div class="flex items-start space-x-3">
                                <!-- Icon -->
                                <div class="flex-shrink-0">
                                    <template x-if="notif.icon === 'ticket'">
                                        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                            </svg>
                                        </div>
                                    </template>
                                    <template x-if="notif.icon === 'bell'">
                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                            </svg>
                                        </div>
                                    </template>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between">
                                        <p class="text-sm font-medium text-gray-900 truncate" x-text="notif.title"></p>
                                        <span x-show="!notif.is_read" class="ml-2 w-2 h-2 bg-blue-600 rounded-full flex-shrink-0"></span>
                                    </div>
                                    <p class="text-xs text-gray-600 mt-1 line-clamp-2" x-text="notif.body"></p>
                                    <p class="text-xs text-gray-400 mt-1" x-text="formatDate(notif.createdat)"></p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <!-- Footer -->
        <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 rounded-b-lg">
            <a href="{{ route('info-updates.notifications.index') }}" 
               class="text-sm text-blue-600 hover:text-blue-800 hover:underline font-medium flex items-center justify-center">
                View all notifications
                <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
function notificationDropdown() {
    return {
        open: false,
        loading: false,
        notifications: [],
        unreadCount: 0,
        echoChannel: null,

        init() {
            this.loadNotifications();
            
            // ✅ SETUP WEBSOCKET LISTENER (ganti polling)
            this.listenForNotifications();
        },

        toggleDropdown() {
            this.open = !this.open;
            if (this.open) {
                this.loadNotifications();
            }
        },

        // ✅ WEBSOCKET LISTENER - INI YANG PENTING!
        listenForNotifications() {
            const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');
            
            if (!userId) {
                console.warn('⚠️ User ID not found in meta tag');
                return;
            }

            if (!window.Echo) {
                console.warn('⚠️ Laravel Echo not initialized');
                return;
            }

            try {
                console.log('🔌 Connecting to WebSocket channel: user.' + userId);

                // Listen to private channel
                this.echoChannel = window.Echo.private(`user.${userId}`)
                    .listen('.notification.new', (event) => {
                        console.log('✅ New notification received via WebSocket:', event);
                        
                        // Update unread count
                        this.unreadCount = event.unread_count;
                        
                        // Reload notifications if dropdown is open
                        if (this.open) {
                            this.loadNotifications();
                        }
                        
                        // Optional: Show browser notification
                        this.showBrowserNotification(event);
                        
                        // Optional: Show toast
                        this.showToast('New notification received!');
                    });

                console.log('✅ WebSocket listener initialized successfully');

            } catch (error) {
                console.error('❌ Failed to setup WebSocket listener:', error);
            }
        },

        async loadNotifications() {
            this.loading = true;
            try {
                const response = await fetch('{{ route("info-updates.notifications.dropdown-data") }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                if (!response.ok) throw new Error('Network response was not ok');
                
                const data = await response.json();
                this.notifications = data.notifications;
                this.unreadCount = data.unread_count;
            } catch (error) {
                console.error('Failed to load notifications:', error);
            } finally {
                this.loading = false;
            }
        },

        async handleNotificationClick(notif) {
            if (!notif.is_read) {
                await this.markAsRead(notif.notification_id);
            }

            this.open = false;

            if (notif.action_url) {
                window.location.href = notif.action_url;
            } else {
                window.location.href = '{{ route("info-updates.notifications.index") }}';
            }
        },

        async markAsRead(notificationId) {
            try {
                const response = await fetch(`/info-updates/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const notif = this.notifications.find(n => n.notification_id === notificationId);
                    if (notif) {
                        notif.is_read = true;
                    }
                    // unreadCount akan auto-update via WebSocket broadcast
                }
            } catch (error) {
                console.error('Failed to mark notification as read:', error);
            }
        },

        async markAllAsRead() {
            try {
                const response = await fetch('{{ route("info-updates.notifications.mark-all-read") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    this.notifications.forEach(n => n.is_read = true);
                    // unreadCount akan auto-update via WebSocket broadcast
                }
            } catch (error) {
                console.error('Failed to mark all as read:', error);
            }
        },

        showBrowserNotification(event) {
            if (!('Notification' in window)) return;

            if (Notification.permission === 'granted') {
                new Notification('New Notification', {
                    body: event.notification?.title || 'You have a new notification',
                    icon: '/favicon.ico'
                });
            } else if (Notification.permission !== 'denied') {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        new Notification('New Notification', {
                            body: event.notification?.title || 'You have a new notification',
                            icon: '/favicon.ico'
                        });
                    }
                });
            }
        },

        showToast(message) {
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-4 right-4 bg-blue-500 text-white px-4 py-2 rounded shadow-lg z-50';
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('opacity-0', 'transition-opacity', 'duration-300');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;
            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(diff / 3600000);
            const days = Math.floor(diff / 86400000);

            if (minutes < 1) return 'Just now';
            if (minutes < 60) return `${minutes}m ago`;
            if (hours < 24) return `${hours}h ago`;
            if (days < 7) return `${days}d ago`;
            
            return date.toLocaleDateString('id-ID', { 
                day: 'numeric', 
                month: 'short',
                year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
            });
        },

        destroy() {
            // Cleanup WebSocket connection
            const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');
            if (userId && window.Echo) {
                window.Echo.leave(`user.${userId}`);
                console.log('✅ WebSocket connection cleaned up');
            }
        }
    }
}
</script>
@endpush
@endonce