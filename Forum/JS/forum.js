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
            window.location.href = 'create-forum.html';
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
            window.location.href = 'login.html';
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
            window.location.href = 'create-post.html';
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
                    window.location.href = `forum-search.html?q=${encodeURIComponent(searchTerm)}`;
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
                    window.location.href = `forum-search.html?q=${encodeURIComponent(searchTerm)}`;
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
        const response = await fetch(`../api/forum_endpoints.php?action=get_forums`);
        const data = await response.json();

        if (data.status === 200) {
            forumState.forums = data.data.forums;
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
             onclick="window.location.href='forum-detail.html?id=${forum.id}'">
            <i class="fas fa-comments" style="color: #878a8c; margin-right: 8px;"></i>
            <span>${escapeHtml(forum.title)}</span>
            ${forum.unread_count > 0 ? `<span style="background: #ff4500; color: white; padding: 2px 6px; border-radius: 10px; font-size: 11px; margin-left: auto;">${forum.unread_count}</span>` : ''}
        </div>
    `).join('');
}

async function loadAllPosts() {
    try {
        const response = await fetch(`../api/forum_endpoints.php?action=get_posts`);
        const data = await response.json();

        if (data.status === 200) {
            forumState.posts = data.data.posts;
            renderPosts(data.data.posts);
            loadPopularTags();
        } else {
            showError(data.message);
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
        const response = await fetch(`../api/forum_endpoints.php?action=get_posts&forum_id=${forumId}`);
        const data = await response.json();

        if (data.status === 200) {
            forumState.posts = data.data.posts;
            renderPosts(data.data.posts);
            loadPopularTags();
        } else {
            showError(data.message);
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
                <h3>No posts yet</h3>
                <p>Be the first to create a post!</p>
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
                                <span class="post-community">r/${escapeHtml(post.forum_name || 'Forum')}</span>
                                <span class="post-time">•</span>
                                <span class="post-time">${formatTime(post.created_at)}</span>
                                ${post.is_pinned ? '<span class="post-pinned"><i class="fas fa-thumbtack"></i> pinned</span>' : ''}
                            </div>
                        </div>
                        <div class="post-title">
                            ${escapeHtml(post.title)}
                        </div>
                        <div class="post-preview-text">
                            ${escapeHtml(post.content.substring(0, 200))}${post.content.length > 200 ? '...' : ''}
                        </div>
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
                            html += '<div class="post-media-container" onclick="event.stopPropagation();">';
                            if (media.type === 'image') {
                                html += `<img src="${media.url}" alt="${escapeHtml(media.name)}" class="post-media-image" onclick="window.open('${media.url}', '_blank');">`;
                            } else {
                                html += `
                                                        <video class="post-media-video" controls preload="metadata">
                                                            <source src="${media.url}" type="video/${media.name.split('.').pop().toLowerCase()}">
                                                            Your browser does not support the video tag.
                                                        </video>
                                                    `;
                            }
                            html += '</div>';
                        } else {
                            // Multiple media items - Grid layout
                            html += '<div class="post-media-grid" onclick="event.stopPropagation();">';
                            mediaAttachments.forEach(media => {
                                html += '<div class="post-media-grid-item">';
                                if (media.type === 'image') {
                                    html += `<img src="${media.url}" alt="${escapeHtml(media.name)}" onclick="window.open('${media.url}', '_blank');">`;
                                } else {
                                    // For videos in grid, show a video tag but maybe muted/small or just a placeholder if we had one. 
                                    // For now, let's just put the video tag, it might be heavy but it works.
                                    html += `
                                                            <video style="width: 100%; height: 100%; object-fit: cover;" controls preload="metadata">
                                                                <source src="${media.url}" type="video/${media.name.split('.').pop().toLowerCase()}">
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
    window.location.href = `post-detail.html?id=${postId}`;
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
        const response = await fetch(`../api/forum_endpoints.php?action=get_posts&post_ids=${postIds}`);
        const data = await response.json();

        if (data.status === 200 && data.data.posts) {
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
                            <span class="recent-post-forum">r/${escapeHtml(post.forum_name || 'Forum')}</span>
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
        const response = await fetch(`../api/forum_endpoints.php?action=get_comments&post_id=${postId}&sort=top`);
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
        const response = await fetch('../api/forum_endpoints.php?action=create_forum', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
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
        const response = await fetch('../api/forum_endpoints.php?action=create_comment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
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
        const response = await fetch('../api/forum_endpoints.php?action=add_reaction', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
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
    const action = document.querySelector('.post-detail-actions .btn-action:contains("Bookmark")');
    const isBookmarked = action.classList.contains('liked');

    try {
        const response = await fetch('../api/forum_endpoints.php?action=bookmark_post', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                post_id: postId,
                action: isBookmarked ? 'remove' : 'add'
            })
        });

        const data = await response.json();

        if (data.status === 200) {
            action.classList.toggle('liked');
        }
    } catch (error) {
        console.error('Error toggling bookmark:', error);
    }
}

function loadPopularTags() {
    const container = document.getElementById('tagCloud');

    // Extract all unique tags from posts
    const allTags = [];
    forumState.posts.forEach(post => {
        if (post.tags) {
            try {
                const tags = JSON.parse(post.tags);
                allTags.push(...tags);
            } catch (e) {
                console.error('Error parsing tags:', e);
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
        const response = await fetch(`../api/forum_endpoints.php?action=get_posts&tag=${encodeURIComponent(tag)}`);
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
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
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
    return 1;
}

async function submitReplyForum(commentId, postId) {
    const replyInput = document.getElementById(`replyInput_${commentId}`);
    const content = replyInput.value.trim();

    if (!content) {
        return;
    }

    try {
        const response = await fetch('../api/forum_endpoints.php?action=create_comment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
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
        const response = await fetch('../api/forum_endpoints.php?action=add_reaction', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
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

window.filterByForum = filterByForum;
window.openPost = openPost;
window.toggleReaction = toggleReaction;
window.toggleBookmark = toggleBookmark;
window.submitComment = submitComment;
window.submitReplyForum = submitReplyForum;
window.toggleReplyFormForum = toggleReplyFormForum;
window.cancelReplyForum = cancelReplyForum;
window.toggleCommentLikeForum = toggleCommentLikeForum;
window.toggleCommentCollapseForum = toggleCommentCollapseForum;
window.filterByTag = filterByTag;
window.joinForum = joinForum;
window.clearRecentPosts = clearRecentPosts;

