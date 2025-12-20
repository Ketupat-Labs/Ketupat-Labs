<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\AppLayout::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <?php echo e(__('Profile')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Profile Header -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-6 py-8">
                    <div class="flex items-start space-x-6">
                        <!-- Avatar -->
                        <div class="flex-shrink-0">
                            <div class="h-32 w-32 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
                                <?php if($profileUser->avatar_url): ?>
                                    <img src="<?php echo e(asset($profileUser->avatar_url)); ?>" alt="<?php echo e($profileUser->full_name); ?>" class="h-full w-full object-cover">
                                <?php else: ?>
                                    <span class="text-4xl font-bold text-gray-600">
                                        <?php echo e(strtoupper(substr($profileUser->full_name ?? $profileUser->username ?? 'U', 0, 1))); ?>

                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Profile Info -->
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h1 class="text-3xl font-bold text-gray-900"><?php echo e($profileUser->full_name ?? $profileUser->username); ?></h1>
                                    <?php if($profileUser->role): ?>
                                        <span class="inline-block mt-2 px-3 py-1 text-xs font-semibold rounded-full <?php echo e($profileUser->role === 'teacher' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'); ?>">
                                            <?php echo e($profileUser->role === 'teacher' ? 'Teacher' : 'Student'); ?>

                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex space-x-3">
                                    <?php if($isOwnProfile): ?>
                                        <a href="<?php echo e(route('profile.edit')); ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                            <i class="fas fa-edit mr-2"></i> Edit Profile
                                        </a>
                                    <?php else: ?>
                                        <?php if($isFriend): ?>
                                            <button onclick="removeFriend(<?php echo e($profileUser->id); ?>)" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                                                <i class="fas fa-user-minus mr-2"></i> Remove Friend
                                            </button>
                                        <?php elseif($hasPendingRequest): ?>
                                            <button class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg cursor-not-allowed" disabled>
                                                <i class="fas fa-clock mr-2"></i> Request Pending
                                            </button>
                                        <?php else: ?>
                                            <button onclick="addFriend(<?php echo e($profileUser->id); ?>)" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                                <i class="fas fa-user-plus mr-2"></i> Add Friend
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Profile Details -->
                            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php if($profileUser->school): ?>
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">School</span>
                                        <p class="text-gray-900"><?php echo e($profileUser->school); ?></p>
                                    </div>
                                <?php endif; ?>
                                <?php if($profileUser->class): ?>
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Class</span>
                                        <p class="text-gray-900"><?php echo e($profileUser->class); ?></p>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Friends</span>
                                    <p class="text-gray-900"><?php echo e($friendCount); ?></p>
                                </div>
                            </div>

                            <?php if($profileUser->bio): ?>
                                <div class="mt-6">
                                    <span class="text-sm font-medium text-gray-500">Bio</span>
                                    <p class="text-gray-900 mt-2"><?php echo e($profileUser->bio); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Tabs -->
            <div class="bg-white shadow rounded-lg">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px" aria-label="Tabs">
                        <?php if($isOwnProfile): ?>
                            <button onclick="showSection('saved')" id="tab-saved" class="section-tab active py-4 px-6 text-sm font-medium border-b-2 border-blue-500 text-blue-600">
                                <i class="fas fa-bookmark mr-2"></i>Saved Posts
                            </button>
                        <?php endif; ?>
                        <button onclick="showSection('posts')" id="tab-posts" class="section-tab <?php echo e($isOwnProfile ? '' : 'active'); ?> py-4 px-6 text-sm font-medium border-b-2 <?php echo e($isOwnProfile ? 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' : 'border-blue-500 text-blue-600'); ?>">
                            <i class="fas fa-comments mr-2"></i>Forum Posts
                        </button>
                        <button onclick="showSection('badges')" id="tab-badges" class="section-tab py-4 px-6 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            <i class="fas fa-trophy mr-2"></i>Badges
                        </button>
                    </nav>
                </div>

                <!-- Saved Posts Section -->
                <?php if($isOwnProfile): ?>
                    <div id="section-saved" class="section-content p-6">
                        <div id="savedPostsContent">
                            <div class="text-center py-8">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                                <p class="text-gray-500 mt-2">Loading saved posts...</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Forum Posts Section -->
                <div id="section-posts" class="section-content <?php echo e($isOwnProfile ? 'hidden' : ''); ?> p-6">
                    <?php if($posts->count() > 0): ?>
                        <div class="space-y-4">
                            <?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="border-b border-gray-200 pb-4 last:border-b-0">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <a href="<?php echo e(route('forum.post.detail', $post->id)); ?>" class="text-lg font-semibold text-blue-600 hover:text-blue-800">
                                                <?php echo e($post->title); ?>

                                            </a>
                                            <p class="text-sm text-gray-600 mt-1">
                                                in <span class="font-medium"><?php echo e($post->forum->title); ?></span>
                                                <span class="mx-2">•</span>
                                                <?php echo e($post->created_at->diffForHumans()); ?>

                                            </p>
                                            <?php if($post->content): ?>
                                                <div class="text-gray-700 mt-2 post-content-preview" data-content="<?php echo e(htmlspecialchars($post->content, ENT_QUOTES, 'UTF-8')); ?>">
                                                    <p class="line-clamp-2"><?php echo e(Str::limit(strip_tags($post->content), 150)); ?></p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-8">No posts yet</p>
                    <?php endif; ?>
                </div>

                <!-- Badges Section -->
                <div id="section-badges" class="section-content hidden p-6">
                    <!-- Badge Filters -->
                    <div class="mb-6 space-y-4">
                        <!-- Category Filter -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Kategori</label>
                            <div class="flex flex-wrap gap-2" id="profileCategoryFilters">
                                <button class="filter-btn active px-3 py-1 text-sm rounded-full border border-blue-500 bg-blue-500 text-white transition-colors" data-filter="all" data-type="category">Semua</button>
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <button class="filter-btn px-3 py-1 text-sm rounded-full border border-gray-300 text-gray-600 hover:border-gray-400 bg-white transition-colors" 
                                            data-filter="<?php echo e($cat->code); ?>" 
                                            data-type="category">
                                        <?php echo e($cat->name); ?>

                                    </button>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Status</label>
                            <div class="flex flex-wrap gap-2" id="profileStatusFilters">
                                <button class="filter-btn active px-3 py-1 text-sm rounded-full border border-blue-500 bg-blue-500 text-white transition-colors" data-filter="all" data-type="status">Semua</button>
                                <button class="filter-btn px-3 py-1 text-sm rounded-full border border-gray-300 text-gray-600 hover:border-gray-400 bg-white transition-colors" data-filter="earned" data-type="status">Tercapai</button>
                                <button class="filter-btn px-3 py-1 text-sm rounded-full border border-gray-300 text-gray-600 hover:border-gray-400 bg-white transition-colors" data-filter="progress" data-type="status">Dalam Progres</button>
                                <button class="filter-btn px-3 py-1 text-sm rounded-full border border-gray-300 text-gray-600 hover:border-gray-400 bg-white transition-colors" data-filter="locked" data-type="status">Terkunci</button>
                            </div>
                        </div>
                    </div>

                    <?php if($badges->count() > 0): ?>
                        <div class="mb-4">
                            <p class="text-sm text-gray-600">
                                <?php echo e(__('Menunjukkan :count lencana.', ['count' => $badges->count()])); ?> 
                                <span class="font-semibold text-blue-600"><?php echo e($badges->where('is_earned', true)->count()); ?> <?php echo e(__('tercapai')); ?></span>
                            </p>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="profileBadgesGrid">
                            <?php $__currentLoopData = $badges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $badge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $statusType = $badge->is_earned ? 'earned' : 'locked';
                                    // Simple logic for progress: if points > 0 but not earned (needs actual progress logic from backend eventually)
                                    if (!$badge->is_earned && isset($badge->progress) && $badge->progress > 0) {
                                        $statusType = 'progress';
                                    }
                                ?>
                                <div class="badge-item relative p-4 rounded-lg border transition-all duration-200 <?php echo e($badge->is_earned ? 'bg-gradient-to-br from-gray-50 to-gray-100 border-gray-200 hover:shadow-md' : 'bg-gray-50 border-gray-300 opacity-75'); ?>"
                                     data-category="<?php echo e($badge->category->code ?? 'uncategorized'); ?>"
                                     data-status-type="<?php echo e($statusType); ?>">
                                    
                                    <div class="flex flex-col items-center text-center">
                                        <!-- Badge Icon -->
                                        <div class="mb-3 relative">
                                            <?php if($badge->icon): ?>
                                                <div class="w-16 h-16 rounded-full flex items-center justify-center text-3xl transition-all duration-200 <?php echo e($badge->is_earned ? '' : 'grayscale brightness-75'); ?>" 
                                                     style="background: <?php echo e($badge->is_earned ? ($badge->color ?? '#3B82F6') : '#9CA3AF'); ?>20; color: <?php echo e($badge->is_earned ? ($badge->color ?? '#3B82F6') : '#6B7280'); ?>;">
                                                    <i class="<?php echo e($badge->icon); ?>"></i>
                                                </div>
                                            <?php else: ?>
                                                <div class="w-16 h-16 rounded-full flex items-center justify-center text-3xl transition-all duration-200 <?php echo e($badge->is_earned ? 'bg-blue-500 text-white' : 'bg-gray-400 text-gray-200'); ?>">
                                                    <i class="fas fa-trophy"></i>
                                                </div>
                                            <?php endif; ?>
                                            <?php if($badge->is_earned): ?>
                                                <div class="absolute -top-1 -right-1 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-check text-white text-xs"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Badge Name -->
                                        <h3 class="font-semibold mb-1 text-sm <?php echo e($badge->is_earned ? 'text-gray-900' : 'text-gray-500'); ?>"><?php echo e($badge->name); ?></h3>
                                        
                                        <!-- Badge Description -->
                                        <?php if($badge->description): ?>
                                            <p class="text-xs mb-2 line-clamp-2 <?php echo e($badge->is_earned ? 'text-gray-600' : 'text-gray-400'); ?>"><?php echo e(Str::limit($badge->description, 60)); ?></p>
                                        <?php endif; ?>
                                        
                                        <!-- Badge Category -->
                                        <?php if($badge->category): ?>
                                            <span class="inline-block px-2 py-1 text-xs rounded-full transition-all duration-200 <?php echo e($badge->is_earned ? '' : 'opacity-50'); ?>" 
                                                  style="background: <?php echo e($badge->is_earned ? ($badge->category->color ?? '#E5E7EB') : '#E5E7EB'); ?>20; color: <?php echo e($badge->is_earned ? ($badge->category->color ?? '#6B7280') : '#9CA3AF'); ?>;">
                                                <?php echo e($badge->category->name); ?>

                                            </span>
                                        <?php endif; ?>
                                        
                                        <!-- XP Reward -->
                                        <?php if($badge->xp_reward > 0): ?>
                                            <div class="mt-2 text-xs <?php echo e($badge->is_earned ? 'text-gray-500' : 'text-gray-400'); ?>">
                                                <i class="fas fa-star <?php echo e($badge->is_earned ? 'text-yellow-500' : 'text-gray-400'); ?>"></i> +<?php echo e($badge->xp_reward); ?> XP
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Locked/Unearned Indicator -->
                                        <?php if(!$badge->is_earned): ?>
                                            <div class="mt-2 text-xs text-gray-400 italic">
                                                <i class="fas fa-lock"></i> <?php echo e(__('Terkunci')); ?>

                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <div class="inline-block p-4 bg-gray-100 rounded-full mb-4">
                                <i class="fas fa-trophy text-gray-400 text-4xl"></i>
                            </div>
                            <p class="text-gray-500 text-lg font-medium"><?php echo e(__('Tiada lencana tersedia')); ?></p>
                            <p class="text-gray-400 text-sm mt-2"><?php echo e(__('Lencana akan muncul di sini sebaik sahaja ia ditambah ke dalam sistem.')); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Forum CSS for post styling -->
    <link rel="stylesheet" href="<?php echo e(asset('Forum/CSS/forum.css')); ?>">
    
    <!-- Override forum.css global styles that affect the profile page -->
    <style>
        /* Reset forum.css global body styles that conflict with profile page */
        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Roboto', sans-serif !important;
            background-color: #f9fafb !important;
            color: #1f2937 !important;
            line-height: 1.5 !important;
        }
        
        /* Ensure the main layout structure is correct */
        .min-h-screen {
            display: block !important;
        }
        
        .min-h-screen > div {
            display: block !important;
        }
        
        /* Ensure main content area is not affected by forum flex layouts */
        main {
            display: block !important;
            width: 100% !important;
        }
        
        /* Hide any sidebar elements that forum.css might create (only allow in saved posts) */
        aside.reddit-sidebar:not(#savedPostsContent aside),
        .reddit-sidebar:not(#savedPostsContent .reddit-sidebar),
        .reddit-sidebar-right:not(#savedPostsContent .reddit-sidebar-right),
        .reddit-container:not(#savedPostsContent .reddit-container),
        .reddit-main:not(#savedPostsContent .reddit-main) {
            display: none !important;
        }
        
        /* Ensure profile page content container is properly sized */
        .max-w-7xl {
            width: 100% !important;
            max-width: 1280px !important;
            margin-left: auto !important;
            margin-right: auto !important;
            padding-left: 1.5rem !important;
            padding-right: 1.5rem !important;
        }
        
        /* Ensure navigation stays as top bar, not sidebar */
        nav.bg-white {
            position: relative !important;
            display: block !important;
            width: 100% !important;
        }
        
        /* Prevent forum.css from creating flex layouts that break profile page */
        .min-h-screen,
        .min-h-screen > div,
        main {
            flex-direction: column !important;
        }
        
        /* Ensure responsive menu is hidden on desktop */
        @media (min-width: 1024px) {
            nav .hidden.lg\\:hidden {
                display: none !important;
            }
        }
    </style>
    
    <!-- Include Forum JS for post rendering functions -->
    <script src="<?php echo e(asset('Forum/JS/forum.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/profile.js')); ?>"></script>
    
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
                                <p class="text-gray-500 text-lg">No saved posts yet</p>
                                <p class="text-gray-400 text-sm mt-2">Posts you save will appear here</p>
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
                        <p class="text-gray-500 text-lg">No saved posts yet</p>
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
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>

<?php /**PATH C:\xampp\htdocs\Material\resources\views/profile/show.blade.php ENDPATH**/ ?>