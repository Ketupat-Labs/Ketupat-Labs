<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Profile Header -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-6 py-8">
                    <div class="flex items-start space-x-6">
                        <!-- Avatar -->
                        <div class="flex-shrink-0">
                            <div class="h-32 w-32 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
                                @if($profileUser->avatar_url)
                                    <img src="{{ asset($profileUser->avatar_url) }}" alt="{{ $profileUser->full_name }}" class="h-full w-full object-cover">
                                @else
                                    <span class="text-4xl font-bold text-gray-600">
                                        {{ strtoupper(substr($profileUser->full_name ?? $profileUser->username ?? 'U', 0, 1)) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Profile Info -->
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h1 class="text-3xl font-bold text-gray-900">{{ $profileUser->full_name ?? $profileUser->username }}</h1>
                                    @if($profileUser->role)
                                        <span class="inline-block mt-2 px-3 py-1 text-xs font-semibold rounded-full {{ $profileUser->role === 'teacher' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $profileUser->role === 'teacher' ? 'Teacher' : 'Student' }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex space-x-3">
                                    @if($isOwnProfile)
                                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                            <i class="fas fa-edit mr-2"></i> Edit Profile
                                        </a>
                                    @else
                                        @if($isFriend)
                                            <button onclick="removeFriend({{ $profileUser->id }})" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                                                <i class="fas fa-user-minus mr-2"></i> Remove Friend
                                            </button>
                                        @elseif($hasPendingRequest)
                                            <button class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg cursor-not-allowed" disabled>
                                                <i class="fas fa-clock mr-2"></i> Request Pending
                                            </button>
                                        @else
                                            <button onclick="addFriend({{ $profileUser->id }})" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                                <i class="fas fa-user-plus mr-2"></i> Add Friend
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <!-- Profile Details -->
                            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                @if($profileUser->school)
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">School</span>
                                        <p class="text-gray-900">{{ $profileUser->school }}</p>
                                    </div>
                                @endif
                                @if($profileUser->class)
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Class</span>
                                        <p class="text-gray-900">{{ $profileUser->class }}</p>
                                    </div>
                                @endif
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Friends</span>
                                    <p class="text-gray-900">{{ $friendCount }}</p>
                                </div>
                            </div>

                            @if($profileUser->bio)
                                <div class="mt-6">
                                    <span class="text-sm font-medium text-gray-500">Bio</span>
                                    <p class="text-gray-900 mt-2">{{ $profileUser->bio }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Tabs -->
            <div class="bg-white shadow rounded-lg">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px" aria-label="Tabs">
                        @if($isOwnProfile)
                            <button onclick="showSection('saved')" id="tab-saved" class="section-tab active py-4 px-6 text-sm font-medium border-b-2 border-blue-500 text-blue-600">
                                <i class="fas fa-bookmark mr-2"></i>Saved Posts
                            </button>
                        @endif
                        <button onclick="showSection('posts')" id="tab-posts" class="section-tab {{ $isOwnProfile ? '' : 'active' }} py-4 px-6 text-sm font-medium border-b-2 {{ $isOwnProfile ? 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' : 'border-blue-500 text-blue-600' }}">
                            <i class="fas fa-comments mr-2"></i>Forum Posts
                        </button>
                        <button onclick="showSection('badges')" id="tab-badges" class="section-tab py-4 px-6 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            <i class="fas fa-trophy mr-2"></i>Badges
                        </button>
                    </nav>
                </div>

                <!-- Saved Posts Section -->
                @if($isOwnProfile)
                    <div id="section-saved" class="section-content p-6">
                        @if($savedPosts->count() > 0)
                            <div class="space-y-4">
                                @foreach($savedPosts as $post)
                                    <div class="border-b border-gray-200 pb-4 last:border-b-0">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <a href="{{ route('forum.post.detail', $post->id) }}" class="text-lg font-semibold text-blue-600 hover:text-blue-800">
                                                    {{ $post->title }}
                                                </a>
                                                <p class="text-sm text-gray-600 mt-1">
                                                    in <span class="font-medium">{{ $post->forum->title }}</span>
                                                    <span class="mx-2">•</span>
                                                    {{ $post->created_at->diffForHumans() }}
                                                </p>
                                                @if($post->content)
                                                    <p class="text-gray-700 mt-2 line-clamp-2">{{ Str::limit(strip_tags($post->content), 150) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-8">No saved posts yet</p>
                        @endif
                    </div>
                @endif

                <!-- Forum Posts Section -->
                <div id="section-posts" class="section-content {{ $isOwnProfile ? 'hidden' : '' }} p-6">
                    @if($posts->count() > 0)
                        <div class="space-y-4">
                            @foreach($posts as $post)
                                <div class="border-b border-gray-200 pb-4 last:border-b-0">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <a href="{{ route('forum.post.detail', $post->id) }}" class="text-lg font-semibold text-blue-600 hover:text-blue-800">
                                                {{ $post->title }}
                                            </a>
                                            <p class="text-sm text-gray-600 mt-1">
                                                in <span class="font-medium">{{ $post->forum->title }}</span>
                                                <span class="mx-2">•</span>
                                                {{ $post->created_at->diffForHumans() }}
                                            </p>
                                            @if($post->content)
                                                <p class="text-gray-700 mt-2 line-clamp-2">{{ Str::limit(strip_tags($post->content), 150) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">No posts yet</p>
                    @endif
                </div>

                <!-- Badges Section -->
                <div id="section-badges" class="section-content hidden p-6">
                    @if($badges->count() > 0)
                        <!-- Filter Controls -->
                        <div class="mb-6 flex flex-wrap gap-4 items-center bg-gray-50 p-4 rounded-lg">
                            <!-- Category Filter -->
                            <div class="flex items-center space-x-2">
                                <label for="badge-category" class="text-sm font-medium text-gray-700 bg-white px-2 py-1 rounded shadow-sm">
                                    <i class="fas fa-filter text-blue-500 mr-1"></i> Kategori
                                </label>
                                <select id="badge-category" onchange="filterBadges()" class="block w-full pl-3 pr-10 py-1 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md shadow-sm">
                                    <option value="all">Semua</option> <!-- All -->
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Status Filter -->
                            <div class="flex items-center space-x-2">
                                <label for="badge-status" class="text-sm font-medium text-gray-700 bg-white px-2 py-1 rounded shadow-sm">
                                    <i class="fas fa-tasks text-green-500 mr-1"></i> Status
                                </label>
                                <select id="badge-status" onchange="filterBadges()" class="block w-full pl-3 pr-10 py-1 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md shadow-sm">
                                    <option value="all">Semua</option> <!-- All -->
                                    <option value="earned">Diperolehi</option> <!-- Earned -->
                                    <option value="locked">Terkunci</option> <!-- Locked -->
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <p class="text-sm text-gray-600">
                                {{ __('Menunjukkan') }} <span id="visible-badges-count" class="font-semibold">{{ $badges->count() }}</span> {{ __('lencana') }}.
                                <span class="font-semibold text-blue-600">{{ $badges->where('is_earned', true)->count() }} {{ __('diperolehi') }}</span>
                            </p>
                        </div>

                        <div id="badges-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            @foreach($badges as $badge)
                                <div class="badge-item relative p-4 rounded-lg border transition-all duration-200 {{ $badge->is_earned ? 'bg-gradient-to-br from-gray-50 to-gray-100 border-gray-200 hover:shadow-md' : 'bg-gray-50 border-gray-300 opacity-75' }}"
                                     data-category="{{ $badge->category_id }}"
                                     data-status="{{ $badge->is_earned ? 'earned' : 'locked' }}">
                                    <div class="flex flex-col items-center text-center">
                                        <!-- Badge Icon -->
                                        <div class="mb-3 relative">
                                            @if($badge->icon)
                                                <div class="w-16 h-16 rounded-full flex items-center justify-center text-3xl transition-all duration-200 {{ $badge->is_earned ? '' : 'grayscale brightness-75' }}" 
                                                     style="background: {{ $badge->is_earned ? ($badge->color ?? '#3B82F6') : '#9CA3AF' }}20; color: {{ $badge->is_earned ? ($badge->color ?? '#3B82F6') : '#6B7280' }};">
                                                    <i class="{{ $badge->icon }}"></i>
                                                </div>
                                            @else
                                                <div class="w-16 h-16 rounded-full flex items-center justify-center text-3xl transition-all duration-200 {{ $badge->is_earned ? 'bg-blue-500 text-white' : 'bg-gray-400 text-gray-200' }}">
                                                    <i class="fas fa-trophy"></i>
                                                </div>
                                            @endif
                                            @if($badge->is_earned)
                                                <div class="absolute -top-1 -right-1 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-check text-white text-xs"></i>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Badge Name -->
                                        <h3 class="font-semibold mb-1 text-sm {{ $badge->is_earned ? 'text-gray-900' : 'text-gray-500' }}">{{ $badge->name }}</h3>
                                        
                                        <!-- Badge Description -->
                                        @if($badge->description)
                                            <p class="text-xs mb-2 line-clamp-2 {{ $badge->is_earned ? 'text-gray-600' : 'text-gray-400' }}">{{ Str::limit($badge->description, 60) }}</p>
                                        @endif
                                        
                                        <!-- Badge Category -->
                                        @if($badge->category)
                                            <span class="inline-block px-2 py-1 text-xs rounded-full transition-all duration-200 {{ $badge->is_earned ? '' : 'opacity-50' }}" 
                                                  style="background: {{ $badge->is_earned ? ($badge->category->color ?? '#E5E7EB') : '#E5E7EB' }}20; color: {{ $badge->is_earned ? ($badge->category->color ?? '#6B7280') : '#9CA3AF' }};">
                                                {{ $badge->category->name }}
                                            </span>
                                        @endif
                                        
                                        <!-- XP Reward -->
                                        @if($badge->xp_reward > 0)
                                            <div class="mt-2 text-xs {{ $badge->is_earned ? 'text-gray-500' : 'text-gray-400' }}">
                                                <i class="fas fa-star {{ $badge->is_earned ? 'text-yellow-500' : 'text-gray-400' }}"></i> +{{ $badge->xp_reward }} XP
                                            </div>
                                        @endif
                                        
                                        <!-- Locked/Unearned Indicator -->
                                        @if(!$badge->is_earned)
                                            <div class="mt-2 text-xs text-gray-400 italic">
                                                <i class="fas fa-lock"></i> {{ __('Belum diperolehi') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            <div id="no-badges-found" class="hidden col-span-full text-center py-8 text-gray-500">
                                {{ __('Tiada lencana ditemui untuk penapis ini.') }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="inline-block p-4 bg-gray-100 rounded-full mb-4">
                                <i class="fas fa-trophy text-gray-400 text-4xl"></i>
                            </div>
                            <p class="text-gray-500 text-lg font-medium">{{ __('Tiada lencana tersedia') }}</p>
                            <p class="text-gray-400 text-sm mt-2">{{ __('Lencana akan muncul di sini sebaik sahaja ia ditambah ke dalam sistem.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function filterBadges() {
            const categoryFilter = document.getElementById('badge-category').value;
            const statusFilter = document.getElementById('badge-status').value;
            const badges = document.querySelectorAll('.badge-item');
            let visibleCount = 0;

            badges.forEach(badge => {
                const category = badge.getAttribute('data-category');
                const status = badge.getAttribute('data-status');
                
                let show = true;

                // Category Filter
                if (categoryFilter !== 'all' && category !== categoryFilter) {
                    show = false;
                }

                // Status Filter
                if (statusFilter !== 'all') {
                    if (statusFilter === 'earned' && status !== 'earned') show = false;
                    if (statusFilter === 'locked' && status !== 'locked') show = false;
                }

                if (show) {
                    badge.classList.remove('hidden');
                    visibleCount++;
                } else {
                    badge.classList.add('hidden');
                }
            });
            
            // Update count
            const countEl = document.getElementById('visible-badges-count');
            if(countEl) countEl.innerText = visibleCount;

            // Show 'no results' message
            const noResults = document.getElementById('no-badges-found');
            if (noResults) {
                if (visibleCount === 0) {
                    noResults.classList.remove('hidden');
                } else {
                    noResults.classList.add('hidden');
                }
            }
        }
        
        function showSection(sectionName) {
            // Hide all sections
            document.querySelectorAll('.section-content').forEach(section => {
                section.classList.add('hidden');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.section-tab').forEach(tab => {
                tab.classList.remove('active', 'border-blue-500', 'text-blue-600');
                tab.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Show selected section
            const section = document.getElementById('section-' + sectionName);
            if (section) {
                section.classList.remove('hidden');
            }
            
            // Add active class to selected tab
            const tab = document.getElementById('tab-' + sectionName);
            if (tab) {
                tab.classList.add('active', 'border-blue-500', 'text-blue-600');
                tab.classList.remove('border-transparent', 'text-gray-500');
            }
        }

        async function addFriend(friendId) {
            try {
                const response = await fetch('/api/friends/add', {
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
                    alert('Friend request sent!');
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to send friend request');
                }
            } catch (error) {
                console.error('Error adding friend:', error);
                alert('Failed to send friend request');
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
    </script>
</x-app-layout>

