let postState = {
    postId: null,
    post: null
};

document.addEventListener('DOMContentLoaded', () => {
    // Check if user is logged in
    if (sessionStorage.getItem('userLoggedIn') !== 'true') {
        window.location.href = 'login.html';
        return;
    }

    initEventListeners();
    loadPostDetail();
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
            window.location.href = 'create-post.html';
        });
    }

    const btnCreateForum = document.getElementById('btnCreateForum');
    if (btnCreateForum) {
        btnCreateForum.addEventListener('click', () => {
            window.location.href = 'create-forum.html';
        });
    }
}

async function loadPostDetail() {
    const urlParams = new URLSearchParams(window.location.search);
    const postId = urlParams.get('id');

    if (!postId) {
        showError('No post selected');
        return;
    }

    postState.postId = postId;

    // Track visited post
    trackVisitedPost(postId);

    try {
        const response = await fetch(`../api/forum_endpoints.php?action=get_posts&post_id=${postId}`);
        const data = await response.json();

        if (data.status === 200 && data.data.posts.length > 0) {
            postState.post = data.data.posts[0];
            renderPostDetail(postState.post);
            loadSidebarData();
            loadAboutCommunity();
        } else {
            showError('Failed to load post details');
        }
    } catch (error) {
        console.error('Error loading post details:', error);
        showError('Failed to load post');
    }
}

function renderPostDetail(post) {
    const container = document.getElementById('postDetailContent');

    // Get forum name from the post - we'll need to add this to the API response
    const forumName = post.forum_name || 'Forum';
    const forumInitials = forumName.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();

    container.innerHTML = `
        <!-- Post Card -->
        <div class="post-detail-card">
            <div class="post-vote-section">
                <button class="vote-btn like ${post.user_reacted ? 'active' : ''}" onclick="toggleReaction(${post.id})">
                    <i class="${post.user_reacted ? 'fas' : 'far'} fa-heart"></i>
                </button>
                <div class="vote-count">${post.reaction_count || 0}</div>
                <button class="vote-btn" style="display: none;">
                    <i class="fas fa-heart"></i>
                </button>
            </div>
            <div class="post-content-section">
                <div class="post-detail-header">
                    <div class="post-detail-header-left">
                        <button class="post-back-link" onclick="window.history.back()" title="Back">
                            <i class="fas fa-arrow-left"></i>
                        </button>
                        <div class="post-community-avatar">${forumInitials}</div>
                        <div>
                            <div class="post-community-info">
                                <span class="post-community-name">r/${escapeHtml(forumName)}</span>
                                <span class="post-time">${formatTime(post.created_at)}</span>
                            </div>
                        </div>
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
                <div class="post-detail-title">
                    ${escapeHtml(post.title)}
                </div>
                ${post.tags ? `
                    <div class="post-tags">
                        ${(() => {
                try {
                    const tags = JSON.parse(post.tags);
                    return Array.isArray(tags) ? tags : [];
                } catch (e) {
                    return [];
                }
            })().map(tag => `
                            <span class="post-tag">${escapeHtml(tag)}</span>
                        `).join('')}
                    </div>
                ` : ''}
                <div class="post-detail-body">
                    ${escapeHtml(post.content)}
                </div>
                ${post.attachments ? `
                    <div style="margin-bottom: 16px; margin-top: 16px;">
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
                        html += '<div class="post-image-preview" style="margin-top: 12px; display: flex; flex-wrap: wrap; gap: 12px;">';
                        imageAttachments.forEach(att => {
                            html += `
                                            <div style="position: relative; max-width: 500px; max-height: 500px;">
                                                <img src="${att.url}" alt="${escapeHtml(att.name)}" 
                                                     style="max-width: 100%; max-height: 500px; object-fit: contain; border-radius: 8px; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.1);" 
                                                     onclick="window.open('${att.url}', '_blank');"
                                                     onerror="this.style.display='none';">
                                            </div>
                                        `;
                        });
                        html += '</div>';
                    }

                    // Show other file attachments as links
                    if (otherAttachments.length > 0) {
                        html += '<div style="margin-top: 12px; display: flex; flex-wrap: wrap; gap: 8px;">';
                        otherAttachments.forEach(att => {
                            html += `
                            <a href="${att.url}" target="_blank" class="attachment-item">
                                <i class="fas ${getFileIcon(att.name)}"></i>
                                <span>${escapeHtml(att.name)}</span>
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
                <div class="post-detail-footer">
                    <div class="post-detail-actions">
                        <button class="btn-post-action" onclick="toggleReaction(${post.id})">
                            <i class="far fa-comment"></i>
                            <span>${post.reply_count || 0}</span>
                        </button>
                        <button class="btn-post-action">
                            <i class="far fa-bookmark"></i>
                            <span>Save</span>
                        </button>
                        <button class="btn-post-action ${post.user_reacted ? 'liked' : ''}" onclick="toggleReaction(${post.id})">
                            <i class="${post.user_reacted ? 'fas' : 'far'} fa-heart"></i>
                            <span>${post.reaction_count || 0}</span>
                        </button>

                    </div>
                </div>
            </div>
        </div>
        
        <!-- Comments Container -->
        <div class="comments-container">
            <div class="comments-header">
                <i class="far fa-comment"></i>
                <span>${post.reply_count || 0} Comments</span>
            </div>
            <div id="commentsContainer">
            </div>
            <div class="comment-form">
                <form id="commentForm" onsubmit="submitComment(event)">
                    <div class="comment-input-container">
                        <div class="comment-input-avatar">
                            ${getCurrentUserInitials()}
                        </div>
                        <div class="comment-input-wrapper">
                            <textarea id="commentInput" class="comment-input" placeholder="Add a comment..." required></textarea>
                            <button type="submit" class="comment-submit-btn">Comment</button>
                        </div>
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
            // Debug: Log full comment structure recursively
            function logCommentStructure(comment, depth = 0, prefix = '') {
                const indent = '  '.repeat(depth);
                console.log(`${indent}${prefix}Comment ${comment.id} (depth ${depth}) - "${comment.content?.substring(0, 20)}..."`);
                if (comment.replies && Array.isArray(comment.replies)) {
                    console.log(`${indent}  └─ Has ${comment.replies.length} direct replies`);
                    comment.replies.forEach((reply, idx) => {
                        logCommentStructure(reply, depth + 1, `Reply ${idx + 1}: `);
                    });
                } else {
                    console.log(`${indent}  └─ No replies array or empty`);
                }
            }

            console.log('=== FULL COMMENT TREE STRUCTURE ===');
            data.data.comments.forEach((comment, idx) => {
                console.log(`\n--- Top-Level Comment ${idx + 1} (ID: ${comment.id}) ---`);
                logCommentStructure(comment, 0);
            });
            console.log('=== END COMMENT TREE ===');

            renderComments(data.data.comments);
        }
    } catch (error) {
        console.error('Error loading comments:', error);
    }
}

let expandedReplies = new Set();
let collapsedComments = new Set();

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

    container.innerHTML = sortedComments.map((comment, index) => {
        return renderCommentItem(comment, 0, 0, null, index === sortedComments.length - 1, comment.parentAuthor);
    }).join('');
}

function renderCommentItem(comment, depth = 0, maxRepliesToShow = 3, parentId = null, isLastChild = false, parentAuthor = null) {
    const isReply = comment.isReply || false;

    return `
        <div class="comment-item" data-comment-id="${comment.id}" style="padding: 12px 0; margin-bottom: 16px; border-bottom: 1px solid #edeff1;">
            <div class="comment-header" style="display: flex; align-items: center; gap: 6px; margin-bottom: 6px;">
                <div class="comment-avatar" style="width: 32px; height: 32px; font-size: 12px; flex-shrink: 0; border-radius: 50%; background: #0079d3; color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                    ${getUserInitials(comment.author_name || comment.author_username)}
                </div>
                <div class="comment-author-info" style="flex: 1; display: flex; align-items: center; gap: 4px;">
                    <span class="comment-author-name" style="font-size: 12px; font-weight: 600; color: #1c1c1c;">
                        ${escapeHtml(comment.author_name || comment.author_username)}
                    </span>
                    <span style="font-size: 12px; color: #878a8c; margin: 0 2px;">•</span>
                    <span class="comment-time" style="font-size: 12px; color: #878a8c;">
                        ${formatTime(comment.created_at)}
                    </span>
                </div>
                ${comment.reaction_count > 0 ? `
                    <span style="font-size: 12px; color: #6b7280; display: flex; align-items: center; gap: 4px;" title="Likes">
                        <i class="fas fa-heart" style="color: #ff4500; font-size: 11px;"></i>
                        <span>${comment.reaction_count}</span>
                    </span>
                ` : ''}
            </div>
            <div class="comment-body" style="font-size: 14px; margin-left: 40px; margin-top: 4px; color: #1c1c1c; word-wrap: break-word; overflow-wrap: break-word; line-height: 1.5;">
                ${parentAuthor && isReply ? `<span style="color: #2454FF; font-weight: 600; margin-right: 4px;">@${escapeHtml(parentAuthor)}</span>` : ''}${escapeHtml(comment.content)}
                ${comment.is_edited ? '<span style="font-style: italic; color: #878a8c; margin-left: 4px; font-size: 11px;">(edited)</span>' : ''}
            </div>
            
            <!-- Reply Form (hidden by default) -->
            <div class="reply-form-container" id="replyForm_${comment.id}" style="display: none; margin-top: 12px; margin-left: 40px; padding: 8px 0;">
                <div class="comment-input-container">
                    <div class="comment-input-avatar" style="width: 32px; height: 32px; font-size: 12px; background: #0079d3;">
                        ${getCurrentUserInitials()}
                    </div>
                    <div class="comment-input-wrapper" style="flex: 1;">
                        <textarea id="replyInput_${comment.id}" class="comment-input" placeholder="Write a reply..." style="min-height: 60px; border: 1px solid #edeff1; border-radius: 4px;" required></textarea>
                        <div style="display: flex; gap: 8px; margin-top: 8px;">
                            <button type="button" class="comment-submit-btn" onclick="event.stopPropagation(); submitReply(${comment.id}, ${postState.postId})" style="padding: 6px 16px; font-size: 13px;">Reply</button>
                            <button type="button" class="comment-cancel-btn" onclick="event.stopPropagation(); cancelReply(${comment.id})" style="padding: 6px 16px; background: transparent; color: #878a8c; border: none; cursor: pointer; font-size: 13px; font-weight: 700;">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="comment-actions" style="margin-top: 6px; margin-left: 40px; display: flex; gap: 12px; align-items: center;" onclick="event.stopPropagation();">
                <button class="comment-action-btn" onclick="toggleCommentLike(${comment.id})" style="background: transparent; border: none; color: #878a8c; cursor: pointer; font-size: 12px; padding: 2px 4px; display: flex; align-items: center; gap: 4px; font-weight: 700;">
                    <i class="far fa-heart" style="font-size: 14px;"></i>
                    <span>${comment.reaction_count > 0 ? comment.reaction_count : ''}</span>
                </button>
                <button class="comment-action-btn" onclick="toggleReplyForm(${comment.id})" style="background: transparent; border: none; color: #878a8c; cursor: pointer; font-size: 12px; padding: 2px 4px; display: flex; align-items: center; gap: 4px; font-weight: 700;">
                    <i class="far fa-comment" style="font-size: 14px;"></i>
                    <span>Reply</span>
                </button>
            </div>
        </div>
    `;
}

function toggleCommentCollapsePost(commentId) {
    if (collapsedComments.has(commentId)) {
        collapsedComments.delete(commentId);
    } else {
        collapsedComments.add(commentId);
    }
    loadComments(postState.postId);
}

function showMoreReplies(commentId, totalReplies) {
    expandedReplies.add(commentId);
    loadComments(postState.postId);
}

function openCommentDetail(commentId) {
    window.location.href = `comment-detail.html?comment_id=${commentId}&post_id=${postState.postId}`;
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
            await loadPostDetail();
        }
    } catch (error) {
        console.error('Error toggling reaction:', error);
    }
}

async function toggleBookmark(postId) {
    try {
        const response = await fetch('../api/forum_endpoints.php?action=bookmark_post', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                post_id: postId,
                action: postState.post.is_bookmarked ? 'remove' : 'add'
            })
        });

        const data = await response.json();

        if (data.status === 200) {
            // Reload post to get updated bookmark status
            await loadPostDetail();
        }
    } catch (error) {
        console.error('Error toggling bookmark:', error);
    }
}

async function submitComment(event) {
    event.preventDefault();

    const content = document.getElementById('commentInput').value;

    if (!content.trim()) {
        return;
    }

    try {
        const response = await fetch('../api/forum_endpoints.php?action=create_comment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                post_id: postState.postId,
                content: content.trim()
            })
        });

        const data = await response.json();

        if (data.status === 200) {
            document.getElementById('commentInput').value = '';
            await loadComments(postState.postId);
        } else {
            showError(data.message);
        }
    } catch (error) {
        console.error('Error posting comment:', error);
        showError('Failed to post comment');
    }
}

async function submitReply(commentId, postId) {
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
            toggleReplyForm(commentId); // Hide the form
            await loadComments(postId);
        } else {
            showError(data.message);
        }
    } catch (error) {
        console.error('Error posting reply:', error);
        showError('Failed to post reply');
    }
}

function toggleReplyForm(commentId) {
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

function cancelReply(commentId) {
    const replyForm = document.getElementById(`replyForm_${commentId}`);
    if (replyForm) {
        replyForm.style.display = 'none';
        const textarea = document.getElementById(`replyInput_${commentId}`);
        if (textarea) {
            textarea.value = '';
        }
    }
}

async function toggleCommentLike(commentId) {
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
            await loadComments(postState.postId);
        }
    } catch (error) {
        console.error('Error toggling comment like:', error);
    }
}

function shareComment(commentId) {
    const url = window.location.href.split('?')[0] + `?id=${postState.postId}#comment-${commentId}`;
    if (navigator.share) {
        navigator.share({
            title: 'Comment',
            url: url
        }).catch(() => {
            copyToClipboard(url);
        });
    } else {
        copyToClipboard(url);
    }
}

function copyToClipboard(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    alert('Comment link copied to clipboard!');
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
        return `${days} day${days > 1 ? 's' : ''} ago`;
    } else if (hours > 0) {
        return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    } else if (minutes > 0) {
        return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
    } else {
        return 'just now';
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

function getUserInitials(name) {
    if (!name) return '?';
    const words = name.split(' ');
    if (words.length >= 2) {
        return (words[0][0] + words[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
}

function getCurrentUserInitials() {
    const userName = sessionStorage.getItem('userEmail') || 'User';
    return getUserInitials(userName);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

async function loadSidebarData() {
    await loadForumsToSidebar();
    await loadTagsForForum();
}

async function loadForumsToSidebar() {
    try {
        const response = await fetch(`../api/forum_endpoints.php?action=get_forums`);
        const data = await response.json();

        if (data.status === 200) {
            renderForumsToSidebar(data.data.forums);
        }
    } catch (error) {
        console.error('Error loading forums:', error);
    }
}

function renderForumsToSidebar(forums) {
    const container = document.getElementById('forumsList');

    if (!forums || forums.length === 0) {
        container.innerHTML = `
            <p style="padding: 12px; color: #878a8c; font-size: 14px;">
                No forums yet. Create one to get started!
            </p>
        `;
        return;
    }

    container.innerHTML = forums.map(forum => `
        <div class="filter-item" 
             onclick="window.location.href='forum-detail.html?id=${forum.id}'">
            <i class="fas fa-comments" style="color: #878a8c;"></i>
            <span>${escapeHtml(forum.title)}</span>
        </div>
    `).join('');
}

async function loadTagsForForum() {
    if (!postState.post) return;

    try {
        const response = await fetch(`../api/forum_endpoints.php?action=get_forum_tags&forum_id=${postState.post.forum_id}`);
        const data = await response.json();

        if (data.status === 200) {
            renderTagsToSidebar(data.data.tags || []);
        } else {
            // Fallback: try to parse tags from the post
            renderTagsFromPost();
        }
    } catch (error) {
        console.error('Error loading forum tags:', error);
        renderTagsFromPost();
    }
}

function renderTagsToSidebar(tags) {
    const container = document.getElementById('tagCloud');

    if (!tags || tags.length === 0) {
        container.innerHTML = `
            <p style="padding: 8px 16px; color: #878a8c; font-size: 12px;">
                No tags yet
            </p>
        `;
        return;
    }

    container.innerHTML = tags.slice(0, 10).map(tag => `
        <span class="tag-chip">#${escapeHtml(tag)}</span>
    `).join('');
}

function renderTagsFromPost() {
    const container = document.getElementById('tagCloud');

    if (!postState.post || !postState.post.tags) {
        container.innerHTML = `
            <p style="padding: 8px 16px; color: #878a8c; font-size: 12px;">
                No tags yet
            </p>
        `;
        return;
    }

    try {
        const tags = typeof postState.post.tags === 'string' ? JSON.parse(postState.post.tags) : postState.post.tags;

        if (!Array.isArray(tags) || tags.length === 0) {
            container.innerHTML = `
                <p style="padding: 8px 16px; color: #878a8c; font-size: 12px;">
                    No tags yet
                </p>
            `;
            return;
        }

        container.innerHTML = tags.slice(0, 10).map(tag => `
            <span class="tag-chip">#${escapeHtml(tag)}</span>
        `).join('');
    } catch (e) {
        container.innerHTML = `
            <p style="padding: 8px 16px; color: #878a8c; font-size: 12px;">
                No tags yet
            </p>
        `;
    }
}

async function loadAboutCommunity() {
    if (!postState.post) return;

    try {
        const response = await fetch(`../api/forum_endpoints.php?action=get_forum_details&forum_id=${postState.post.forum_id}`);
        const data = await response.json();

        if (data.status === 200) {
            renderAboutCommunity(data.data.forum);
        }
    } catch (error) {
        console.error('Error loading forum details:', error);
    }
}

function renderAboutCommunity(forum) {
    const container = document.getElementById('aboutCommunity');

    if (!forum) {
        container.innerHTML = '<p style="color: #878a8c;">Unable to load community details</p>';
        return;
    }

    const forumInitials = forum.title ? forum.title.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase() : '??';

    container.innerHTML = `
        <div class="forum-description">
            ${escapeHtml(forum.description || 'No description available.')}
        </div>
        
        <div class="forum-meta">
            <div class="forum-meta-item">
                <i class="fas fa-home"></i>
                <span>Created ${formatDate(forum.created_at)}</span>
            </div>
            <div class="forum-meta-item">
                <i class="fas fa-globe"></i>
                <span>${escapeHtml(forum.visibility || 'Public')}</span>
            </div>
        </div>

        <div class="forum-stats-grid">
            <div class="forum-stat-big">
                <div class="forum-stat-value">${forum.member_count || 0}</div>
                <div class="forum-stat-label">MEMBERS</div>
            </div>
            <div class="forum-stat-big">
                <div class="forum-stat-value">${forum.post_count || 0}</div>
                <div class="forum-stat-label">POSTS</div>
            </div>
        </div>

        <button class="btn-message-mods" style="margin-top: 16px; width: 100%;" onclick="messageMods()">
            <i class="fas fa-comment"></i>
            Message Mods
        </button>
    `;
}

function messageMods() {
    // Navigate to messaging page
    window.location.href = '../Messaging/messaging.html';
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
            // Redirect back to forum or forum detail page
            if (postState.post && postState.post.forum_id) {
                window.location.href = `forum-detail.html?id=${postState.post.forum_id}`;
            } else {
                window.location.href = 'forum.html';
            }
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
    const postUrl = `${window.location.origin}${window.location.pathname}?id=${postId}`;
    
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
window.toggleReaction = toggleReaction;
window.toggleBookmark = toggleBookmark;
window.submitComment = submitComment;
window.submitReply = submitReply;
window.toggleReplyForm = toggleReplyForm;
window.cancelReply = cancelReply;
window.toggleCommentLike = toggleCommentLike;
window.shareComment = shareComment;
window.showMoreReplies = showMoreReplies;
window.openCommentDetail = openCommentDetail;
window.toggleCommentCollapsePost = toggleCommentCollapsePost;
window.messageMods = messageMods;
window.togglePostOptions = togglePostOptions;
window.confirmDeletePost = confirmDeletePost;
window.openShareModal = openShareModal;
window.closeShareModal = closeShareModal;
window.shareToConversation = shareToConversation;

function trackVisitedPost(postId) {
    try {
        let recentPosts = JSON.parse(localStorage.getItem('recentPosts') || '[]');

        // Remove if already exists
        recentPosts = recentPosts.filter(p => p.id !== postId);

        // Add to beginning
        recentPosts.unshift({
            id: postId,
            visitedAt: new Date().toISOString()
        });

        // Keep only last 10
        recentPosts = recentPosts.slice(0, 10);

        localStorage.setItem('recentPosts', JSON.stringify(recentPosts));
    } catch (error) {
        console.error('Error tracking visited post:', error);
    }
}




