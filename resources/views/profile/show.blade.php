<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profil') }}
        </h2>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-gray-50 to-white min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Profile Header -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
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
                                    <div class="flex items-center gap-3">
                                        <h1 class="text-3xl font-bold text-gray-900">{{ $profileUser->full_name ?? $profileUser->username }}</h1>
                                        
                                        @if(($profileUser->share_badges_on_profile ?? true) && isset($badges))
                                            <div class="flex items-center -space-x-2 overflow-hidden py-1">
                                                @foreach($badges->where('is_visible', true)->take(3) as $badge)
                                                    <div class="relative inline-block h-8 w-8 rounded-full ring-2 ring-white bg-white z-10" 
                                                         title="{{ $badge->name }}">
                                                        @if($badge->icon && str_starts_with($badge->icon, 'fa'))
                                                            <div class="w-full h-full flex items-center justify-center rounded-full bg-{{ $badge->color ?? 'blue' }}-100 text-{{ $badge->color ?? 'blue' }}-600">
                                                                <i class="{{ $badge->icon }} text-xs"></i>
                                                            </div>
                                                        @else
                                                            <img class="h-full w-full rounded-full object-cover" 
                                                                 src="{{ asset($badge->icon_url ?? 'assets/badges/default.png') }}" 
                                                                 alt="{{ $badge->name }}">
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    @if($profileUser->role)
                                        <span class="inline-block mt-2 px-3 py-1 text-xs font-semibold rounded-full {{ $profileUser->role === 'teacher' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $profileUser->role === 'teacher' ? 'Guru' : 'Pelajar' }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex space-x-3">
                                    @if($isOwnProfile)
                                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                            <i class="fas fa-edit mr-2"></i> Sunting Profil
                                        </a>
                                    @else
                                        <button id="followButton" onclick="toggleFollow({{ $profileUser->id }})" class="inline-flex items-center px-4 py-2 {{ $isFollowing ? 'bg-gray-600 hover:bg-gray-700' : 'bg-blue-600 hover:bg-blue-700' }} text-white rounded-lg transition">
                                            <i class="fas {{ $isFollowing ? 'fa-user-check' : 'fa-user-plus' }} mr-2"></i> 
                                            <span id="followButtonText">{{ $isFollowing ? 'Mengikuti' : 'Ikut' }}</span>
                                        </button>
                                        <button onclick="startConversation({{ $profileUser->id }})" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                            <i class="fas fa-envelope mr-2"></i> Mesej
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <!-- Follow Stats -->
                            <div class="mt-6 flex space-x-6">
                                <button onclick="showFollowingModal({{ $profileUser->id }})" class="text-center hover:text-blue-600 transition cursor-pointer">
                                    <div class="text-2xl font-bold text-gray-900">{{ $followingCount }}</div>
                                    <div class="text-sm text-gray-500">Mengikuti</div>
                                </button>
                                <button onclick="showFollowersModal({{ $profileUser->id }})" class="text-center hover:text-blue-600 transition cursor-pointer">
                                    <div class="text-2xl font-bold text-gray-900">{{ $followersCount }}</div>
                                    <div class="text-sm text-gray-500">Pengikut</div>
                                </button>
                            </div>

                            <!-- Profile Details -->
                            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                @if($profileUser->school)
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Sekolah</span>
                                        <p class="text-gray-900">{{ $profileUser->school }}</p>
                                    </div>
                                @endif
                                @if($profileUser->class)
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Kelas</span>
                                        <p class="text-gray-900">{{ $profileUser->class }}</p>
                                    </div>
                                @endif
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
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px" aria-label="Tabs">
                        @if($isOwnProfile)
                            <button onclick="showSection('saved')" id="tab-saved" class="section-tab active py-4 px-6 text-sm font-medium border-b-2 border-blue-500 text-blue-600">
                                <i class="fas fa-bookmark mr-2"></i>Hantaran Disimpan
                            </button>
                        @endif
                        <button onclick="showSection('posts')" id="tab-posts" class="section-tab {{ $isOwnProfile ? '' : 'active' }} py-4 px-6 text-sm font-medium border-b-2 {{ $isOwnProfile ? 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' : 'border-blue-500 text-blue-600' }}">
                            <i class="fas fa-comments mr-2"></i>Hantaran Forum
                        </button>
                        <button onclick="showSection('badges')" id="tab-badges" class="section-tab py-4 px-6 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            <i class="fas fa-trophy mr-2"></i>Lencana
                        </button>
                    </nav>
                </div>

                <!-- Saved Posts Section -->
                @if($isOwnProfile)
                    <div id="section-saved" class="section-content p-6">
                        <div id="savedPostsContent">
                            <div class="text-center py-8">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                                <p class="text-gray-500 mt-2">Memuatkan hantaran disimpan...</p>
                            </div>
                        </div>
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
                                                <div class="text-gray-700 mt-2 post-content-preview" data-content="{{ htmlspecialchars($post->content, ENT_QUOTES, 'UTF-8') }}">
                                                    <p class="line-clamp-2">{{ Str::limit(strip_tags($post->content), 150) }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">Tiada hantaran lagi</p>
                    @endif
                </div>

                <!-- Badges Section -->
                <div id="section-badges" class="section-content hidden p-6">
                    <!-- Badge Filters -->
                    <div class="mb-6 space-y-4">
                        <!-- Category Filter -->
                        <div>
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Kategori</span>
                            <div class="flex flex-wrap gap-2" id="profileCategoryFilters">
                                <button class="filter-btn active px-3 py-1 text-sm rounded-full border border-blue-500 bg-blue-500 text-white transition-colors" data-filter="all" data-type="category">Semua</button>
                                @foreach($categories as $cat)
                                    @if($cat && $cat->name)
                                        <button class="filter-btn px-3 py-1 text-sm rounded-full border border-gray-300 text-gray-600 hover:border-gray-400 bg-white transition-colors" 
                                                data-filter="{{ $cat->code }}" 
                                                data-type="category">
                                            {{ $cat->name }}
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Status</span>
                            <div class="flex flex-wrap gap-2" id="profileStatusFilters">
                                <button class="filter-btn active px-3 py-1 text-sm rounded-full border border-blue-500 bg-blue-500 text-white transition-colors" data-filter="all" data-type="status">Semua</button>
                                <button class="filter-btn px-3 py-1 text-sm rounded-full border border-gray-300 text-gray-600 hover:border-gray-400 bg-white transition-colors" data-filter="earned" data-type="status">Tercapai</button>
                                <button class="filter-btn px-3 py-1 text-sm rounded-full border border-gray-300 text-gray-600 hover:border-gray-400 bg-white transition-colors" data-filter="locked" data-type="status">Terkunci</button>
                            </div>
                        </div>
                    </div>

                    @if($badges->count() > 0)
                        <div class="mb-4">
                            <p class="text-sm text-gray-600">
                                {{ __('Menunjukkan :count lencana.', ['count' => $badges->count()]) }} 
                                <span class="font-semibold text-blue-600">{{ $badges->where('is_earned', true)->count() }} {{ __('tercapai') }}</span>
                            </p>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="profileBadgesGrid">
                            @foreach($badges as $badge)
                                @php
                                    // Badges are binary: either earned or locked (no progress state)
                                    $statusType = $badge->is_earned ? 'earned' : 'locked';
                                @endphp
                                <div class="badge-item relative p-4 rounded-lg border transition-all duration-200 {{ $badge->is_earned ? 'bg-gradient-to-br from-gray-50 to-gray-100 border-gray-200 hover:shadow-md' : 'bg-gray-50 border-gray-300 opacity-75' }} cursor-pointer"
                                     data-category="{{ $badge->category->code ?? 'uncategorized' }}"
                                     data-status-type="{{ $statusType }}"
                                     onclick="showBadgeModal({{ $badge->id }}, '{{ $badge->code }}', '{{ addslashes($badge->name) }}', '{{ addslashes($badge->description ?? '') }}', '{{ $badge->icon ?? 'fas fa-trophy' }}', '{{ $badge->color ?? '#3B82F6' }}', '{{ $badge->category->name ?? 'Uncategorized' }}', '{{ $badge->category->color ?? '#E5E7EB' }}', {{ $badge->xp_reward ?? 0 }}, {{ $badge->requirement_value ?? ($badge->points_required ?? 0) }}, {{ $badge->progress ?? 0 }}, '{{ $statusType }}')">
                                    
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
                                                <i class="fas fa-lock"></i> {{ __('Terkunci') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
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

    <!-- Include Forum CSS for post styling -->
    <link rel="stylesheet" href="{{ asset('Forum/CSS/forum.css') }}">
    
    <!-- Override forum.css global styles that affect the profile page -->
    <script src="{{ asset('assets/js/profile.js') }}"></script>
    
    <script>
        // Load and render saved posts in forum format
        async function loadSavedPosts() {
            const container = document.getElementById('savedPostsContent');
            if (!container) return;
            
            try {
                const response = await fetch('/api/forum/saved-posts', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'include',
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.status === 200 && data.data && data.data.posts) {
                    const posts = data.data.posts;
                    
                    if (posts.length === 0) {
                        container.innerHTML = `
                            <div class="text-center py-8">
                                <i class="fas fa-bookmark text-gray-400 text-4xl mb-4"></i>
                                <p class="text-gray-500 text-lg">Tiada hantaran disimpan lagi</p>
                                <p class="text-gray-400 text-sm mt-2">Hantaran yang anda simpan akan muncul di sini</p>
                            </div>
                        `;
                    } else {
                        // Render posts using the same format as forum main page
                        container.innerHTML = renderSavedPosts(posts);
                    }
                } else {
                    container.innerHTML = `
                        <div class="text-center py-8">
                            <p class="text-gray-500">Failed to load saved posts</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading saved posts:', error);
                container.innerHTML = `
                    <div class="text-center py-8">
                        <p class="text-gray-500">Error loading saved posts</p>
                    </div>
                `;
            }
        }
        
        // Render saved posts in the same format as forum main page
        function renderSavedPosts(posts) {
            if (posts.length === 0) {
                return `
                    <div class="text-center py-8">
                        <i class="fas fa-bookmark text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-500 text-lg">Tiada hantaran disimpan lagi</p>
                    </div>
                `;
            }
            
            return `
                <div class="reddit-content">
                    ${posts.map(post => `
                        <div class="reddit-post-card" onclick="if(typeof openPost === 'function') { openPost(${post.id}); } else { window.location.href='/forum/post/${post.id}'; }">
                            <div class="post-content-section">
                                <div class="post-header">
                                    <div class="post-header-left">
                                        <span class="post-community">${escapeHtml(post.forum_name || 'Forum')}</span>
                                        <span class="post-time">•</span>
                                        <span class="post-time">${formatTime(post.created_at)}</span>
                                        ${post.is_pinned ? '<span class="post-pinned"><i class="fas fa-thumbtack"></i> disematkan</span>' : ''}
                                    </div>
                                </div>
                                <div class="post-title">
                                    ${escapeHtml(post.title)}
                                </div>
                                ${post.post_type === 'poll' && post.poll_options ? `
                                <div class="post-preview-text" style="margin-top: 12px;">
                                    <div style="font-weight: 600; margin-bottom: 8px; color: #666;">Poll Options:</div>
                                    ${post.poll_options.map((option, idx) => `
                                        <div style="padding: 8px; margin: 4px 0; background: #f5f5f5; border-radius: 4px; display: flex; align-items: center; gap: 8px;">
                                            <span style="font-weight: 600; color: #ff4500;">${idx + 1}.</span>
                                            <span>${escapeHtml(option.text)}</span>
                                            ${option.vote_count > 0 ? `<span style="margin-left: auto; color: #666; font-size: 0.9em;">${option.vote_count} vote${option.vote_count !== 1 ? 's' : ''}</span>` : ''}
                                        </div>
                                    `).join('')}
                                </div>
                                ` : `
                                <div class="post-preview-text">
                                    ${post.content ? (typeof processVideoLinks === 'function' ? processVideoLinks(post.content) : escapeHtml(post.content)) : ''}
                                </div>
                                `}
                                ${post.attachments && post.attachments.length > 0 && post.post_type !== 'link' ? `
                                    <div style="margin-top: 12px;">
                                        ${(() => {
                                            try {
                                                const attachments = Array.isArray(post.attachments) ? post.attachments : [];
                                                if (attachments.length === 0) return '';
                                                
                                                const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                                                const imageAttachments = [];
                                                const otherAttachments = [];
                                                
                                                attachments.forEach(att => {
                                                    const ext = (att.name || '').split('.').pop().toLowerCase();
                                                    if (imageExts.includes(ext)) {
                                                        imageAttachments.push(att);
                                                    } else {
                                                        otherAttachments.push(att);
                                                    }
                                                });
                                                
                                                let html = '';
                                                
                                                if (imageAttachments.length > 0) {
                                                    html += '<div class="post-image-preview" style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 8px;">';
                                                    imageAttachments.forEach(att => {
                                                        const normalizedUrl = (typeof normalizeFileUrl === 'function' ? normalizeFileUrl(att.url) : (att.url.startsWith('/') ? att.url : '/' + att.url));
                                                        html += `
                                                            <div style="position: relative; max-width: 300px; max-height: 300px;">
                                                                <img src="${normalizedUrl}" alt="${escapeHtml(att.name)}" 
                                                                     style="max-width: 100%; max-height: 300px; object-fit: contain; border-radius: 4px; cursor: pointer;" 
                                                                     onclick="event.stopPropagation(); window.open('${normalizedUrl}', '_blank');"
                                                                     onerror="this.style.display='none';">
                                                            </div>
                                                        `;
                                                    });
                                                    html += '</div>';
                                                }
                                                
                                                if (otherAttachments.length > 0) {
                                                    html += '<div style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 8px;">';
                                                    otherAttachments.forEach(att => {
                                                        const normalizedUrl = (typeof normalizeFileUrl === 'function' ? normalizeFileUrl(att.url) : (att.url.startsWith('/') ? att.url : '/' + att.url));
                                                        const fileIcon = (typeof getFileIcon === 'function' ? getFileIcon(att.name) : 'fa-file');
                                                        html += `
                                                            <a href="${normalizedUrl}" target="_blank" class="attachment-file" onclick="event.stopPropagation();">
                                                                <i class="fas ${fileIcon}"></i>
                                                                ${escapeHtml(att.name)}
                                                            </a>
                                                        `;
                                                    });
                                                    html += '</div>';
                                                }
                                                
                                                return html;
                                            } catch (e) {
                                                return '';
                                            }
                                        })()}
                                    </div>
                                ` : ''}
                                ${post.tags && post.tags.length > 0 ? `
                                    <div class="post-tags" style="margin-top: 8px;">
                                        ${post.tags.map(tag => `
                                            <span class="post-tag">#${escapeHtml(tag)}</span>
                                        `).join('')}
                                    </div>
                                ` : ''}
                                <div class="post-footer">
                                    <button class="post-footer-btn vote-btn-inline ${post.user_reacted ? 'active' : ''}" onclick="event.stopPropagation(); if(typeof toggleReaction === 'function') { toggleReaction(${post.id}); } else { window.location.href='/forum/post/${post.id}'; }">
                                        <i class="${post.user_reacted ? 'fas' : 'far'} fa-heart"></i>
                                        ${post.reaction_count || 0}
                                    </button>
                                    <button class="post-footer-btn" onclick="event.stopPropagation(); if(typeof openPost === 'function') { openPost(${post.id}); } else { window.location.href='/forum/post/${post.id}'; }">
                                        <i class="far fa-comment"></i>
                                        ${post.reply_count || 0} Comments
                                    </button>
                                    <button class="post-footer-btn ${post.is_bookmarked ? 'active' : ''}" onclick="event.stopPropagation(); toggleSavedPostBookmark(${post.id})">
                                        <i class="${post.is_bookmarked ? 'fas' : 'far'} fa-bookmark"></i>
                                        Save
                                    </button>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }
        
        // Helper functions
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function formatTime(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);
            
            if (diffMins < 1) return 'just now';
            if (diffMins < 60) return `${diffMins}m ago`;
            if (diffHours < 24) return `${diffHours}h ago`;
            if (diffDays < 7) return `${diffDays}d ago`;
            return date.toLocaleDateString();
        }
        
        function toggleSavedPostBookmark(postId) {
            fetch('/api/forum/bookmark', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'include',
                body: JSON.stringify({ post_id: postId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 200) {
                    loadSavedPosts(); // Reload saved posts
                }
            })
            .catch(error => {
                console.error('Error toggling bookmark:', error);
            });
        }
        
        // Load saved posts when the saved section is shown
        let savedPostsLoaded = false;
        const originalShowSection = window.showSection;
        window.showSection = function(sectionName) {
            if (typeof originalShowSection === 'function') {
                originalShowSection(sectionName);
            } else {
                // Fallback implementation
                document.querySelectorAll('.section-content').forEach(section => {
                    section.classList.add('hidden');
                });
                document.querySelectorAll('.section-tab').forEach(tab => {
                    tab.classList.remove('active', 'border-blue-500', 'text-blue-600');
                    tab.classList.add('border-transparent', 'text-gray-500');
                });
                const section = document.getElementById('section-' + sectionName);
                if (section) {
                    section.classList.remove('hidden');
                }
                const tab = document.getElementById('tab-' + sectionName);
                if (tab) {
                    tab.classList.add('active', 'border-blue-500', 'text-blue-600');
                    tab.classList.remove('border-transparent', 'text-gray-500');
                }
            }
            
            // Load saved posts when saved section is shown
            if (sectionName === 'saved' && !savedPostsLoaded) {
                savedPostsLoaded = true;
                loadSavedPosts();
            }
        };
        
        // Load saved posts on page load if saved section is active
        document.addEventListener('DOMContentLoaded', function() {
            const savedSection = document.getElementById('section-saved');
            if (savedSection && !savedSection.classList.contains('hidden')) {
                loadSavedPosts();
                savedPostsLoaded = true;
            }
            
            // Ensure responsive menu is closed on profile page
            const responsiveMenu = document.querySelector('nav [x-data]');
            if (responsiveMenu && window.Alpine) {
                // Close any open responsive menu
                const menuDiv = document.querySelector('nav .hidden.lg\\:hidden');
                if (menuDiv) {
                    menuDiv.classList.add('hidden');
                }
            }
            
            // Remove any sidebar elements that might have been created
            const sidebars = document.querySelectorAll('aside.reddit-sidebar:not(#savedPostsContent aside)');
            sidebars.forEach(sidebar => sidebar.remove());
            
            const redditContainers = document.querySelectorAll('.reddit-container:not(#savedPostsContent .reddit-container)');
            redditContainers.forEach(container => {
                if (!container.closest('#savedPostsContent')) {
                    container.remove();
                }
            });
        });

        // Follow/Unfollow functionality
        async function toggleFollow(userId) {
            const button = document.getElementById('followButton');
            const buttonText = document.getElementById('followButtonText');
            const icon = button.querySelector('i');
            
            const isFollowing = buttonText.textContent === 'Following';
            const endpoint = isFollowing ? `/api/profile/${userId}/unfollow` : `/api/profile/${userId}/follow`;
            
            // Disable button during request
            button.disabled = true;
            
            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include'
                });
                
                const data = await response.json();
                
                if (data.status === 200) {
                    // Update button state
                    if (isFollowing) {
                        button.classList.remove('bg-gray-600', 'hover:bg-gray-700');
                        button.classList.add('bg-blue-600', 'hover:bg-blue-700');
                        icon.classList.remove('fa-user-check');
                        icon.classList.add('fa-user-plus');
                        buttonText.textContent = 'Ikut';
                    } else {
                        button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                        button.classList.add('bg-gray-600', 'hover:bg-gray-700');
                        icon.classList.remove('fa-user-plus');
                        icon.classList.add('fa-user-check');
                        buttonText.textContent = 'Mengikuti';
                    }
                    
                    // Update followers count
                    updateFollowCounts(userId);
                } else {
                    alert(data.message || 'Failed to update follow status');
                }
            } catch (error) {
                console.error('Error toggling follow:', error);
                alert('Failed to update follow status');
            } finally {
                button.disabled = false;
            }
        }
        
        // Update follow counts
        async function updateFollowCounts(userId) {
            try {
                const followingResponse = await fetch(`/api/profile/${userId}/following`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    credentials: 'include'
                });
                
                const followersResponse = await fetch(`/api/profile/${userId}/followers`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    credentials: 'include'
                });
                
                const followingData = await followingResponse.json();
                const followersData = await followersResponse.json();
                
                if (followingData.status === 200 && followersData.status === 200) {
                    const followingCount = followingData.data.users.length;
                    const followersCount = followersData.data.users.length;
                    
                    // Update the display
                    const followingElement = document.querySelector('[onclick*="showFollowingModal"]');
                    const followersElement = document.querySelector('[onclick*="showFollowersModal"]');
                    
                    if (followingElement) {
                        const countElement = followingElement.querySelector('.text-2xl');
                        if (countElement) countElement.textContent = followingCount;
                    }
                    
                    if (followersElement) {
                        const countElement = followersElement.querySelector('.text-2xl');
                        if (countElement) countElement.textContent = followersCount;
                    }
                }
            } catch (error) {
                console.error('Error updating follow counts:', error);
            }
        }
        
        // Show following modal
        async function showFollowingModal(userId) {
            try {
                const response = await fetch(`/api/profile/${userId}/following`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    credentials: 'include'
                });
                
                const data = await response.json();
                
                if (data.status === 200) {
                    showUserListModal('Following', data.data.users);
                } else {
                    alert('Failed to load following list');
                }
            } catch (error) {
                console.error('Error loading following:', error);
                alert('Failed to load following list');
            }
        }
        
        // Show followers modal
        async function showFollowersModal(userId) {
            try {
                const response = await fetch(`/api/profile/${userId}/followers`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    credentials: 'include'
                });
                
                const data = await response.json();
                
                if (data.status === 200) {
                    showUserListModal('Followed by', data.data.users);
                } else {
                    alert('Failed to load followers list');
                }
            } catch (error) {
                console.error('Error loading followers:', error);
                alert('Failed to load followers list');
            }
        }
        
        // Show user list modal
        function showUserListModal(title, users) {
            const modal = document.createElement('div');
            modal.id = 'userListModal';
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            modal.innerHTML = `
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 max-h-[80vh] overflow-hidden flex flex-col">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">${title}</h3>
                        <button onclick="closeUserListModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="px-6 py-4 overflow-y-auto flex-1">
                        ${users.length === 0 ? `
                            <p class="text-gray-500 text-center py-8">No users found</p>
                        ` : `
                            <div class="space-y-3">
                                ${users.map(user => `
                                    <div class="flex items-center space-x-3 p-3 hover:bg-gray-50 rounded-lg cursor-pointer" onclick="window.location.href='/profile/${user.id}'">
                                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden flex-shrink-0">
                                            ${user.avatar_url ? `
                                                <img src="${user.avatar_url.startsWith('/') ? user.avatar_url : '/' + user.avatar_url}" alt="${user.full_name}" class="w-full h-full object-cover">
                                            ` : `
                                                <span class="text-lg font-bold text-gray-600">
                                                    ${(user.full_name || user.username || 'U').charAt(0).toUpperCase()}
                                                </span>
                                            `}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="font-semibold text-gray-900 truncate">${user.full_name || user.username}</div>
                                            <div class="text-sm text-gray-500 truncate">@${user.username}</div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        `}
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Close on outside click
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeUserListModal();
                }
            });
        }
        
        // Close user list modal
        function closeUserListModal() {
            const modal = document.getElementById('userListModal');
            if (modal) {
                modal.remove();
            }
        }
        
        async function startConversation(userId) {
            try {
                // Show loading state
                const button = event.target.closest('button');
                const originalText = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Membuka...';
                
                // Create or get existing conversation
                const response = await fetch('/api/messaging/conversation/direct', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ user_id: userId }),
                    credentials: 'include'
                });
                
                const data = await response.json();
                
                if (data.status === 200 && data.data.conversation_id) {
                    // Redirect to messaging page with conversation
                    window.location.href = '{{ route("messaging.index") }}?conversation=' + data.data.conversation_id;
                } else {
                    // Fallback: redirect to messaging page
                    window.location.href = '{{ route("messaging.index") }}';
                }
            } catch (error) {
                console.error('Error starting conversation:', error);
                // Fallback: redirect to messaging page
                window.location.href = '{{ route("messaging.index") }}';
            }
        }
        
        // Badge Modal Function - Dynamic Content Loading
        function showBadgeModal(badgeId, badgeCode, badgeName, badgeDescription, badgeIcon, badgeColor, categoryName, categoryColor, xpReward, pointsRequired, progress, status) {
            // 1. Update Modal Content dynamically
            document.getElementById('badgeModalIcon').className = badgeIcon + ' text-6xl';
            document.getElementById('badgeModalIcon').style.color = badgeColor;
            
            document.getElementById('badgeModalTitle').textContent = badgeName;
            document.getElementById('badgeModalDescription').textContent = badgeDescription || 'No description available.';
            
            // Category styling
            const catBadge = document.getElementById('badgeModalCategory');
            catBadge.textContent = categoryName;
            catBadge.style.backgroundColor = categoryColor + '20'; // 20 hex = 12% opacity
            catBadge.style.color = categoryColor;
            
            // Stats
            document.getElementById('badgeModalXP').textContent = xpReward + ' XP';
            document.getElementById('badgeModalRequired').textContent = pointsRequired + ' Mata';
            document.getElementById('badgeModalProgress').textContent = progress + ' / ' + pointsRequired;
            
            // 2. Button Logic
            const modalButton = document.getElementById('badgeModalButton');
            if (status === 'locked' || status === 'progress') {
                modalButton.textContent = 'Terkunci';
                modalButton.className = 'w-full px-6 py-3 bg-gray-400 text-white rounded-lg font-semibold cursor-not-allowed';
                modalButton.disabled = true;
            } else if (status === 'earned') {
                modalButton.textContent = 'Tercapai';
                modalButton.className = 'w-full px-6 py-3 bg-green-500 text-white rounded-lg font-semibold hover:bg-green-600 transition shadow-md';
                modalButton.disabled = false;
            }
            
            // 3. Show Modal using Bootstrap 5 API
            const modalEl = document.getElementById('badgeDetailModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    </script>
    
    <!-- Dynamic Badge Detail Modal -->
    <!-- Dynamic Badge Detail Modal -->
    <!-- Added style z-index: 10000 to ensure it sits above any backdrop -->
    <div class="modal fade" id="badgeDetailModal" tabindex="-1" aria-labelledby="badgeDetailModalLabel" aria-hidden="true" style="z-index: 10000 !important;">
        <div class="modal-dialog modal-dialog-centered" style="z-index: 10001;">
            <!-- Modal Content with Rounded Corners and Forced White Background -->
            <div class="modal-content rounded-2xl border-0 shadow-2xl relative overflow-hidden bg-white">
                
                <!-- Close Button: Simple, Standard, High-Z-Index -->
                <button type="button" 
                        class="absolute top-4 right-4 z-[9999] p-2 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer border-0 bg-transparent flex items-center justify-center"
                        style="width: 40px; height: 40px; pointer-events: auto;"
                        data-bs-dismiss="modal"
                        data-dismiss="modal"
                        onclick="const m = bootstrap.Modal.getInstance(document.getElementById('badgeDetailModal')); if(m) m.hide();" 
                        aria-label="Close">
                    <i class="fas fa-times text-2xl"></i>
                </button>
                
                <div class="modal-body px-8 py-10 text-center bg-white">
                    
                    <!-- Icon Circle -->
                    <div class="mb-6 flex justify-center">
                        <div class="w-24 h-24 rounded-full flex items-center justify-center bg-opacity-10" style="background-color: rgba(59, 130, 246, 0.1);">
                            <i id="badgeModalIcon" class="fas fa-trophy text-6xl"></i>
                        </div>
                    </div>
                    
                    <!-- Title & Category -->
                    <h3 id="badgeModalTitle" class="text-2xl font-bold text-gray-900 mb-2">Badge Title</h3>
                    <span id="badgeModalCategory" class="inline-block px-3 py-1 rounded-full text-sm font-medium mb-5">Category</span>
                    
                    <!-- Description -->
                    <p id="badgeModalDescription" class="text-gray-600 mb-8 leading-relaxed">Description goes here...</p>
                    
                    <!-- Stats Grid - Clean White (No Border/Bg) -->
                    <div class="grid grid-cols-2 gap-4 mb-6 p-4">
                        <div class="text-center border-r border-gray-100">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">GANJARAN</p>
                            <p id="badgeModalXP" class="text-lg font-bold text-gray-900">0 XP</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">KEPERLUAN</p>
                            <p id="badgeModalRequired" class="text-lg font-bold text-gray-900">0 Mata</p>
                        </div>
                        <div class="col-span-2 border-t border-gray-100 pt-3 mt-1">
                             <div class="flex justify-between items-center text-xs text-gray-500 mb-1">
                                <span>Kemajuan</span>
                                <span id="badgeModalProgress" class="font-bold text-gray-700">0 / 0</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Button -->
                    <button id="badgeModalButton" type="button" class="w-full px-6 py-3 bg-gray-400 text-white rounded-lg font-semibold cursor-not-allowed" disabled>
                        Terkunci
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

