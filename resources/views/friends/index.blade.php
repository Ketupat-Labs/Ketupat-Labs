<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Friends') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Tabs -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px" aria-label="Tabs">
                        <button onclick="showTab('friends')" id="tab-friends" class="tab-button active py-4 px-6 text-sm font-medium border-b-2 border-blue-500 text-blue-600">
                            <i class="fas fa-user-friends mr-2"></i>Friends (<span id="count-friends">{{ $friends->count() }}</span>)
                        </button>
                        <button onclick="showTab('pending')" id="tab-pending" class="tab-button py-4 px-6 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            <i class="fas fa-clock mr-2"></i>Pending Requests (<span id="count-pending">{{ $pendingRequests->count() }}</span>)
                        </button>
                        <button onclick="showTab('sent')" id="tab-sent" class="tab-button py-4 px-6 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            <i class="fas fa-paper-plane mr-2"></i>Sent Requests (<span id="count-sent">{{ $sentRequests->count() }}</span>)
                        </button>
                        <button onclick="showTab('search')" id="tab-search" class="tab-button py-4 px-6 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            <i class="fas fa-search mr-2"></i>Cari Rakan
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <!-- Friends Tab -->
                    <div id="content-friends" class="tab-content">
                        @if($friends->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($friends as $friend)
                                    <div class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition-shadow">
                                        <div class="flex items-center space-x-4">
                                            <div class="flex-shrink-0">
                                                @if($friend->avatar_url)
                                                    <img src="{{ asset($friend->avatar_url) }}" alt="{{ $friend->full_name }}" class="h-12 w-12 rounded-full object-cover">
                                                @else
                                                    <div class="h-12 w-12 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                                                        {{ strtoupper(substr($friend->full_name ?? $friend->username ?? 'U', 0, 1)) }}
                                                    </div>
                                                @endif
                                                @if($friend->is_online)
                                                    <span class="absolute -bottom-1 -right-1 h-4 w-4 bg-green-500 border-2 border-white rounded-full"></span>
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <a href="{{ route('profile.show', $friend->id) }}" class="block">
                                                    <p class="text-sm font-medium text-gray-900 truncate">
                                                        {{ $friend->full_name ?? $friend->username }}
                                                    </p>
                                                    <p class="text-sm text-gray-500 truncate">
                                                        {{ $friend->is_online ? 'Online' : 'Offline' }}
                                                    </p>
                                                </a>
                                            </div>
                                            <div class="flex-shrink-0 flex space-x-2">
                                                <a href="{{ route('messaging.index') }}?user={{ $friend->id }}" 
                                                   class="text-blue-600 hover:text-blue-800" 
                                                   title="Message">
                                                    <i class="fas fa-envelope"></i>
                                                </a>
                                                <button onclick="removeFriend({{ $friend->id }})" 
                                                        class="text-red-600 hover:text-red-800" 
                                                        title="Remove Friend">
                                                    <i class="fas fa-user-minus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12">
                                <i class="fas fa-user-friends text-gray-400 text-6xl mb-4"></i>
                                <p class="text-gray-500 text-lg">No friends yet</p>
                                <p class="text-gray-400 text-sm mt-2">Start adding friends to see them here!</p>
                            </div>
                        @endif
                    </div>

                    <!-- Pending Requests Tab -->
                    <div id="content-pending" class="tab-content hidden">
                        @if($pendingRequests->count() > 0)
                            <div class="space-y-4">
                                @foreach($pendingRequests as $request)
                                    <div class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition-shadow">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-4">
                                                <div class="flex-shrink-0">
                                                    @if($request->avatar_url)
                                                        <img src="{{ asset($request->avatar_url) }}" alt="{{ $request->full_name }}" class="h-12 w-12 rounded-full object-cover">
                                                    @else
                                                        <div class="h-12 w-12 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                                                            {{ strtoupper(substr($request->full_name ?? $request->username ?? 'U', 0, 1)) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <a href="{{ route('profile.show', $request->id) }}" class="block">
                                                        <p class="text-sm font-medium text-gray-900">
                                                            {{ $request->full_name ?? $request->username }}
                                                        </p>
                                                        <p class="text-sm text-gray-500">Wants to be your friend</p>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="flex space-x-2">
                                                <button onclick="acceptFriend({{ $request->id }})" 
                                                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                                                    Accept
                                                </button>
                                                <button onclick="declineFriend({{ $request->id }})" 
                                                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 text-sm">
                                                    Decline
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12">
                                <i class="fas fa-clock text-gray-400 text-6xl mb-4"></i>
                                <p class="text-gray-500 text-lg">No pending requests</p>
                            </div>
                        @endif
                    </div>

                    <!-- Sent Requests Tab -->
                    <div id="content-sent" class="tab-content hidden">
                        @if($sentRequests->count() > 0)
                            <div class="space-y-4">
                                @foreach($sentRequests as $request)
                                    <div class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition-shadow">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-4">
                                                <div class="flex-shrink-0">
                                                    @if($request->avatar_url)
                                                        <img src="{{ asset($request->avatar_url) }}" alt="{{ $request->full_name }}" class="h-12 w-12 rounded-full object-cover">
                                                    @else
                                                        <div class="h-12 w-12 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                                                            {{ strtoupper(substr($request->full_name ?? $request->username ?? 'U', 0, 1)) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <a href="{{ route('profile.show', $request->id) }}" class="block">
                                                        <p class="text-sm font-medium text-gray-900">
                                                            {{ $request->full_name ?? $request->username }}
                                                        </p>
                                                        <p class="text-sm text-gray-500">Request sent</p>
                                                    </a>
                                                </div>
                                            </div>
                                            <button onclick="cancelFriendRequest({{ $request->id }})" 
                                                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 text-sm">
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12">
                                <i class="fas fa-paper-plane text-gray-400 text-6xl mb-4"></i>
                                <p class="text-gray-500 text-lg">No sent requests</p>
                            </div>
                        @endif
                    </div>

                    <!-- Search Tab -->
                    <div id="content-search" class="tab-content hidden">
                        <div class="max-w-xl mx-auto">
                            <div class="relative">
                                <input type="text" id="friendSearchInput" 
                                       placeholder="Cari nama atau username..." 
                                       class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                       onkeyup="handleSearch(event)">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <button onclick="searchFriends()" class="absolute inset-y-0 right-0 px-4 bg-blue-600 text-white rounded-r-lg hover:bg-blue-700">
                                    Cari
                                </button>
                            </div>
                            
                            <div id="searchResults" class="mt-6 space-y-4">
                                <!-- Results will appear here -->
                                <div class="text-center text-gray-500 py-8">
                                    Mula menaip untuk mencari rakan baru...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active', 'border-blue-500', 'text-blue-600');
                button.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            });
            
            // Show selected tab content
            const content = document.getElementById('content-' + tabName);
            if (content) {
                content.classList.remove('hidden');
            }
            
            // Add active class to selected tab
            const tab = document.getElementById('tab-' + tabName);
            if (tab) {
                tab.classList.add('active', 'border-blue-500', 'text-blue-600');
                tab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            }
        }

        async function acceptFriend(friendId) {
            try {
                const response = await fetch('/api/friends/accept', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include',
                    body: JSON.stringify({ friend_id: friendId })
                });

                const data = await response.json();
                
                if (data.status === 200) {
                    alert('Friend request accepted!');
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to accept friend request');
                }
            } catch (error) {
                console.error('Error accepting friend:', error);
                alert('Failed to accept friend request');
            }
        }

        async function declineFriend(friendId) {
            if (!confirm('Are you sure you want to decline this friend request?')) {
                return;
            }

            try {
                const response = await fetch('/api/friends/remove', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include',
                    body: JSON.stringify({ friend_id: friendId })
                });

                const data = await response.json();
                
                if (data.status === 200) {
                    alert('Friend request declined');
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to decline friend request');
                }
            } catch (error) {
                console.error('Error declining friend:', error);
                alert('Failed to decline friend request');
            }
        }

        async function removeFriend(friendId) {
            if (!confirm('Are you sure you want to remove this friend?')) {
                return;
            }

            try {
                const response = await fetch('/api/friends/remove', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include',
                    body: JSON.stringify({ friend_id: friendId })
                });

                const data = await response.json();
                
                if (data.status === 200) {
                    alert('Friend removed');
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to remove friend');
                }
            } catch (error) {
                console.error('Error removing friend:', error);
                alert('Failed to remove friend');
            }
        }

        async function cancelFriendRequest(friendId) {
            if (!confirm('Are you sure you want to cancel this friend request?')) {
                return;
            }

            try {
                const response = await fetch('/api/friends/remove', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include',
                    body: JSON.stringify({ friend_id: friendId })
                });

                const data = await response.json();
                
                if (data.status === 200) {
                    alert('Friend request cancelled');
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to cancel friend request');
                }
            } catch (error) {
                console.error('Error cancelling friend request:', error);
                alert('Failed to cancel friend request');
            }
        }

        let searchTimeout;
        function handleSearch(event) {
            clearTimeout(searchTimeout);
            if (event.key === 'Enter') {
                searchFriends();
            } else {
                searchTimeout = setTimeout(searchFriends, 500);
            }
        }

        async function searchFriends() {
            const query = document.getElementById('friendSearchInput').value;
            const resultsDiv = document.getElementById('searchResults');
            
            if (query.length < 2) {
                resultsDiv.innerHTML = '<div class="text-center text-gray-500 py-8">Mula menaip untuk mencari rakan baru...</div>';
                return;
            }

            resultsDiv.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i></div>';

            try {
                const response = await fetch(`/api/friends/search?query=${encodeURIComponent(query)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();

                if (data.data.length === 0) {
                    resultsDiv.innerHTML = '<div class="text-center text-gray-500 py-8">Tiada pengguna dijumpai.</div>';
                    return;
                }

                const userJson = JSON.stringify(user).replace(/"/g, '&quot;');
                resultsDiv.innerHTML = data.data.map(user => `
                    <div class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition-shadow flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                ${user.avatar_url 
                                    ? `<img src="${user.avatar_url}" class="h-12 w-12 rounded-full object-cover">`
                                    : `<div class="h-12 w-12 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">${user.username.charAt(0).toUpperCase()}</div>`
                                }
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">${user.full_name || user.username}</p>
                                <span class="text-xs px-2 py-0.5 rounded-full ${user.role === 'teacher' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'}">
                                    ${user.role === 'teacher' ? 'Cikgu' : 'Pelajar'}
                                </span>
                            </div>
                        </div>
                        <button onclick='addFriend(${user.id}, this, ${JSON.stringify(user).replace(/'/g, "&#39;")})' class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                            <i class="fas fa-user-plus mr-1"></i> Tambah
                        </button>
                    </div>
                `).join('');

            } catch (error) {
                console.error('Search error:', error);
                resultsDiv.innerHTML = '<div class="text-center text-red-500 py-8">Ralat semasa mencari. Sila cuba lagi.</div>';
            }
        }

        async function addFriend(friendId, btnElement, user) {
            // Disable button immediately
            const originalText = btnElement.innerHTML;
            btnElement.disabled = true;
            btnElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            try {
                const response = await fetch('/api/friends/add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ friend_id: friendId })
                });

                const data = await response.json();
                
                if (data.status === 200) {
                    // Update button state
                    btnElement.innerHTML = '<i class="fas fa-check mr-1"></i> Dihantar';
                    btnElement.classList.remove('text-blue-600', 'hover:text-blue-800');
                    btnElement.classList.add('text-green-600', 'cursor-default');
                    
                    // Update Sent Requests count
                    const sentCountSpan = document.getElementById('count-sent');
                    if (sentCountSpan) {
                        let count = parseInt(sentCountSpan.innerText) || 0;
                        sentCountSpan.innerText = count + 1;
                    }

                    // ADD TO LIST: Dynamically add to Sent Requests tab
                    const sentContent = document.getElementById('content-sent');
                    if (sentContent) {
                        // Check if it's currently showing empty state logic
                        // We check if it has the empty icon/text div
                        const emptyState = sentContent.querySelector('.text-center.py-12');
                        let listContainer = sentContent.querySelector('.space-y-4');

                        if (emptyState) {
                            emptyState.remove();
                        }
                        
                        if (!listContainer) {
                            // Create container if it didn't exist
                            listContainer = document.createElement('div');
                            listContainer.className = 'space-y-4';
                            sentContent.appendChild(listContainer);
                        }

                        // Create new card
                        const newCard = document.createElement('div');
                        newCard.className = 'bg-gray-50 rounded-lg p-4 hover:shadow-md transition-shadow';
                        newCard.innerHTML = `
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        ${user.avatar_url 
                                            ? `<img src="${user.avatar_url}" class="h-12 w-12 rounded-full object-cover">`
                                            : `<div class="h-12 w-12 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">${(user.username || 'U').charAt(0).toUpperCase()}</div>`
                                        }
                                    </div>
                                    <div>
                                        <a href="/profile/${user.id}" class="block">
                                            <p class="text-sm font-medium text-gray-900">
                                                ${user.full_name || user.username}
                                            </p>
                                            <p class="text-sm text-gray-500">Request sent just now</p>
                                        </a>
                                    </div>
                                </div>
                                <button onclick="cancelFriendRequest(${user.id})" 
                                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 text-sm">
                                    Cancel
                                </button>
                            </div>
                        `;
                        // Prepend to list
                        listContainer.prepend(newCard);
                    }
                    
                    // Show notification
                    const notification = document.createElement('div');
                    notification.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-bounce';
                    notification.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Permintaan dihantar';
                    document.body.appendChild(notification);
                    setTimeout(() => notification.remove(), 3000);

                } else {
                    alert(data.message || 'Gagal menambah rakan');
                    btnElement.innerHTML = originalText;
                    btnElement.disabled = false;
                }
            } catch (error) {
                console.error('Error adding friend:', error);
                alert('Gagal menambah rakan');
                btnElement.innerHTML = originalText;
                btnElement.disabled = false;
            }
        }
    </script>
</x-app-layout>

