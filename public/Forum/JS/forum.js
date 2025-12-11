const forumState = {
    currentForumId: null,
    forums: [],
    posts: [],
    currentPost: null,
    filters: {
        tag: null,
        search: '',
        sort: 'recent'
    }
};

// Helper function to normalize file URLs
function normalizeFileUrl(url) {
    if (!url) return '';
    
    // If URL is already absolute (starts with / or http), return as-is
    if (url.startsWith('/') || url.startsWith('http://') || url.startsWith('https://')) {
        return url;
    }
    
    // If URL is relative, make it absolute from web root
    // For Laravel, URLs should start with /storage/ or be absolute
    // If it's a storage path, ensure it starts with /storage/
    if (url.includes('storage/')) {
        return url.startsWith('/') ? url : '/' + url;
    }
    
    // Otherwise, treat as relative to root
    return url.startsWith('/') ? url : '/' + url;
}

document.addEventListener('DOMContentLoaded', async () => {
    if (sessionStorage.getItem('userLoggedIn') !== 'true') {
        window.location.href = 'login.html';
        return;
    }

    initEventListeners();
    await loadForumsToSidebar();
    loadAllPosts();
    loadRecentPosts();
});

function initEventListeners() {
    const btnCreateForum = document.getElementById('btnCreateForum');
    if (btnCreateForum) {
        btnCreateForum.addEventListener('click', () => {
            window.location.href = '/forum/create';
        });
    }

    const logoutBtn = document.getElementById('btnLogout');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async () => {
            try {
                await fetch('logout.php', {
                    method: 'POST',
                    credentials: 'include'
                });
            } catch (error) {
                console.error('Logout error:', error);
            }

            sessionStorage.removeItem('userLoggedIn');
            sessionStorage.removeItem('userEmail');
            sessionStorage.removeItem('userId');
            window.location.href = '/login';
        });
    }

    // Keep modal functionality for any existing modals
    const closeForumModal = document.getElementById('closeForumModal');
    if (closeForumModal) {
        closeForumModal.addEventListener('click', closeCreateForumModal);
    }

    const cancelForumModal = document.getElementById('cancelForumModal');
    if (cancelForumModal) {
        cancelForumModal.addEventListener('click', closeCreateForumModal);
    }

    const createForumForm = document.getElementById('createForumForm');
    if (createForumForm) {
        createForumForm.addEventListener('submit', createForum);
    }

        const btnCreatePost = document.getElementById('btnCreatePost');
        if (btnCreatePost) {
            btnCreatePost.addEventListener('click', () => {
                window.location.href = '/forum/post/create';
            });
        }

    const searchForums = document.getElementById('searchForums');
    if (searchForums) {
        searchForums.addEventListener('input', (e) => {
            forumState.filters.search = e.target.value;
            loadAllPosts();
        });

        searchForums.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const searchTerm = e.target.value.trim();
                if (searchTerm) {
                    window.location.href = `/forum/search?q=${encodeURIComponent(searchTerm)}`;
                }
            }
        });
    }

    // Handle mobile search input
    const searchForumsMobile = document.getElementById('searchForumsMobile');
    if (searchForumsMobile) {
        searchForumsMobile.addEventListener('input', (e) => {
            forumState.filters.search = e.target.value;
            loadAllPosts();
        });

        searchForumsMobile.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const searchTerm = e.target.value.trim();
                if (searchTerm) {
                    window.location.href = `/forum/search?q=${encodeURIComponent(searchTerm)}`;
                }
            }
        });
    }

    const sortPosts = document.getElementById('sortPosts');
    if (sortPosts) {
        sortPosts.addEventListener('change', (e) => {
            forumState.filters.sort = e.target.value;
            loadAllPosts();
        });
    }

    const closePostModal = document.getElementById('closePostModal');
    if (closePostModal) {
        closePostModal.addEventListener('click', closePostModal);
    }
}

async function loadForumsToSidebar() {
    try {
        const response = await fetch(`/api/forum`, {
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

        if (data.status === 200) {
            forumState.forums = data.data.forums || data.data || [];
            renderForumsToSidebar();
        } else {
            console.error('Error loading forums:', data.message);
        }
    } catch (error) {
        console.error('Error loading forums:', error);
    }
}

function renderForumsToSidebar() {
    const container = document.getElementById('forumsList');

    if (forumState.forums.length === 0) {
        container.innerHTML = `
            <p style="padding: 12px; color: #878a8c; font-size: 14px;">
                No forums yet. Create one to get started!
            </p>
        `;
        return;
    }

    container.innerHTML = forumState.forums.map(forum => `
        <div class="filter-item" 
             onclick="window.location.href='/forum/${forum.id}'">
            <i class="fas fa-comments" style="color: #878a8c; margin-right: 8px;"></i>
            <span>${escapeHtml(forum.title)}</span>
            ${forum.unread_count > 0 ? `<span style="background: #ff4500; color: white; padding: 2px 6px; border-radius: 10px; font-size: 11px; margin-left: auto;">${forum.unread_count}</span>` : ''}
        </div>
    `).join('');
}

async function loadAllPosts() {
    try {
        const response = await fetch(`/api/forum/post`, {
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

        if (data.status === 200) {
            forumState.posts = data.data.posts || data.data || [];
            renderPosts(forumState.posts);
            // Load tags dynamically from API
            loadPopularTags();
        } else {
            showError(data.message || 'Failed to load posts');
        }
    } catch (error) {
        console.error('Error loading posts:', error);
        showError('Failed to load posts');
    }
}

function filterByForum(forumId) {
    forumState.currentForumId = forumId;
    renderForumsToSidebar();
    if (forumId === null) {
        loadAllPosts();
    } else {
        loadPostsForForum(forumId);
    }
}

async function loadPostsForForum(forumId) {
    try {
        // Get posts for this specific forum
        const response = await fetch(`/api/forum/post?forum_id=${forumId}`, {
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
        
        const postsData = await response.json();

        if (postsData.status === 200) {
            // The API returns { status: 200, data: { posts: [...] } }
            forumState.posts = postsData.data?.posts || postsData.data || [];
            renderPosts(forumState.posts);
            loadPopularTags();
        } else {
            showError(postsData.message || 'Failed to load posts');
        }
    } catch (error) {
        console.error('Error loading posts:', error);
        showError('Failed to load posts');
    }
}

function renderPosts(posts) {
    const container = document.getElementById('forumsContent');

    if (posts.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-edit"></i>
                <h3>Tiada post lagi</h3>
                <p>Jadilah yang pertama mencipta post!</p>
            </div>
        `;
        return;
    }

    container.innerHTML = `
        <div class="reddit-content">
            ${posts.map(post => `
                <div class="reddit-post-card" onclick="openPost(${post.id})">
                    <div class="post-content-section">
                        <div class="post-header">
                            <div class="post-header-left">
                                <span class="post-community">${escapeHtml(post.forum_name || 'Forum')}</span>
                                <span class="post-time">•</span>
                                <span class="post-time">${formatTime(post.created_at)}</span>
                                ${post.is_pinned ? '<span class="post-pinned"><i class="fas fa-thumbtack"></i> disematkan</span>' : ''}
                            </div>
                            ${post.user_forum_role ? `
                            <div class="post-options-container" onclick="event.stopPropagation();">
                                <button class="post-options-btn" onclick="event.stopPropagation(); togglePostOptions(${post.id})" title="More options">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                                <div id="postOptions_${post.id}" class="post-options-menu hidden">
                                    <button class="post-options-item" onclick="event.stopPropagation(); openShareModal(${post.id}, '${escapeHtml(post.title)}', '${escapeHtml(post.forum_name || 'Forum')}')">
                                        <i class="fas fa-share"></i> Share
                                    </button>
                                    ${post.is_forum_member && parseInt(getCurrentUserId()) !== parseInt(post.author_id) ? `
                                    <button class="post-options-item report-option" onclick="event.stopPropagation(); openReportModal(${post.id}, '${escapeHtml(post.title)}')">
                                        <i class="fas fa-flag"></i> Report
                                    </button>
                                    ` : ''}
                                    ${(parseInt(getCurrentUserId()) === parseInt(post.author_id) || (post.user_forum_role && ['admin', 'moderator'].includes(post.user_forum_role))) ? `
                                    <button class="post-options-item delete-option" onclick="event.stopPropagation(); confirmDeletePost(${post.id})">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                    ${post.user_forum_role && ['admin', 'moderator'].includes(post.user_forum_role) ? `
                                    <button class="post-options-item" onclick="event.stopPropagation(); toggleHidePost(${post.id}, ${post.is_hidden ? 'false' : 'true'})">
                                        <i class="fas fa-${post.is_hidden ? 'eye' : 'eye-slash'}"></i> ${post.is_hidden ? 'Unhide' : 'Hide'}
                                    </button>
                                    ` : ''}
                                    ` : ''}
                                </div>
                            </div>
                            ` : ''}
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
                            ${post.content ? escapeHtml(post.content.substring(0, 200)) + (post.content.length > 200 ? '...' : '') : ''}
                        </div>
                        `}
                        ${post.attachments ? `
                            <div style="margin-top: 12px;">
                                ${(() => {
                try {
                    const attachments = typeof post.attachments === 'string'
                        ? JSON.parse(post.attachments)
                        : post.attachments;
                    if (!Array.isArray(attachments)) return '';

                    const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    const videoExts = ['mp4', 'webm', 'ogg'];
                    const mediaAttachments = [];
                    const otherAttachments = [];

                    attachments.forEach(att => {
                        const ext = att.name.split('.').pop().toLowerCase();
                        if (imageExts.includes(ext)) {
                            mediaAttachments.push({ ...att, type: 'image' });
                        } else if (videoExts.includes(ext)) {
                            mediaAttachments.push({ ...att, type: 'video' });
                        } else {
                            otherAttachments.push(att);
                        }
                    });

                    let html = '';

                    // Show media previews (Images & Videos)
                    if (mediaAttachments.length > 0) {
                        // If single media item, show full width
                        if (mediaAttachments.length === 1) {
                            const media = mediaAttachments[0];
                            const normalizedUrl = normalizeFileUrl(media.url);
                            html += '<div class="post-media-container" onclick="event.stopPropagation();">';
                            if (media.type === 'image') {
                                html += `<img src="${normalizedUrl}" alt="${escapeHtml(media.name)}" class="post-media-image" onclick="window.open('${normalizedUrl}', '_blank');" onerror="this.style.display='none'; console.error('Failed to load image:', '${normalizedUrl}');">`;
                            } else {
                                html += `
                                                        <video class="post-media-video" controls preload="metadata">
                                                            <source src="${normalizedUrl}" type="video/${media.name.split('.').pop().toLowerCase()}">
                                                            Your browser does not support the video tag.
                                                        </video>
                                                    `;
                            }
                            html += '</div>';
                        } else {
                            // Multiple media items - Grid layout
                            html += '<div class="post-media-grid" onclick="event.stopPropagation();">';
                            mediaAttachments.forEach(media => {
                                const normalizedUrl = normalizeFileUrl(media.url);
                                html += '<div class="post-media-grid-item">';
                                if (media.type === 'image') {
                                    html += `<img src="${normalizedUrl}" alt="${escapeHtml(media.name)}" onclick="window.open('${normalizedUrl}', '_blank');" onerror="this.style.display='none'; console.error('Failed to load image:', '${normalizedUrl}');">`;
                                } else {
                                    // For videos in grid, show a video tag but maybe muted/small or just a placeholder if we had one. 
                                    // For now, let's just put the video tag, it might be heavy but it works.
                                    html += `
                                                            <video style="width: 100%; height: 100%; object-fit: cover;" controls preload="metadata">
                                                                <source src="${normalizedUrl}" type="video/${media.name.split('.').pop().toLowerCase()}">
                                                            </video>
                                                        `;
                                }
                                html += '</div>';
                            });
                            html += '</div>';
                        }
                    }

                    // Show other file attachments as links
                    if (otherAttachments.length > 0) {
                        html += '<div style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 8px;">';
                        otherAttachments.forEach(att => {
                            html += `
                                                    <a href="${att.url}" target="_blank" class="attachment-file" onclick="event.stopPropagation();">
                                                        <i class="fas ${getFileIcon(att.name)}"></i>
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
                        ${post.tags ? `
                            <div class="post-tags" style="margin-top: 8px;">
                                ${(() => {
                try {
                    const tags = JSON.parse(post.tags);
                    return Array.isArray(tags) ? tags : [];
                } catch (e) {
                    return [];
                }
            })().map(tag => `
                                    <span class="post-tag">#${escapeHtml(tag)}</span>
                                `).join('')}
                            </div>
                        ` : ''}
                        <div class="post-footer">
                            <button class="post-footer-btn vote-btn-inline ${post.user_reacted ? 'active' : ''}" onclick="event.stopPropagation(); toggleReaction(${post.id})">
                                <i class="${post.user_reacted ? 'fas' : 'far'} fa-heart"></i>
                                ${post.reaction_count || 0}
                            </button>
                            <button class="post-footer-btn" onclick="event.stopPropagation(); openPost(${post.id})">
                                <i class="far fa-comment"></i>
                                ${post.reply_count || 0}
                            </button>
                            <button class="post-footer-btn ${post.is_bookmarked ? 'active' : ''}" onclick="event.stopPropagation(); toggleBookmark(${post.id})">
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

async function openPost(postId) {
    // Track visited post
    trackVisitedPost(postId);
    // Use Laravel route for post detail
    window.location.href = `/forum/post/${postId}`;
}

function trackVisitedPost(postId) {
    try {
        // Convert to number to ensure consistent type
        const postIdNum = parseInt(postId);
        if (isNaN(postIdNum)) return;

        let recentPosts = JSON.parse(localStorage.getItem('recentPosts') || '[]');

        // Remove if already exists (check both string and number to handle type inconsistencies)
        recentPosts = recentPosts.filter(p => parseInt(p.id) !== postIdNum);

        // Add to beginning
        recentPosts.unshift({
            id: postIdNum,
            visitedAt: new Date().toISOString()
        });

        // Keep only last 10
        recentPosts = recentPosts.slice(0, 10);

        localStorage.setItem('recentPosts', JSON.stringify(recentPosts));

        // If we're on the forum page, update the sidebar
        if (document.getElementById('recentPostsList')) {
            loadRecentPosts();
        }
    } catch (error) {
        console.error('Error tracking visited post:', error);
    }
}

async function loadRecentPosts() {
    const container = document.getElementById('recentPostsList');
    if (!container) return;

    try {
        let recentPostIds = JSON.parse(localStorage.getItem('recentPosts') || '[]');

        // Clean up duplicates in localStorage (keep first occurrence)
        const seenIds = new Set();
        recentPostIds = recentPostIds.filter(p => {
            const id = parseInt(p.id);
            if (seenIds.has(id)) {
                return false; // Remove duplicate
            }
            seenIds.add(id);
            return true;
        });

        // Save cleaned data back to localStorage
        if (recentPostIds.length !== JSON.parse(localStorage.getItem('recentPosts') || '[]').length) {
            localStorage.setItem('recentPosts', JSON.stringify(recentPostIds));
        }

        if (recentPostIds.length === 0) {
            container.innerHTML = '<p style="padding: 16px; color: #878a8c; font-size: 12px; text-align: center;">No recent posts</p>';
            return;
        }

        // Fetch post details for recent posts
        const postIds = recentPostIds.map(p => p.id).join(',');
        // Get recent posts from Laravel API
        const response = await fetch(`/api/forum/post?post_ids=${postIds}`, {
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
            // Sort by visit order and remove duplicates
            const postsMap = {};
            data.data.posts.forEach(post => {
                const postId = parseInt(post.id);
                // Only keep first occurrence if duplicate
                if (!postsMap[postId]) {
                    postsMap[postId] = post;
                }
            });

            // Track seen IDs to prevent duplicates
            const seenIds = new Set();
            const sortedPosts = recentPostIds
                .map(recent => {
                    const recentId = parseInt(recent.id);
                    if (seenIds.has(recentId)) {
                        return null; // Skip duplicates
                    }
                    seenIds.add(recentId);
                    return postsMap[recentId];
                })
                .filter(post => post !== undefined && post !== null)
                .slice(0, 5); // Limit to top 5 most recent

            renderRecentPosts(sortedPosts);
        } else {
            container.innerHTML = '<p style="padding: 16px; color: #878a8c; font-size: 12px; text-align: center;">No recent posts</p>';
        }
    } catch (error) {
        console.error('Error loading recent posts:', error);
        container.innerHTML = '<p style="padding: 16px; color: #878a8c; font-size: 12px; text-align: center;">Error loading recent posts</p>';
    }
}

function renderRecentPosts(posts) {
    const container = document.getElementById('recentPostsList');
    if (!container || posts.length === 0) {
        container.innerHTML = '<p style="padding: 16px; color: #878a8c; font-size: 12px; text-align: center;">No recent posts</p>';
        return;
    }

    container.innerHTML = posts.map((post, index) => {
        const forumInitials = (post.forum_name || 'Forum').split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
        const timeAgo = formatTime(post.created_at);

        // Check for image thumbnail
        let thumbnail = '';
        if (post.attachments) {
            try {
                const attachments = typeof post.attachments === 'string'
                    ? JSON.parse(post.attachments)
                    : post.attachments;
                if (Array.isArray(attachments)) {
                    const imageAtt = attachments.find(att => {
                        const ext = att.name.split('.').pop().toLowerCase();
                        return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
                    });
                    if (imageAtt) {
                        thumbnail = `<img src="${imageAtt.url}" alt="Thumbnail" class="recent-post-thumbnail">`;
                    }
                }
            } catch (e) {
                // Ignore
            }
        }

        return `
            <div class="recent-post-item" onclick="openPost(${post.id})">
                <div class="recent-post-content">
                    <div class="recent-post-header">
                        <div class="recent-post-forum-icon">${forumInitials}</div>
                        <div class="recent-post-meta">
                            <span class="recent-post-forum">${escapeHtml(post.forum_name || 'Forum')}</span>
                            <span class="recent-post-time">${timeAgo}</span>
                        </div>
                    </div>
                    <div class="recent-post-title">${escapeHtml(post.title)}</div>
                    <div class="recent-post-stats">
                        <span class="recent-post-stat">
                            <i class="fas fa-heart"></i>
                            ${post.reaction_count || 0}
                        </span>
                        <span class="recent-post-stat">
                            <i class="far fa-comment"></i>
                            ${post.reply_count || 0}
                        </span>
                    </div>
                </div>
                ${thumbnail ? `<div class="recent-post-thumbnail-container">${thumbnail}</div>` : ''}
            </div>
        `;
    }).join('');
}

function clearRecentPosts() {
    if (confirm('Clear all recent posts?')) {
        localStorage.removeItem('recentPosts');
        const container = document.getElementById('recentPostsList');
        if (container) {
            container.innerHTML = '<p style="padding: 16px; color: #878a8c; font-size: 12px; text-align: center;">No recent posts</p>';
        }
    }
}

function renderPostDetail(post) {
    const container = document.getElementById('postDetailContent');

    container.innerHTML = `
        <div class="post-detail">
            <div class="post-detail-header">
                <div class="post-header">
                    ${post.is_pinned ? '<span class="post-pinned"><i class="fas fa-thumbtack"></i> pinned</span>' : ''}
                    <span class="post-author">u/${escapeHtml(post.author_name || post.author_username)}</span>
                    <span class="post-time"><i class="fas fa-clock"></i> ${formatTime(post.created_at)}</span>
                    ${post.is_edited ? '<span class="post-time">(edited)</span>' : ''}
                </div>
                <div class="post-detail-title">
                    ${escapeHtml(post.title)}
                </div>
            </div>
            
            <div class="post-detail-content">
                ${escapeHtml(post.content)}
            </div>
            
            ${post.attachments ? `
                <div style="margin-bottom: 20px;">
                    <strong>Attachments:</strong>
                    ${(() => {
                try {
                    const attachments = typeof post.attachments === 'string'
                        ? JSON.parse(post.attachments)
                        : post.attachments;
                    if (!Array.isArray(attachments)) return '';

                    const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    const videoExts = ['mp4', 'webm', 'ogg'];
                    const mediaAttachments = [];
                    const otherAttachments = [];

                    attachments.forEach(att => {
                        const ext = att.name.split('.').pop().toLowerCase();
                        if (imageExts.includes(ext)) {
                            mediaAttachments.push({ ...att, type: 'image' });
                        } else if (videoExts.includes(ext)) {
                            mediaAttachments.push({ ...att, type: 'video' });
                        } else {
                            otherAttachments.push(att);
                        }
                    });

                    let html = '';

                    // Show media previews (Images & Videos)
                    if (mediaAttachments.length > 0) {
                        html += '<div style="margin-top: 12px; display: flex; flex-direction: column; gap: 16px;">';
                        mediaAttachments.forEach(media => {
                            if (media.type === 'image') {
                                html += `
                                            <div class="post-media-container" style="background: transparent; border: 1px solid #edeff1;">
                                                <img src="${media.url}" alt="${escapeHtml(media.name)}" class="post-media-image" style="cursor: pointer;" onclick="window.open('${media.url}', '_blank');">
                                            </div>
                                        `;
                            } else {
                                html += `
                                            <div class="post-media-container" style="background: black;">
                                                <video class="post-media-video" controls preload="metadata">
                                                    <source src="${media.url}" type="video/${media.name.split('.').pop().toLowerCase()}">
                                                    Your browser does not support the video tag.
                                                </video>
                                            </div>
                                        `;
                            }
                        });
                        html += '</div>';
                    }

                    // Show other file attachments as links
                    if (otherAttachments.length > 0) {
                        html += '<div style="margin-top: 12px;">';
                        otherAttachments.forEach(att => {
                            html += `
                                        <a href="${att.url}" target="_blank" class="attachment-file" style="display: inline-flex; margin-right: 10px; margin-bottom: 8px;">
                                            <i class="fas ${getFileIcon(att.name)}"></i>
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
            
            ${post.tags ? `
                <div class="post-detail-tags">
                    ${(() => {
                try {
                    const tags = JSON.parse(post.tags);
                    return Array.isArray(tags) ? tags : [];
                } catch (e) {
                    return [];
                }
            })().map(tag => `
                        <span class="post-tag">#${escapeHtml(tag)}</span>
                    `).join('')}
                </div>
            ` : ''}
            
            <div class="post-detail-actions">
                <button class="btn-action ${post.user_reacted ? 'liked' : ''}" onclick="toggleReaction(${post.id})">
                    <i class="far fa-heart"></i>
                    Like (${post.reaction_count || 0})
                </button>
                <button class="btn-action ${post.is_bookmarked ? 'liked' : ''}" onclick="toggleBookmark(${post.id})">
                    <i class="${post.is_bookmarked ? 'fas' : 'far'} fa-bookmark"></i>
                    Bookmark
                </button>
                ${getCurrentUserId() === post.author_id ? `
                    <button class="btn-action" onclick="editPost(${post.id})">
                        <i class="far fa-edit"></i>
                        Edit
                    </button>
                    <button class="btn-action" onclick="deletePost(${post.id})">
                        <i class="far fa-trash-alt"></i>
                        Delete
                    </button>
                ` : ''}
            </div>
            
            <div class="comments-section">
                <div class="comments-header">
                    <i class="far fa-comment"></i> Comments (${post.reply_count || 0})
                </div>
                <div id="commentsContainer">
                </div>
                <form id="commentForm" onsubmit="submitComment(event)">
                    <div style="display: flex; gap: 10px;">
                        <input type="text" id="commentInput" placeholder="Write a comment..." style="flex: 1; padding: 10px 12px; border: 1px solid #edeff1; border-radius: 4px;" required>
                        <button type="submit" class="btn-primary">Post</button>
                    </div>
                </form>
            </div>
        </div>
    `;

    loadComments(post.id);
}

async function loadComments(postId) {
    try {
        const response = await fetch(`/api/forum/post/${postId}/comments`, {
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

        if (data.status === 200) {
            renderComments(data.data.comments);
        }
    } catch (error) {
        console.error('Error loading comments:', error);
    }
}

function renderComments(comments) {
    const container = document.getElementById('commentsContainer');

    if (!comments || comments.length === 0) {
        container.innerHTML = '<p style="color: #878a8c; text-align: center; padding: 20px;">No comments yet</p>';
        return;
    }

    // Sort comments by reaction_count + reply_count for top comments
    const sortedComments = [...comments].sort((a, b) => {
        const scoreA = (a.reaction_count || 0) + (a.reply_count || 0);
        const scoreB = (b.reaction_count || 0) + (b.reply_count || 0);
        return scoreB - scoreA;
    });

    container.innerHTML = sortedComments.map((comment, index) => renderCommentItemForum(comment, 0, forumState.currentPost, index === sortedComments.length - 1)).join('');
}

let collapsedCommentsForum = new Set();

function toggleCommentCollapseForum(commentId, postId) {
    if (collapsedCommentsForum.has(commentId)) {
        collapsedCommentsForum.delete(commentId);
    } else {
        collapsedCommentsForum.add(commentId);
    }
    loadComments(postId);
}

function renderCommentItemForum(comment, depth = 0, postId, isLastChild = false) {
    const totalScore = (comment.reaction_count || 0) + (comment.reply_count || 0);
    const hasHighScore = totalScore > 0 && depth === 0;
    const isCollapsed = collapsedCommentsForum.has(comment.id);
    const hasReplies = comment.replies && comment.replies.length > 0;
    const indentAmount = depth * 40;
    const buttonSize = 18;
    const isLastInThread = isLastChild && !hasReplies && depth > 0;

    return `
        <div class="comment-thread" data-comment-id="${comment.id}" style="position: relative; padding-left: ${depth > 0 ? indentAmount + 32 : 0}px; padding-bottom: ${isLastInThread ? '0' : '8px'}; margin-bottom: ${depth === 0 ? '16px' : '0'};">
            <!-- Vertical line from parent's button to this comment's button -->
            ${depth > 0 ? `
                <div style="position: absolute; left: ${indentAmount - 40 + 16}px; top: -8px; width: 2px; background: #d1d5db; z-index: 0; height: ${buttonSize / 2 + 8}px;"></div>
            ` : ''}
            
            <!-- Collapse button container (only for nested comments) -->
            ${depth > 0 ? `
                <div style="position: absolute; left: ${indentAmount + 7}px; top: 0px; z-index: 2;">
                    <button onclick="toggleCommentCollapseForum(${comment.id}, ${postId})" style="width: ${buttonSize}px; height: ${buttonSize}px; border-radius: 50%; border: 1px solid #d1d5db; background: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 0; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                        ${isCollapsed ? `
                            <i class="fas fa-plus" style="font-size: 9px; color: #6b7280;"></i>
                        ` : `
                            <i class="fas fa-minus" style="font-size: 9px; color: #6b7280;"></i>
                        `}
                    </button>
                    
                    <!-- Vertical line from button down to children -->
                    ${hasReplies && !isCollapsed ? `
                        <div style="position: absolute; left: 50%; top: ${buttonSize}px; bottom: ${isLastInThread ? 'auto' : '-8px'}; width: 2px; background: #d1d5db; transform: translateX(-50%); z-index: 0; height: ${isLastInThread ? '8px' : '100%'};"></div>
                    ` : ''}
                </div>
            ` : ''}
            
            ${isCollapsed && depth > 0 ? `
                <div style="padding: 4px 0; margin-left: ${depth > 0 ? '12px' : '0'};">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div class="comment-author-info" style="display: flex; align-items: center; gap: 8px;">
                            <span class="comment-author-name" style="font-size: 13px; font-weight: 600; color: #9ca3af;">
                                u/${escapeHtml(comment.author_name || comment.author_username)}
                            </span>
                            <span style="font-size: 12px; color: #9ca3af;">• ${comment.reply_count || 0} ${comment.reply_count === 1 ? 'reply' : 'replies'}</span>
                        </div>
                    </div>
                </div>
            ` : `
                <div class="comment-item" data-comment-id="${comment.id}" style="${hasHighScore && depth === 0 ? 'border-left: 3px solid #ff4500; padding-left: 12px;' : ''} padding: ${depth > 0 ? '4px 0' : '0'}; margin-left: ${depth > 0 ? '12px' : '0'};">
            <div class="comment-header">
                <div class="comment-author">
                            <div class="comment-author-avatar" style="${depth > 0 ? 'width: 28px; height: 28px; font-size: 11px;' : ''}">
                        ${comment.author_avatar ?
            `<img src="${comment.author_avatar}" alt="${comment.author_username}" style="width: 100%; height: 100%; border-radius: 50%;">` :
            (comment.author_username ? comment.author_username.charAt(0).toUpperCase() : '?')
        }
                    </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; ${depth > 0 ? 'font-size: 13px;' : ''} color: #1c1c1c;">u/${escapeHtml(comment.author_name || comment.author_username)}</div>
                                <div style="font-size: 12px; color: #6b7280; display: flex; align-items: center; gap: 4px;">
                                    <span>•</span>
                                    <span>${formatTime(comment.created_at)}</span>
                    </div>
                            </div>
                            ${hasHighScore ? `
                                <div style="display: flex; gap: 12px; align-items: center; font-size: 12px; color: #878a8c;">
                                    <span title="Likes" style="display: flex; align-items: center; gap: 4px;">
                                        <i class="fas fa-heart" style="color: #ff4500; font-size: 11px;"></i>
                                        <span>${comment.reaction_count || 0}</span>
                                    </span>
                                    ${comment.reply_count > 0 ? `
                                        <span title="Replies" style="display: flex; align-items: center; gap: 4px;">
                                            <i class="fas fa-comments" style="font-size: 11px;"></i>
                                            <span>${comment.reply_count}</span>
                                        </span>
                                    ` : ''}
                                </div>
                            ` : comment.reaction_count > 0 ? `
                                <span style="font-size: 12px; color: #6b7280; display: flex; align-items: center; gap: 4px;" title="Likes">
                                    <i class="fas fa-heart" style="color: #ff4500; font-size: 11px;"></i>
                                    <span>${comment.reaction_count}</span>
                                </span>
                            ` : ''}
                </div>
            </div>
                    <div class="comment-content" style="${depth > 0 ? 'font-size: 14px;' : 'font-size: 15px;'} margin-left: ${depth > 0 ? '36px' : '0'}; margin-top: 4px; color: #1c1c1c; word-wrap: break-word; overflow-wrap: break-word; line-height: 1.6;">
                ${escapeHtml(comment.content)}
                        ${comment.is_edited ? '<span style="font-style: italic; color: #6b7280; margin-left: 4px; font-size: 12px;">(edited)</span>' : ''}
                    </div>
                    
                    <!-- Reply Form (hidden by default) -->
                    <div class="reply-form-container" id="replyForm_${comment.id}" style="display: none; margin-top: 12px; margin-left: ${depth > 0 ? '36px' : '0'}; padding: 12px; background: #f7f9fa; border-radius: 4px;">
                        <div style="display: flex; gap: 8px;">
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: #ff4500; color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600;">
                                ${(sessionStorage.getItem('userEmail') || 'U').charAt(0).toUpperCase()}
            </div>
                            <div style="flex: 1;">
                                <textarea id="replyInput_${comment.id}" placeholder="Write a reply..." style="width: 100%; min-height: 60px; padding: 8px; border: 1px solid #edeff1; border-radius: 4px; resize: vertical;" required></textarea>
                                <div style="display: flex; gap: 8px; margin-top: 8px;">
                                    <button type="button" onclick="submitReplyForum(${comment.id}, ${postId})" style="padding: 8px 16px; background: #ff4500; color: white; border: none; border-radius: 4px; cursor: pointer;">Reply</button>
                                    <button type="button" onclick="cancelReplyForum(${comment.id})" style="padding: 8px 16px; background: #e4e6eb; color: #1c1c1c; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                                </div>
                                </div>
                            </div>
                            </div>
                        </div>
                
                ${hasReplies && !isCollapsed ? `
                    <div style="margin-top: 12px;">
                        ${comment.replies.map((reply, index) => renderCommentItemForum(reply, depth + 1, postId, index === comment.replies.length - 1)).join('')}
                </div>
            ` : ''}
                
                <div style="margin-top: 8px; margin-left: ${depth > 0 ? '36px' : '0'}; display: flex; gap: 16px;">
                    <button class="post-footer-btn" onclick="toggleCommentLikeForum(${comment.id}, ${postId})" style="background: transparent; border: none; color: #6b7280; cursor: pointer; font-size: 13px; padding: 4px 8px; display: flex; align-items: center; gap: 4px;">
                        <i class="far fa-heart"></i> Like
                    </button>
                    <button class="post-footer-btn" onclick="toggleReplyFormForum(${comment.id})" style="background: transparent; border: none; color: #6b7280; cursor: pointer; font-size: 13px; padding: 4px 8px; display: flex; align-items: center; gap: 4px;">
                        <i class="far fa-comment"></i> Reply
                    </button>
                </div>
            `}
        </div>
    `;
}

async function createForum(event) {
    event.preventDefault();

    const title = document.getElementById('forumTitle').value;
    const description = document.getElementById('forumDescription').value;
    const visibility = document.getElementById('forumVisibility').value;
    const tags = document.getElementById('forumTags').value.split(',').map(t => t.trim()).filter(t => t);
    const startDate = document.getElementById('forumStartDate').value || null;
    const endDate = document.getElementById('forumEndDate').value || null;

    try {
        const response = await fetch('/api/forum', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify({
                title,
                description,
                visibility,
                tags,
                start_date: startDate,
                end_date: endDate
            })
        });

        const data = await response.json();

        if (data.status === 200) {
            closeCreateForumModal();
            await loadForumsToSidebar();
            await loadPopularTags();
            showSuccess('Forum created successfully!');
        } else {
            showError(data.message);
        }
    } catch (error) {
        console.error('Error creating forum:', error);
        showError('Failed to create forum');
    }
}

async function submitComment(event) {
    event.preventDefault();

    const content = document.getElementById('commentInput').value;

    try {
        const response = await fetch('/api/forum/comment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify({
                post_id: forumState.currentPost,
                content: content
            })
        });

        const data = await response.json();

        if (data.status === 200) {
            document.getElementById('commentInput').value = '';
            await loadComments(forumState.currentPost);
        } else {
            showError(data.message);
        }
    } catch (error) {
        console.error('Error posting comment:', error);
        showError('Failed to post comment');
    }
}

async function toggleReaction(postId) {
    try {
        const response = await fetch('/api/forum/react', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify({
                target_type: 'post',
                target_id: postId,
                reaction_type: 'like'
            })
        });

        const data = await response.json();

        if (data.status === 200) {
            const modal = document.getElementById('postDetailModal');
            if (modal && modal.classList.contains('active')) {
                await openPost(postId);
            } else {
                if (forumState.currentForumId) {
                    await loadPostsForForum(forumState.currentForumId);
                } else {
                    await loadAllPosts();
                }
            }
        }
    } catch (error) {
        console.error('Error toggling reaction:', error);
    }
}

async function toggleBookmark(postId) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        const response = await fetch('/api/forum/bookmark', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'include',
            body: JSON.stringify({
                post_id: parseInt(postId)
            })
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Bookmark error response:', errorText);
            let errorData;
            try {
                errorData = JSON.parse(errorText);
            } catch (e) {
                errorData = { message: 'Failed to toggle bookmark' };
            }
            alert(errorData.message || 'Failed to toggle bookmark');
            return;
        }

        const data = await response.json();

        if (data.status === 200) {
            // Reload posts to get updated bookmark status
            if (forumState.currentForumId) {
                await loadPostsForForum(forumState.currentForumId);
            } else {
                await loadAllPosts();
            }
        } else {
            alert(data.message || 'Failed to toggle bookmark');
        }
    } catch (error) {
        console.error('Error toggling bookmark:', error);
        alert('Failed to toggle bookmark: ' + error.message);
    }
}

function loadPopularTags() {
    const container = document.getElementById('tagCloud');

    // Extract all unique tags from posts
    const allTags = [];
    forumState.posts.forEach(post => {
        if (post.tags) {
            try {
                // Tags are already an array from the API, no need to parse
                let tags = post.tags;
                // If it's a string, try to parse it
                if (typeof tags === 'string') {
                    // Try JSON parse first
                    try {
                        tags = JSON.parse(tags);
                    } catch (e) {
                        // If not JSON, try splitting by comma
                        tags = tags.split(',').map(t => t.trim()).filter(t => t);
                    }
                }
                // Ensure it's an array
                if (Array.isArray(tags)) {
                    allTags.push(...tags);
                }
            } catch (e) {
                console.error('Error parsing tags:', e, post.tags);
            }
        }
    });

    // Count tag occurrences
    const tagCounts = {};
    allTags.forEach(tag => {
        tagCounts[tag] = (tagCounts[tag] || 0) + 1;
    });

    // Sort by count and get top tags
    const sortedTags = Object.entries(tagCounts)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 10) // Top 10 most popular tags
        .map(([tag]) => tag);

    if (sortedTags.length === 0) {
        container.innerHTML = `
            <p style="padding: 8px 16px; color: #878a8c; font-size: 12px;">
                No tags yet
            </p>
        `;
        return;
    }

    container.innerHTML = sortedTags.map(tag => `
        <span class="tag-chip ${forumState.filters.tag === tag ? 'active' : ''}" data-tag="${escapeHtml(tag)}">#${escapeHtml(tag)}</span>
    `).join('');

    // Add event listeners to tag chips
    container.querySelectorAll('.tag-chip').forEach(chip => {
        chip.addEventListener('click', function () {
            filterByTag(this.dataset.tag);
        });
    });
}

function filterByTag(tag) {
    // If clicking the same tag, clear the filter
    if (forumState.filters.tag === tag) {
        forumState.filters.tag = null;
        document.querySelectorAll('#tagCloud .tag-chip').forEach(chip => {
            chip.classList.remove('active');
        });
    } else {
        // Set new filter and update UI
        forumState.filters.tag = tag;
        document.querySelectorAll('#tagCloud .tag-chip').forEach(chip => {
            chip.classList.remove('active');
            if (chip.dataset.tag === tag) {
                chip.classList.add('active');
            }
        });
    }

    // Load posts with the selected tag
    if (forumState.filters.tag) {
        loadPostsByTag(forumState.filters.tag);
    } else {
        if (forumState.currentForumId) {
            loadPostsForForum(forumState.currentForumId);
        } else {
            loadAllPosts();
        }
    }
}

async function loadPostsByTag(tag) {
    try {
        const response = await fetch(`/api/forum/post?tag=${encodeURIComponent(tag)}`, {
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

        if (data.status === 200) {
            forumState.posts = data.data.posts;
            renderPosts(data.data.posts);
            loadPopularTags();
        } else {
            showError(data.message);
        }
    } catch (error) {
        console.error('Error loading posts with tag:', error);
        showError('Failed to load posts');
    }
}

function openCreateForumModal() {
    document.getElementById('createForumModal').classList.add('active');
}

function closeCreateForumModal() {
    document.getElementById('createForumModal').classList.remove('active');
    document.getElementById('createForumForm').reset();
}

function closePostModal() {
    document.getElementById('postDetailModal').classList.remove('active');
}

function formatTime(dateString) {
    if (!dateString) {
        return 'Unknown';
    }
    
    const date = new Date(dateString);
    
    // Check if date is valid (not NaN and not epoch 0)
    if (isNaN(date.getTime()) || date.getTime() === 0) {
        return 'Unknown';
    }
    
    const now = new Date();
    const diff = now - date;
    
    // If date is in the future or diff is negative, return formatted date
    if (diff < 0) {
        return date.toLocaleDateString();
    }
    
    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);

    if (days > 7) {
        return date.toLocaleDateString();
    } else if (days > 0) {
        return `${days}d ago`;
    } else if (hours > 0) {
        return `${hours}h ago`;
    } else if (minutes > 0) {
        return `${minutes}m ago`;
    } else {
        return 'Just now';
    }
}

function getFileIcon(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    const icons = {
        'pdf': 'fa-file-pdf',
        'doc': 'fa-file-word',
        'docx': 'fa-file-word',
        'jpg': 'fa-file-image',
        'jpeg': 'fa-file-image',
        'png': 'fa-file-image',
        'gif': 'fa-file-image'
    };
    return icons[ext] || 'fa-file';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showError(message) {
    alert(message);
}

function showSuccess(message) {
    alert(message);
}

function getCurrentUserId() {
    return parseInt(sessionStorage.getItem('userId')) || 0;
}

async function submitReplyForum(commentId, postId) {
    const replyInput = document.getElementById(`replyInput_${commentId}`);
    const content = replyInput.value.trim();

    if (!content) {
        return;
    }

    try {
        const response = await fetch('/api/forum/comment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify({
                post_id: postId,
                parent_id: commentId,
                content: content
            })
        });

        const data = await response.json();

        if (data.status === 200) {
            replyInput.value = '';
            toggleReplyFormForum(commentId);
            await loadComments(postId);
        } else {
            showError(data.message);
        }
    } catch (error) {
        console.error('Error posting reply:', error);
        showError('Failed to post reply');
    }
}

function toggleReplyFormForum(commentId) {
    const replyForm = document.getElementById(`replyForm_${commentId}`);
    if (replyForm) {
        if (replyForm.style.display === 'none') {
            replyForm.style.display = 'block';
            const textarea = document.getElementById(`replyInput_${commentId}`);
            if (textarea) {
                setTimeout(() => textarea.focus(), 100);
            }
        } else {
            replyForm.style.display = 'none';
            const textarea = document.getElementById(`replyInput_${commentId}`);
            if (textarea) {
                textarea.value = '';
            }
        }
    }
}

function cancelReplyForum(commentId) {
    const replyForm = document.getElementById(`replyForm_${commentId}`);
    if (replyForm) {
        replyForm.style.display = 'none';
        const textarea = document.getElementById(`replyInput_${commentId}`);
        if (textarea) {
            textarea.value = '';
        }
    }
}

async function toggleCommentLikeForum(commentId, postId) {
    try {
        const response = await fetch('/api/forum/react', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify({
                target_type: 'comment',
                target_id: commentId,
                reaction_type: 'like'
            })
        });

        const data = await response.json();

        if (data.status === 200) {
            await loadComments(postId);
        }
    } catch (error) {
        console.error('Error toggling comment like:', error);
    }
}

async function joinForum(forumId) {
    // TODO: Implement join forum functionality
    console.log('Join forum:', forumId);
    // You can implement the join functionality here when the API endpoint is ready
}

function togglePostOptions(postId) {
    const menu = document.getElementById(`postOptions_${postId}`);
    if (!menu) return;
    
    // Close all other menus
    document.querySelectorAll('.post-options-menu').forEach(m => {
        if (m.id !== `postOptions_${postId}`) {
            m.classList.add('hidden');
        }
    });
    
    menu.classList.toggle('hidden');
}

async function confirmDeletePost(postId) {
    if (!confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
        return;
    }
    
    try {
        const response = await fetch(`/api/forum/post/${postId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'include',
        });
        
        const data = await response.json();
        
        if (data.status === 200) {
            // Remove the post from the DOM
            const postCard = document.querySelector(`.reddit-post-card[onclick*="${postId}"]`);
            if (postCard) {
                postCard.remove();
            }
            
            // Reload posts to refresh the list
            loadAllPosts();
        } else {
            alert(data.message || 'Failed to delete post');
        }
    } catch (error) {
        console.error('Error deleting post:', error);
        alert('Failed to delete post');
    }
}

async function openShareModal(postId, postTitle, forumName) {
    // Close post options menu
    document.querySelectorAll('.post-options-menu').forEach(menu => {
        menu.classList.add('hidden');
    });
    
    // Create or get share modal
    let modal = document.getElementById('sharePostModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'sharePostModal';
        modal.className = 'share-modal';
        modal.innerHTML = `
            <div class="share-modal-overlay" onclick="closeShareModal()"></div>
            <div class="share-modal-content">
                <div class="share-modal-header">
                    <h3>Share Post</h3>
                    <button class="share-modal-close" onclick="closeShareModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="share-modal-body">
                    <div class="share-post-preview">
                        <div class="share-post-title">${escapeHtml(postTitle)}</div>
                        <div class="share-post-forum">r/${escapeHtml(forumName)}</div>
                    </div>
                    <div class="share-conversations-list" id="shareConversationsList">
                        <div class="loading">Loading conversations...</div>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
    
    // Update modal with current post info
    modal.querySelector('.share-post-title').textContent = postTitle;
    modal.querySelector('.share-post-forum').textContent = `r/${forumName}`;
    modal.dataset.postId = postId;
    
    // Show modal
    modal.classList.add('active');
    
    // Load conversations
    await loadShareConversations();
}

function closeShareModal() {
    const modal = document.getElementById('sharePostModal');
    if (modal) {
        modal.classList.remove('active');
    }
}

async function loadShareConversations() {
    const container = document.getElementById('shareConversationsList');
    if (!container) return;
    
    container.innerHTML = '<div class="loading">Loading conversations...</div>';
    
    try {
        const response = await fetch('/api/messaging/conversations', {
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
        
        if (data.status === 200 && data.data && data.data.conversations) {
            const conversations = data.data.conversations;
            
            if (conversations.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <p>No conversations found</p>
                        <a href="/messaging" class="btn-create-conversation">Start a conversation</a>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = conversations.map(conv => {
                const displayName = conv.type === 'group' 
                    ? conv.name 
                    : (conv.other_full_name || conv.other_username || 'Unknown');
                const avatar = conv.type === 'group'
                    ? '<i class="fas fa-users"></i>'
                    : (conv.other_avatar 
                        ? `<img src="${conv.other_avatar}" alt="${displayName}">`
                        : `<div class="avatar-initial">${displayName.charAt(0).toUpperCase()}</div>`);
                
                return `
                    <div class="share-conversation-item" onclick="shareToConversation(${conv.id}, '${conv.type}')">
                        <div class="share-conversation-avatar">${avatar}</div>
                        <div class="share-conversation-info">
                            <div class="share-conversation-name">${escapeHtml(displayName)}</div>
                            <div class="share-conversation-type">${conv.type === 'group' ? 'Group' : 'Direct message'}</div>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            container.innerHTML = '<div class="error">Failed to load conversations</div>';
        }
    } catch (error) {
        console.error('Error loading conversations:', error);
        container.innerHTML = '<div class="error">Failed to load conversations</div>';
    }
}

async function shareToConversation(conversationId, conversationType) {
    const modal = document.getElementById('sharePostModal');
    if (!modal) return;
    
    const postId = modal.dataset.postId;
    const postTitle = modal.querySelector('.share-post-title')?.textContent || '';
    const forumName = modal.querySelector('.share-post-forum')?.textContent || '';
    const postUrl = `${window.location.origin}/forum/post/${postId}`;
    
    // Create a preview message with post details
    const previewMessage = `📌 Shared Post: ${postTitle}\n\nForum: ${forumName}\n\n${postUrl}`;
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        // Send the message with post preview
        const response = await fetch('/api/messaging/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'include',
            body: JSON.stringify({
                conversation_id: parseInt(conversationId),
                content: previewMessage,
                message_type: 'text'
            })
        });
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ message: 'Failed to send message' }));
            alert(errorData.message || 'Failed to share post');
            return;
        }
        
        const data = await response.json();
        
        if (data.status === 200) {
            // Close the share modal
            closeShareModal();
            
            // Navigate to messaging page with the conversation selected
            window.location.href = `/messaging?conversation=${conversationId}`;
        } else {
            alert(data.message || 'Failed to share post');
        }
    } catch (error) {
        console.error('Error sharing post:', error);
        alert('Failed to share post: ' + error.message);
    }
}

// Close post options menu when clicking outside
document.addEventListener('click', (e) => {
    if (!e.target.closest('.post-options-container')) {
        document.querySelectorAll('.post-options-menu').forEach(menu => {
            menu.classList.add('hidden');
        });
    }
});

// Report Post Functions
async function openReportModal(postId, postTitle) {
    // Close post options menu
    document.querySelectorAll('.post-options-menu').forEach(menu => {
        menu.classList.add('hidden');
    });
    
    // Create or get report modal
    let modal = document.getElementById('reportPostModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'reportPostModal';
        modal.className = 'report-modal';
        modal.innerHTML = `
            <div class="report-modal-overlay" onclick="closeReportModal()"></div>
            <div class="report-modal-content">
                <div class="report-modal-header">
                    <h3>Report Post</h3>
                    <button class="report-modal-close" onclick="closeReportModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="report-modal-body">
                    <div class="report-post-preview">
                        <div class="report-post-title">${escapeHtml(postTitle)}</div>
                    </div>
                    <div class="report-reason-section">
                        <label class="report-label">Why are you reporting this post?</label>
                        <div class="report-reasons">
                            <label class="report-reason-option">
                                <input type="radio" name="reportReason" value="spam" required>
                                <span>Spam</span>
                            </label>
                            <label class="report-reason-option">
                                <input type="radio" name="reportReason" value="harassment" required>
                                <span>Harassment or Bullying</span>
                            </label>
                            <label class="report-reason-option">
                                <input type="radio" name="reportReason" value="inappropriate" required>
                                <span>Inappropriate Content</span>
                            </label>
                            <label class="report-reason-option">
                                <input type="radio" name="reportReason" value="misinformation" required>
                                <span>Misinformation</span>
                            </label>
                            <label class="report-reason-option">
                                <input type="radio" name="reportReason" value="other" required>
                                <span>Other</span>
                            </label>
                        </div>
                    </div>
                    <div class="report-details-section">
                        <label class="report-label" for="reportDetails">Additional details (optional)</label>
                        <textarea id="reportDetails" class="report-details-input" placeholder="Provide more information about why you're reporting this post..." maxlength="500"></textarea>
                        <div class="report-char-count"><span id="reportCharCount">0</span>/500</div>
                    </div>
                    <div id="reportError" class="report-error" style="display: none;"></div>
                </div>
                <div class="report-modal-footer">
                    <button class="report-cancel-btn" onclick="closeReportModal()">Cancel</button>
                    <button class="report-submit-btn" onclick="submitReport()">Submit Report</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        
        // Add character counter
        const detailsInput = document.getElementById('reportDetails');
        const charCount = document.getElementById('reportCharCount');
        if (detailsInput && charCount) {
            detailsInput.addEventListener('input', () => {
                charCount.textContent = detailsInput.value.length;
            });
        }
    }
    
    // Update modal with current post info
    const titleElement = modal.querySelector('.report-post-title');
    if (titleElement) {
        titleElement.textContent = postTitle;
    }
    modal.dataset.postId = postId;
    
    // Reset form
    const form = modal.querySelector('form');
    if (form) form.reset();
    const detailsInput = document.getElementById('reportDetails');
    if (detailsInput) {
        detailsInput.value = '';
        const charCount = document.getElementById('reportCharCount');
        if (charCount) charCount.textContent = '0';
    }
    const errorDiv = document.getElementById('reportError');
    if (errorDiv) {
        errorDiv.style.display = 'none';
        errorDiv.textContent = '';
    }
    
    // Show modal
    modal.classList.add('active');
}

function closeReportModal() {
    const modal = document.getElementById('reportPostModal');
    if (modal) {
        modal.classList.remove('active');
    }
}

async function submitReport() {
    const modal = document.getElementById('reportPostModal');
    if (!modal) return;
    
    const postId = modal.dataset.postId;
    const selectedReason = modal.querySelector('input[name="reportReason"]:checked');
    const detailsInput = document.getElementById('reportDetails');
    const errorDiv = document.getElementById('reportError');
    
    if (!selectedReason) {
        if (errorDiv) {
            errorDiv.textContent = 'Please select a reason for reporting';
            errorDiv.style.display = 'block';
        }
        return;
    }
    
    const reason = selectedReason.value;
    const details = detailsInput ? detailsInput.value.trim() : '';
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const response = await fetch('/api/forum/post/report', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'include',
            body: JSON.stringify({
                post_id: parseInt(postId),
                reason: reason,
                details: details
            })
        });
        
        const data = await response.json();
        
        if (data.status === 200) {
            closeReportModal();
            alert('Post reported successfully. Thank you for helping keep our community safe.');
            
            // Reload posts to update report count display
            loadAllPosts();
        } else {
            if (errorDiv) {
                errorDiv.textContent = data.message || 'Failed to submit report';
                errorDiv.style.display = 'block';
            }
        }
    } catch (error) {
        console.error('Error submitting report:', error);
        if (errorDiv) {
            errorDiv.textContent = 'Failed to submit report. Please try again.';
            errorDiv.style.display = 'block';
        }
    }
}

window.filterByForum = filterByForum;
window.openPost = openPost;
window.toggleReaction = toggleReaction;
window.toggleBookmark = toggleBookmark;
window.submitComment = submitComment;
window.togglePostOptions = togglePostOptions;
window.confirmDeletePost = confirmDeletePost;
window.openShareModal = openShareModal;
window.closeShareModal = closeShareModal;
window.shareToConversation = shareToConversation;
window.openReportModal = openReportModal;
window.closeReportModal = closeReportModal;
window.submitReport = submitReport;
window.toggleHidePost = toggleHidePost;
window.submitReplyForum = submitReplyForum;
window.toggleReplyFormForum = toggleReplyFormForum;
window.cancelReplyForum = cancelReplyForum;
window.toggleCommentLikeForum = toggleCommentLikeForum;
window.toggleCommentCollapseForum = toggleCommentCollapseForum;
window.filterByTag = filterByTag;
window.joinForum = joinForum;
window.clearRecentPosts = clearRecentPosts;

