let forumState = {
    forumId: null,
    forum: null,
    isMember: false,
    userRole: null,
    isMuted: false,
    isFavorite: false
};

document.addEventListener('DOMContentLoaded', () => {
    // Check if user is logged in
    if (sessionStorage.getItem('userLoggedIn') !== 'true') {
        window.location.href = 'login.html';
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
            window.location.href = 'login.html';
        });
    }
    
    const btnCreatePost = document.getElementById('btnCreatePost');
    if (btnCreatePost) {
        btnCreatePost.addEventListener('click', () => {
            const forumId = forumState.forumId || new URLSearchParams(window.location.search).get('id');
            window.location.href = `create-post.html?forum=${forumId}`;
        });
    }
    
    const btnCreatePostForForum = document.getElementById('btnCreatePostForForum');
    if (btnCreatePostForForum) {
        btnCreatePostForForum.addEventListener('click', () => {
            const forumId = forumState.forumId || new URLSearchParams(window.location.search).get('id');
            window.location.href = `create-post.html?forum=${forumId}`;
        });
    }
    
    const btnJoin = document.getElementById('btnJoin');
    if (btnJoin) {
        btnJoin.addEventListener('click', joinForum);
    }
    
    const btnMore = document.getElementById('btnMore');
    if (btnMore) {
        btnMore.addEventListener('click', function(e) {
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
        document.addEventListener('click', function(e) {
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
    
    const closePostModal = document.getElementById('closePostModal');
    if (closePostModal) {
        closePostModal.addEventListener('click', closePostModal);
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
        createPostBtn.style.display = 'flex';
        joinedBtn.style.display = 'block';
        joinBtn.style.display = 'none';
        
        // Show manage option for admins/moderators
        if (forumState.userRole === 'admin' || forumState.userRole === 'moderator') {
            manageOption.style.display = 'flex';
        }
    } else {
        createPostBtn.style.display = 'none';
        joinedBtn.style.display = 'none';
        joinBtn.style.display = 'block';
        manageOption.style.display = 'none';
    }
    
    // Update mute/favorite button text
    document.getElementById('muteText').textContent = forumState.isMuted ? 'Unmute Forum' : 'Mute Forum';
    document.getElementById('favoriteText').textContent = forumState.isFavorite ? 'Remove from Favorites' : 'Add to Favorites';
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
                            ${post.is_pinned ? '<span class="post-pinned"><i class="fas fa-thumbtack"></i> pinned</span>' : ''}
                            <span class="post-author">u/${escapeHtml(post.author_name || post.author_username)}</span>
                            <span class="post-time">${formatTime(post.created_at)}</span>
                            ${post.is_edited ? '<span class="post-time">(edited)</span>' : ''}
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
                                                html += `
                                                    <div style="position: relative; max-width: 300px; max-height: 300px;">
                                                        <img src="${att.url}" alt="${escapeHtml(att.name)}" 
                                                             style="max-width: 100%; max-height: 300px; object-fit: contain; border-radius: 4px; cursor: pointer;" 
                                                             onclick="event.stopPropagation(); window.open('${att.url}', '_blank');"
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
    window.location.href = `post-detail.html?id=${postId}`;
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
                    })().map(att => `
                        <a href="${att.url}" target="_blank" class="attachment-file" style="display: inline-flex; margin-right: 10px;">
                            <i class="fas ${getFileIcon(att.name)}"></i>
                            ${escapeHtml(att.name)}
                        </a>
                    `).join('')}
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

