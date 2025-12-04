let forumState = {
    forumId: null,
    forum: null,
    isMember: false,
    userRole: null,
    isMuted: false,
    isFavorite: false
};

// Helper function to normalize file URLs
function normalizeFileUrl(url) {
    if (!url) return '';
    
    // If URL is already absolute (starts with / or http), return as-is
    if (url.startsWith('/') || url.startsWith('http://') || url.startsWith('https://')) {
        return url;
    }
    
    // If URL is relative, make it absolute from web root
    // Get base path from current location
    const pathname = window.location.pathname;
    const pathParts = pathname.split('/').filter(part => part);
    // Remove filename and 'Forum' directory to get base path
    if (pathParts.length > 1) {
        pathParts.pop(); // Remove filename
        if (pathParts[pathParts.length - 1] === 'Forum') {
            pathParts.pop(); // Remove 'Forum' directory
        }
    }
    const basePath = pathParts.length > 0 ? '/' + pathParts.join('/') : '';
    
    // Ensure URL starts with / and doesn't have double slashes
    const normalizedUrl = (basePath + '/' + url).replace(/\/+/g, '/');
    return normalizedUrl;
}

document.addEventListener('DOMContentLoaded', () => {
    // Check if user is logged in
    if (sessionStorage.getItem('userLoggedIn') !== 'true') {
        window.location.href = '../login.html';
        return;
    }

    initEventListeners();
    loadForumDetails();
});

function initEventListeners() {
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
            window.location.href = '../login.html';
        });
    }

    const btnCreatePost = document.getElementById('btnCreatePost');
    if (btnCreatePost) {
        btnCreatePost.addEventListener('click', () => {
            const forumId = forumState.forumId || new URLSearchParams(window.location.search).get('id');
            const referrer = `forum-detail.html?id=${forumId}`;
            window.location.href = `create-post.html?forum=${forumId}&referrer=${encodeURIComponent(referrer)}`;
        });
    }

    const btnCreatePostForForum = document.getElementById('btnCreatePostForForum');
    if (btnCreatePostForForum) {
        btnCreatePostForForum.addEventListener('click', () => {
            const forumId = forumState.forumId || new URLSearchParams(window.location.search).get('id');
            const referrer = `forum-detail.html?id=${forumId}`;
            window.location.href = `create-post.html?forum=${forumId}&referrer=${encodeURIComponent(referrer)}`;
        });
    }

    const btnJoin = document.getElementById('btnJoin');
    if (btnJoin) {
        btnJoin.addEventListener('click', joinForum);
    }

    const btnMore = document.getElementById('btnMore');
    if (btnMore) {
        btnMore.addEventListener('click', function (e) {
            e.stopPropagation();
            const menu = document.getElementById('moreMenu');
            if (menu) {
                menu.classList.toggle('show');
            }
        });
    }

    // Close more menu when clicking outside
    const moreMenu = document.getElementById('moreMenu');
    if (moreMenu && btnMore) {
        document.addEventListener('click', function (e) {
            if (!moreMenu.contains(e.target) && !btnMore.contains(e.target)) {
                moreMenu.classList.remove('show');
            }
        });
    }

    const muteOption = document.getElementById('muteOption');
    if (muteOption) {
        muteOption.addEventListener('click', toggleMute);
    }

    const favoriteOption = document.getElementById('favoriteOption');
    if (favoriteOption) {
        favoriteOption.addEventListener('click', toggleFavorite);
    }

    const manageOption = document.getElementById('manageOption');
    if (manageOption) {
        manageOption.addEventListener('click', manageForum);
    }

    const leaveOption = document.getElementById('leaveOption');
    if (leaveOption) {
        leaveOption.addEventListener('click', leaveForum);
    }

    const closePostModalBtn = document.getElementById('closePostModal');
    if (closePostModalBtn) {
        closePostModalBtn.addEventListener('click', closePostModal);
    }

    const sortPosts = document.getElementById('sortPosts');
    if (sortPosts) {
        sortPosts.addEventListener('change', (e) => {
            loadPosts();
        });
    }
}

async function loadForumDetails() {
    const urlParams = new URLSearchParams(window.location.search);
    const forumId = urlParams.get('id');

    if (!forumId) {
        showError('No forum selected');
        return;
    }

    forumState.forumId = forumId;

    try {
        // Load forum details
        const forumResponse = await fetch(`../api/forum_endpoints.php?action=get_forum_details&forum_id=${forumId}`, {
            credentials: 'include'
        });
        const forumData = await forumResponse.json();

        if (forumData.status === 200) {
            forumState.forum = forumData.data.forum;
            renderForumHeader();

            // Load posts
            loadPosts();
        } else {
            showError(forumData.message || 'Failed to load forum details');
        }
    } catch (error) {
        console.error('Error loading forum details:', error);
        showError('Failed to load forum');
    }
}

function renderForumHeader() {
    const forum = forumState.forum;

    // Main title and avatar
    document.getElementById('forumName').textContent = forum.title;

    // Avatar - show first letters of forum title
    const firstLetters = forum.title.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
    document.getElementById('forumAvatar').innerHTML = firstLetters;

    // Sidebar info
    document.getElementById('forumDescription').textContent = forum.description || 'No description available.';
    document.getElementById('memberCount').textContent = forum.member_count || 0;
    document.getElementById('postCount').textContent = forum.post_count || 0;
    document.getElementById('createdDate').textContent = formatDate(forum.created_at);

    // Visibility
    const visibilityMap = {
        'public': 'Public',
        'class': 'Class Only',
        'specific': 'Invite Only'
    };
    document.getElementById('forumVisibility').textContent = visibilityMap[forum.visibility] || 'Public';

    // Set membership status
    forumState.isMember = forum.is_member || false;
    forumState.userRole = forum.user_role || null;
    forumState.isMuted = forum.is_muted || false;
    forumState.isFavorite = forum.is_favorite || false;

    // Show/hide buttons based on membership
    const createPostBtn = document.getElementById('btnCreatePostForForum');
    const joinBtn = document.getElementById('btnJoin');
    const joinedBtn = document.getElementById('btnJoined');
    const manageOption = document.getElementById('manageOption');

    if (forumState.isMember) {
        if (createPostBtn) createPostBtn.style.display = 'flex';
        if (joinedBtn) joinedBtn.style.display = 'block';
        if (joinBtn) joinBtn.style.display = 'none';

        // Show manage option for admins/moderators
        if (forumState.userRole === 'admin' || forumState.userRole === 'moderator') {
            if (manageOption) manageOption.style.display = 'flex';
        }
    } else {
        if (createPostBtn) createPostBtn.style.display = 'none';
        if (joinedBtn) joinedBtn.style.display = 'none';
        if (joinBtn) joinBtn.style.display = 'block';
        if (manageOption) manageOption.style.display = 'none';
    }

    // Update mute/favorite button text
    // Update mute/favorite button text
    const muteText = document.getElementById('muteText');
    if (muteText) {
        muteText.textContent = forumState.isMuted ? 'Unmute Forum' : 'Mute Forum';
    }

    const favoriteText = document.getElementById('favoriteText');
    if (favoriteText) {
        favoriteText.textContent = forumState.isFavorite ? 'Remove from Favorites' : 'Add to Favorites';
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return `${months[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;
}

async function loadPosts() {
    try {
        const response = await fetch(`../api/forum_endpoints.php?action=get_posts&forum_id=${forumState.forumId}`);
        const data = await response.json();

        if (data.status === 200) {
            renderPosts(data.data.posts);
        } else {
            showError(data.message);
        }
    } catch (error) {
        console.error('Error loading posts:', error);
        showError('Failed to load posts');
    }
}

function renderPosts(posts) {
    const container = document.getElementById('postsContent');

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
                    <div class="post-vote-section">
                        <button class="vote-btn like ${post.user_reacted ? 'active' : ''}" onclick="event.stopPropagation(); toggleReaction(${post.id})" title="Like">
                            <i class="${post.user_reacted ? 'fas' : 'far'} fa-heart"></i>
                        </button>
                        <div class="vote-count">${post.reaction_count || 0}</div>
                    </div>
                    <div class="post-content-section">
                        <div class="post-header">
                            <div class="post-header-left">
                                ${post.is_pinned ? '<span class="post-pinned"><i class="fas fa-thumbtack"></i> pinned</span>' : ''}
                                <span class="post-author">u/${escapeHtml(post.author_name || post.author_username)}</span>
                                <span class="post-time">${formatTime(post.created_at)}</span>
                                ${post.is_edited ? '<span class="post-time">(edited)</span>' : ''}
                            </div>
                            ${(getCurrentUserId() === post.author_id || (post.user_forum_role && ['admin', 'moderator'].includes(post.user_forum_role))) ? `
                            <div class="post-options-container" onclick="event.stopPropagation();">
                                <button class="post-options-btn" onclick="event.stopPropagation(); togglePostOptions(${post.id})" title="More options">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                                <div id="postOptions_${post.id}" class="post-options-menu hidden">
                                    <button class="post-options-item" onclick="event.stopPropagation(); openShareModal(${post.id}, '${escapeHtml(post.title)}', '${escapeHtml(post.forum_name || 'Forum')}')">
                                        <i class="fas fa-share"></i> Share
                                    </button>
                                    <button class="post-options-item delete-option" onclick="event.stopPropagation(); confirmDeletePost(${post.id})">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                            ` : ''}
                        </div>
                        <div class="post-title">
                            ${escapeHtml(post.title)}
                        </div>
                        <div class="post-preview-text">
                            ${escapeHtml(post.content)}
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
                    const imageAttachments = [];
                    const otherAttachments = [];

                    attachments.forEach(att => {
                        const ext = att.name.split('.').pop().toLowerCase();
                        if (imageExts.includes(ext)) {
                            imageAttachments.push(att);
                        } else {
                            otherAttachments.push(att);
                        }
                    });

                    let html = '';

                    // Show image previews
                    if (imageAttachments.length > 0) {
                        html += '<div class="post-image-preview" style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 8px;">';
                        imageAttachments.forEach(att => {
                            const normalizedUrl = normalizeFileUrl(att.url);
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

                    // Show other file attachments as links
                    if (otherAttachments.length > 0) {
                        html += '<div style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 8px;">';
                        otherAttachments.forEach(att => {
                            const normalizedUrl = normalizeFileUrl(att.url);
                            html += `
                                                    <a href="${normalizedUrl}" target="_blank" class="attachment-file" onclick="event.stopPropagation();">
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
                            <button class="post-footer-btn" onclick="event.stopPropagation(); openPost(${post.id})">
                                <i class="far fa-comment"></i>
                                ${post.reply_count || 0} Comments
                            </button>
                            <button class="post-footer-btn" onclick="event.stopPropagation(); toggleBookmark(${post.id})">
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

async function joinForum() {
    try {
        const response = await fetch('../api/forum_endpoints.php?action=join_forum', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({
                forum_id: forumState.forumId
            })
        });

        const data = await response.json();

        if (data.status === 200) {
            forumState.isMember = true;
            renderForumHeader();
            loadPosts();
        } else {
            showError(data.message || 'Failed to join forum');
        }
    } catch (error) {
        console.error('Error joining forum:', error);
        showError('Failed to join forum');
    }
}

async function toggleMute() {
    const action = forumState.isMuted ? 'unmute_forum' : 'mute_forum';

    try {
        const response = await fetch(`../api/forum_endpoints.php?action=${action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({
                forum_id: forumState.forumId
            })
        });

        const data = await response.json();

        if (data.status === 200) {
            forumState.isMuted = !forumState.isMuted;
            renderForumHeader();
        } else {
            showError(data.message || 'Failed to update mute status');
        }
    } catch (error) {
        console.error('Error toggling mute:', error);
        showError('Failed to update mute status');
    }
}

async function toggleFavorite() {
    const action = forumState.isFavorite ? 'unfavorite_forum' : 'favorite_forum';

    try {
        const response = await fetch(`../api/forum_endpoints.php?action=${action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({
                forum_id: forumState.forumId
            })
        });

        const data = await response.json();

        if (data.status === 200) {
            forumState.isFavorite = !forumState.isFavorite;
            renderForumHeader();
        } else {
            showError(data.message || 'Failed to update favorite status');
        }
    } catch (error) {
        console.error('Error toggling favorite:', error);
        showError('Failed to update favorite status');
    }
}

async function manageForum() {
    window.location.href = `manage-forum.html?id=${forumState.forumId}`;
}

async function leaveForum() {
    if (!confirm('Are you sure you want to leave this forum?')) {
        return;
    }

    try {
        const response = await fetch('../api/forum_endpoints.php?action=leave_forum', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({
                forum_id: forumState.forumId
            })
        });

        const data = await response.json();

        if (data.status === 200) {
            window.location.href = 'forum.html';
        } else {
            showError(data.message || 'Failed to leave forum');
        }
    } catch (error) {
        console.error('Error leaving forum:', error);
        showError('Failed to leave forum');
    }
}

async function openPost(postId) {
    // Get current forum ID to pass as referrer
    const forumId = forumState.forumId || new URLSearchParams(window.location.search).get('id');
    const referrer = forumId ? `forum-detail.html?id=${forumId}` : 'forum-detail.html';
    window.location.href = `post-detail.html?id=${postId}&referrer=${encodeURIComponent(referrer)}`;
}

async function renderPostDetail(post) {
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
                    const attachments = JSON.parse(post.attachments);
                    return Array.isArray(attachments) ? attachments : [];
                } catch (e) {
                    return [];
                }
            })().map(att => {
                        const normalizedUrl = normalizeFileUrl(att.url);
                        return `
                        <a href="${normalizedUrl}" target="_blank" class="attachment-file" style="display: inline-flex; margin-right: 10px;">
                            <i class="fas ${getFileIcon(att.name)}"></i>
                            ${escapeHtml(att.name)}
                        </a>
                    `;
                    }).join('')}
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
            </div>
            
            <div class="comments-section">
                <div class="comments-header">
                    <i class="far fa-comment"></i> Comments (${post.reply_count || 0})
                </div>
                <div id="commentsContainer">
                </div>
            </div>
        </div>
    `;

    loadComments(post.id);
}

function renderComments(comments) {
    const container = document.getElementById('commentsContainer');

    if (!comments || comments.length === 0) {
        container.innerHTML = '<p style="color: #878a8c; text-align: center; padding: 20px;">No comments yet</p>';
        return;
    }

    // Flatten all comments and replies into a single flat list
    const flattenComments = (comments, parentAuthor = null) => {
        const flatList = [];
        comments.forEach(comment => {
            // Add the comment itself
            flatList.push({ ...comment, parentAuthor, isReply: parentAuthor !== null });
            // Recursively add all replies
            if (comment.replies && comment.replies.length > 0) {
                const replies = flattenComments(comment.replies, comment.author_name || comment.author_username);
                flatList.push(...replies);
            }
        });
        return flatList;
    };

    const flatComments = flattenComments(comments);

    // Sort by creation date (newest first)
    const sortedComments = flatComments.sort((a, b) => {
        return new Date(b.created_at) - new Date(a.created_at);
    });

    container.innerHTML = sortedComments.map((comment, index) => renderCommentItemForumDetail(comment, 0, index === sortedComments.length - 1, comment.parentAuthor)).join('');
}

let collapsedCommentsForumDetail = new Set();

function toggleCommentCollapseForumDetail(commentId) {
    if (collapsedCommentsForumDetail.has(commentId)) {
        collapsedCommentsForumDetail.delete(commentId);
    } else {
        collapsedCommentsForumDetail.add(commentId);
    }
    if (currentPostIdForReplies) {
        loadComments(currentPostIdForReplies);
    }
}

function renderCommentItemForumDetail(comment, depth = 0, isLastChild = false, parentAuthor = null) {
    const isReply = comment.isReply || false;

    return `
        <div class="comment-item" data-comment-id="${comment.id}" style="padding: 12px 0; margin-bottom: 16px; border-bottom: 1px solid #edeff1;">
            <div class="comment-header">
                <div class="comment-author">
                    <div class="comment-author-avatar">
                        ${comment.author_avatar ?
            `<img src="${comment.author_avatar}" alt="${comment.author_username}" style="width: 100%; height: 100%; border-radius: 50%;">` :
            (comment.author_username ? comment.author_username.charAt(0).toUpperCase() : '?')
        }
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; font-size: 14px; color: #1c1c1c;">u/${escapeHtml(comment.author_name || comment.author_username)}</div>
                        <div style="font-size: 12px; color: #6b7280; display: flex; align-items: center; gap: 4px;">
                            <span>•</span>
                            <span>${formatTime(comment.created_at)}</span>
                        </div>
                    </div>
                    ${comment.reaction_count > 0 ? `
                        <span style="font-size: 12px; color: #6b7280; display: flex; align-items: center; gap: 4px;" title="Likes">
                            <i class="fas fa-heart" style="color: #ff4500; font-size: 11px;"></i>
                            <span>${comment.reaction_count}</span>
                        </span>
                    ` : ''}
                </div>
            </div>
            <div class="comment-content" style="font-size: 15px; margin-left: 40px; margin-top: 4px; color: #1c1c1c; word-wrap: break-word; overflow-wrap: break-word; line-height: 1.6;">
                ${parentAuthor && isReply ? `<span style="color: #2454FF; font-weight: 600; margin-right: 4px;">@${escapeHtml(parentAuthor)}</span>` : ''}${escapeHtml(comment.content)}
                ${comment.is_edited ? '<span style="font-style: italic; color: #6b7280; margin-left: 4px; font-size: 12px;">(edited)</span>' : ''}
            </div>
            
            <!-- Reply Form (hidden by default) -->
            <div class="reply-form-container" id="replyForm_${comment.id}" style="display: none; margin-top: 12px; margin-left: 40px; padding: 12px; background: #f7f9fa; border-radius: 4px;">
                <div style="display: flex; gap: 8px;">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: #ff4500; color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600;">
                        ${(sessionStorage.getItem('userEmail') || 'U').charAt(0).toUpperCase()}
                    </div>
                    <div style="flex: 1;">
                        <textarea id="replyInput_${comment.id}" placeholder="Write a reply..." style="width: 100%; min-height: 60px; padding: 8px; border: 1px solid #edeff1; border-radius: 4px; resize: vertical;" required></textarea>
                        <div style="display: flex; gap: 8px; margin-top: 8px;">
                            <button type="button" onclick="submitReplyForumDetail(${comment.id})" style="padding: 8px 16px; background: #ff4500; color: white; border: none; border-radius: 4px; cursor: pointer;">Reply</button>
                            <button type="button" onclick="cancelReplyForumDetail(${comment.id})" style="padding: 8px 16px; background: #e4e6eb; color: #1c1c1c; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 8px; margin-left: 40px; display: flex; gap: 16px;">
                <button class="post-footer-btn" onclick="toggleCommentLikeForumDetail(${comment.id})" style="background: transparent; border: none; color: #6b7280; cursor: pointer; font-size: 13px; padding: 4px 8px; display: flex; align-items: center; gap: 4px;">
                    <i class="far fa-heart"></i> Like
                </button>
                <button class="post-footer-btn" onclick="toggleReplyFormForumDetail(${comment.id})" style="background: transparent; border: none; color: #6b7280; cursor: pointer; font-size: 13px; padding: 4px 8px; display: flex; align-items: center; gap: 4px;">
                    <i class="far fa-comment"></i> Reply
                </button>
            </div>
        </div>
    `;
}

let currentPostIdForReplies = null;

async function loadComments(postId) {
    currentPostIdForReplies = postId;
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

async function submitReplyForumDetail(commentId) {
    if (!currentPostIdForReplies) return;

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
                post_id: currentPostIdForReplies,
                parent_id: commentId,
                content: content
            })
        });

        const data = await response.json();

        if (data.status === 200) {
            replyInput.value = '';
            toggleReplyFormForumDetail(commentId);
            await loadComments(currentPostIdForReplies);
        } else {
            showError(data.message);
        }
    } catch (error) {
        console.error('Error posting reply:', error);
        showError('Failed to post reply');
    }
}

function toggleReplyFormForumDetail(commentId) {
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

function cancelReplyForumDetail(commentId) {
    const replyForm = document.getElementById(`replyForm_${commentId}`);
    if (replyForm) {
        replyForm.style.display = 'none';
        const textarea = document.getElementById(`replyInput_${commentId}`);
        if (textarea) {
            textarea.value = '';
        }
    }
}

async function toggleCommentLikeForumDetail(commentId) {
    if (!currentPostIdForReplies) return;

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
            await loadComments(currentPostIdForReplies);
        }
    } catch (error) {
        console.error('Error toggling comment like:', error);
    }
}

function toggleReaction(postId) {
    // TODO: Implement reaction toggle
    alert('Reaction feature not yet implemented');
}

function toggleBookmark(postId) {
    // TODO: Implement bookmark toggle
    alert('Bookmark feature not yet implemented');
}

function closePostModal() {
    document.getElementById('postDetailModal').classList.remove('active');
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

function getCurrentUserId() {
    return parseInt(sessionStorage.getItem('userId')) || 0;
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
        const response = await fetch(`../api/forum_endpoints.php?action=delete_post&post_id=${postId}`, {
            method: 'DELETE',
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.status === 200) {
            // Remove the post from the DOM
            const postCard = document.querySelector(`.reddit-post-card[onclick*="${postId}"]`);
            if (postCard) {
                postCard.remove();
            }
            
            // Reload posts to refresh the list
            loadPosts();
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
        const response = await fetch('../api/messaging_endpoints.php?action=get_conversations', {
            method: 'GET',
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.status === 200 && data.data.conversations) {
            const conversations = data.data.conversations;
            
            if (conversations.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <p>No conversations found</p>
                        <a href="../Messaging/messaging.html" class="btn-create-conversation">Start a conversation</a>
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

function shareToConversation(conversationId, conversationType) {
    const modal = document.getElementById('sharePostModal');
    if (!modal) return;
    
    const postId = modal.dataset.postId;
    const postUrl = `${window.location.origin}${window.location.pathname.replace('forum-detail.html', 'post-detail.html')}?id=${postId}`;
    
    // Navigate to messaging page with conversation and post link
    window.location.href = `../Messaging/messaging.html?conversation=${conversationId}&share=${encodeURIComponent(postUrl)}`;
}

// Close post options menu when clicking outside
document.addEventListener('click', (e) => {
    if (!e.target.closest('.post-options-container')) {
        document.querySelectorAll('.post-options-menu').forEach(menu => {
            menu.classList.add('hidden');
        });
    }
});

// Make functions globally accessible
window.openPost = openPost;
window.toggleReaction = toggleReaction;
window.toggleBookmark = toggleBookmark;
window.submitReplyForumDetail = submitReplyForumDetail;
window.toggleReplyFormForumDetail = toggleReplyFormForumDetail;
window.cancelReplyForumDetail = cancelReplyForumDetail;
window.toggleCommentLikeForumDetail = toggleCommentLikeForumDetail;
window.toggleCommentCollapseForumDetail = toggleCommentCollapseForumDetail;
window.closePostModal = closePostModal;
window.togglePostOptions = togglePostOptions;
window.confirmDeletePost = confirmDeletePost;
window.openShareModal = openShareModal;
window.closeShareModal = closeShareModal;
window.shareToConversation = shareToConversation;

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
        return `${days} day${days > 1 ? 's' : ''} ago`;
    } else if (hours > 0) {
        return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    } else if (minutes > 0) {
        return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
    } else {
        return 'just now';
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showError(message) {
    alert(message);
}

