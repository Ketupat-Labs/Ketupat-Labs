<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pemberitahuan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Header with filters and actions -->
                    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                Semua Pemberitahuan
                            </h3>
                            @if($unreadCount > 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $unreadCount }} Belum dibaca
                                </span>
                            @endif
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <button onclick="clearAllNotifications()" class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <i class="fas fa-trash mr-2"></i>
                                Padam Semua
                            </button>
                        </div>
                    </div>


                    <!-- Notifications List -->
                    <div class="space-y-2">
                        @forelse($notifications as $notification)
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors {{ !$notification->is_read ? 'bg-blue-50 border-blue-200' : '' }}">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 cursor-pointer" onclick="handleNotificationClick({{ $notification->id }}, '{{ $notification->type }}', '{{ $notification->related_type ?? '' }}', {{ $notification->related_id ?? 'null' }})">
                                        <div class="flex items-start gap-3">
                                            @if(!$notification->is_read)
                                                <div class="mt-1 h-2 w-2 bg-blue-600 rounded-full flex-shrink-0"></div>
                                            @endif
                                            <div class="flex-1">
                                                <h4 class="text-sm font-semibold text-gray-900">
                                                    {{ $notification->title }}
                                                </h4>
                                                <p class="text-sm text-gray-600 mt-1">
                                                    {{ $notification->message }}
                                                </p>
                                                <div class="flex items-center gap-4 mt-2">
                                                    <span class="text-xs text-gray-400">
                                                        {{ $notification->created_at->diffForHumans() }}
                                                    </span>
                                                    <span class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded">
                                                        {{ ucfirst(str_replace('_', ' ', $notification->type)) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 ml-4">
                                        @if(!$notification->is_read)
                                            <button onclick="markAsRead({{ $notification->id }})" class="p-2 text-gray-400 hover:text-blue-600" title="Tandakan sebagai dibaca">
                                                <i class="fas fa-check text-sm"></i>
                                            </button>
                                        @endif
                                        <button onclick="deleteNotification({{ $notification->id }})" class="p-2 text-gray-400 hover:text-red-600" title="Padam">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <i class="fas fa-bell-slash text-4xl text-gray-300 mb-4"></i>
                                <p class="text-gray-500 text-lg">Tiada pemberitahuan</p>
                                <p class="text-gray-400 text-sm mt-2">Anda tidak mempunyai sebarang pemberitahuan pada masa ini.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    @if($notifications->hasPages())
                        <div class="mt-6">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        async function handleNotificationClick(notificationId, type, relatedType, relatedId) {
            try {
                // Mark as read
                await markAsRead(notificationId);
                
                // Get redirect URL from server
                const response = await fetch(`/api/notifications/${notificationId}/redirect`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include'
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.status === 200 && data.data.redirect_url) {
                        window.location.href = data.data.redirect_url;
                    }
                }
            } catch (error) {
                console.error('Error handling notification click:', error);
            }
        }

        async function markAsRead(notificationId) {
            try {
                const response = await fetch(`/api/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include'
                });
                
                if (response.ok) {
                    // Reload page to update UI
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        }

        async function deleteNotification(notificationId) {
            if (!confirm('Adakah anda pasti mahu memadam pemberitahuan ini?')) {
                return;
            }
            
            try {
                const response = await fetch(`/api/notifications/${notificationId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include'
                });
                
                if (response.ok) {
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error deleting notification:', error);
            }
        }

        async function clearAllNotifications() {
            if (!confirm('Adakah anda pasti mahu memadam SEMUA pemberitahuan? Tindakan ini tidak boleh dibatalkan.')) {
                return;
            }
            
            try {
                const response = await fetch('/api/notifications', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include'
                });
                
                if (response.ok) {
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error clearing all notifications:', error);
            }
        }
    </script>
</x-app-layout>

