{{-- resources/views/info-updates/notifications/admin-index.blade.php --}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>Info & Updates</x-slot:navbar>
    <x-slot:nav>Notification Management</x-slot:nav>

    <div class="mx-auto py-4">
        <!-- Header with Actions -->
        <div class="bg-white rounded-t-md shadow-md px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Notification Management</h2>
                    <p class="text-sm text-gray-600 mt-1">Manage and create notifications for users</p>
                </div>
                @can('infoupdates.notification.create')
                <a href="{{ route('info-updates.notifications.admin.create') }}" 
                   class="flex items-center space-x-2 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Create Notification</span>
                </a>
                @endcan
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow-md px-6 py-4 border-b border-gray-200">
            <form method="GET" action="{{ route('info-updates.notifications.admin.index') }}" class="flex items-end space-x-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search by title, body, or company..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Per Page</label>
                    <select name="perPage" 
                            class="px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <option value="15" {{ request('perPage', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="30" {{ request('perPage') == 30 ? 'selected' : '' }}>30</option>
                        <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('perPage') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                <button type="submit" 
                        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors">
                    Filter
                </button>
                @if(request('search'))
                <a href="{{ route('info-updates.notifications.admin.index') }}" 
                   class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
                    Clear
                </a>
                @endif
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-b-md shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($result as $notif)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $notif->notification_type === 'manual' ? 'bg-blue-100 text-blue-800' : 
                                       ($notif->notification_type === 'support_ticket' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ ucfirst($notif->notification_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ Str::limit($notif->title, 50) }}</div>
                                <div class="text-sm text-gray-500">{{ Str::limit($notif->body, 60) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ Str::limit($notif->companycode, 20) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $notif->priority === 'high' ? 'bg-red-100 text-red-800' : 
                                       ($notif->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                    {{ ucfirst($notif->priority) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $notif->status === 'active' ? 'bg-green-100 text-green-800' : 
                                       ($notif->status === 'archived' ? 'bg-gray-100 text-gray-800' : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($notif->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $notif->createdat->format('d M Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-3">
                                    @if($notif->notification_type === 'manual')
                                        @can('infoupdates.notification.edit')
                                        <a href="{{ route('info-updates.notifications.admin.edit', $notif->notification_id) }}" 
                                           class="text-blue-600 hover:text-blue-900">
                                            Edit
                                        </a>
                                        @endcan
                                        @can('infoupdates.notification.delete')
                                        <form action="{{ route('info-updates.notifications.admin.destroy', $notif->notification_id) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Are you sure you want to delete this notification?');"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                Delete
                                            </button>
                                        </form>
                                        @endcan
                                    @else
                                        <span class="text-gray-400">System Generated</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <p class="text-lg font-medium">No notifications found</p>
                                <p class="text-sm mt-1">{{ request('search') ? 'Try adjusting your search criteria' : 'Create your first notification to get started' }}</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($result->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $result->links() }}
            </div>
            @endif
        </div>
    </div>
</x-layout>