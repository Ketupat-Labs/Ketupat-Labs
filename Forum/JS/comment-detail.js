let commentState = {
    commentId: null,
    postId: null,
    comment: null
};

document.addEventListener('DOMContentLoaded', () => {
    if (sessionStorage.getItem('userLoggedIn') !== 'true') {
        window.location.href = 'login.html';
        return;
    }
    
    initEventListeners();
    loadCommentDetail();
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

async function loadCommentDetail() {
    const urlParams = new URLSearchParams(window.location.search);
    const commentId = urlParams.get('comment_id');
    const postId = urlParams.get('post_id');
    
    if (!commentId || !postId) {
        showError('Comment ID and Post ID are required');
        return;
    }
    
    commentState.commentId = commentId;
    commentState.postId = postId;
    
    try {
        const response = await fetch(`../api/forum_endpoints.php?action=get_comment_detail&comment_id=${commentId}`);
        const data = await response.json();
        
        if (data.status === 200) {
            commentState.comment = data.data.comment;
            renderCommentDetail();
        } else {
            showError(data.message || 'Failed to load comment');
        }
    } catch (error) {
        console.error('Error loading comment detail:', error);
        showError('Failed to load comment');
    }
}

function renderCommentDetail() {
    const container = document.getElementById('commentDetailContent');
    const comment = commentState.comment;
    const post = comment.post || {};
    
    container.innerHTML = `
        <!-- Back Link -->
        <div style="margin-bottom: 16px;">
            <a href="post-detail.html?id=${post.id || commentState.postId}" style="color: #ff4500; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Post</span>
            </a>
        </div>
        
        <!-- Post Preview -->
        ${post.title ? `
            <div class="post-detail-card" style="margin-bottom: 24px; padding: 16px; background: #fff; border-radius: 8px; border: 1px solid #edeff1; word-wrap: break-word; overflow-wrap: break-word;">
                <div style="font-size: 12px; color: #878a8c; margin-bottom: 8px;">Original Post</div>
                <div style="font-weight: 600; font-size: 18px; margin-bottom: 8px; color: #1c1c1c; word-wrap: break-word; overflow-wrap: break-word;">
                    ${escapeHtml(post.title)}
                </div>
                <div style="font-size: 14px; color: #787c7e; word-wrap: break-word; overflow-wrap: break-word; max-width: 100%;">
                    ${escapeHtml(post.content ? (post.content.length > 200 ? post.content.substring(0, 200) + '...' : post.content) : '')}
                </div>
            </div>
        ` : ''}
        
        <!-- Main Comment -->
        <div class="comment-item" style="border-left: 3px solid #ff4500; padding-left: 12px; margin-bottom: 24px; background: #fff; padding: 16px; border-radius: 8px; border: 1px solid #edeff1;">
            <div class="comment-header">
                <div class="comment-avatar">
                    ${getUserInitials(comment.author_name || comment.author_username)}
                </div>
                <div class="comment-author-info" style="flex: 1;">
                    <span class="comment-author-name">
                        ${escapeHtml(comment.author_name || comment.author_username)}
                    </span>
                    <span class="comment-time">${formatTime(comment.created_at)}</span>
                </div>
                <div style="display: flex; gap: 12px; align-items: center; font-size: 12px; color: #878a8c;">
                    <span title="Likes">
                        <i class="fas fa-heart" style="color: #ff4500;"></i> ${comment.reaction_count || 0}
                    </span>
                    ${comment.reply_count > 0 ? `
                        <span title="Replies">
                            <i class="fas fa-comments"></i> ${comment.reply_count}
                        </span>
                    ` : ''}
                </div>
            </div>
            <div class="comment-body" style="margin-top: 12px; font-size: 16px; line-height: 1.6;">
                ${escapeHtml(comment.content)}
                ${comment.is_edited ? '<span style="font-style: italic; color: #787c7e; margin-left: 4px;">(edited)</span>' : ''}
            </div>
            
            <div class="comment-actions" style="margin-top: 16px;">
                <button class="comment-action-btn" onclick="toggleCommentLike(${comment.id})">
                    <i class="far fa-heart"></i>
                    <span>Like</span>
                </button>
                <button class="comment-action-btn" onclick="toggleReplyForm(${comment.id})">
                    <i class="far fa-comment"></i>
                    <span>Reply</span>
                </button>
                <button class="comment-action-btn" onclick="shareComment(${comment.id})">
                    <i class="fas fa-share"></i>
                    <span>Share</span>
                </button>
            </div>
            
            <!-- Reply Form -->
            <div class="reply-form-container" id="replyForm_${comment.id}" style="display: none; margin-top: 16px; padding: 16px; background: #f7f9fa; border-radius: 4px;">
                <div class="comment-input-container">
                    <div class="comment-input-avatar">
                        ${getCurrentUserInitials()}
                    </div>
                    <div class="comment-input-wrapper" style="flex: 1;">
                        <textarea id="replyInput_${comment.id}" class="comment-input" placeholder="Write a reply..." style="min-height: 80px;" required></textarea>
                        <div style="display: flex; gap: 8px; margin-top: 8px;">
                            <button type="button" class="comment-submit-btn" onclick="submitReply(${comment.id}, ${commentState.postId})">Reply</button>
                            <button type="button" class="comment-cancel-btn" onclick="cancelReply(${comment.id})" style="padding: 8px 16px; background: #e4e6eb; color: #1c1c1c; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- All Replies Section -->
        <div class="comments-container">
            <div class="comments-header" style="margin-bottom: 16px;">
                <i class="far fa-comment"></i>
                <span>All Replies (${comment.reply_count || 0})</span>
            </div>
            <div id="repliesContainer" style="position: relative;">
                ${comment.replies && comment.replies.length > 0 
                    ? (() => {
                        // Flatten all replies into a single list
                        const flattenReplies = (replies, parentAuthor = null) => {
                            const flatList = [];
                            replies.forEach(reply => {
                                flatList.push({ ...reply, parentAuthor, isReply: parentAuthor !== null });
                                if (reply.replies && reply.replies.length > 0) {
                                    const nested = flattenReplies(reply.replies, reply.author_name || reply.author_username);
                                    flatList.push(...nested);
                                }
                            });
                            return flatList;
                        };
                        const flatReplies = flattenReplies(comment.replies, comment.author_name || comment.author_username);
                        // Sort by creation date (newest first)
                        const sortedReplies = flatReplies.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                        return sortedReplies.map((reply, index) => renderReplyItem(reply, 0, comment.id, index === sortedReplies.length - 1, reply.parentAuthor)).join('');
                    })()
                    : '<p style="color: #878a8c; text-align: center; padding: 20px;">No replies yet</p>'
                }
            </div>
        </div>
    `;
}

let collapsedComments = new Set();

function toggleCommentCollapse(commentId) {
    if (collapsedComments.has(commentId)) {
        collapsedComments.delete(commentId);
    } else {
        collapsedComments.add(commentId);
    }
    renderCommentDetail();
}

function calculateLastReplyDepth(reply, depth) {
    if (!reply.replies || reply.replies.length === 0) {
        return depth;
    }
    return Math.max(...reply.replies.map(r => calculateLastReplyDepth(r, depth + 1)));
}

function renderReplyItem(reply, depth = 0, parentId = null, isLastChild = false, parentAuthor = null) {
    const isReply = reply.isReply || false;
    
    return `
        <div class="comment-item" data-comment-id="${reply.id}" style="padding: 12px 0; margin-bottom: 16px; border-bottom: 1px solid #edeff1;">
            <div class="comment-header" style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                <div class="comment-avatar" style="width: 32px; height: 32px; font-size: 12px; flex-shrink: 0; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                    ${getUserInitials(reply.author_name || reply.author_username)}
                </div>
                <div class="comment-author-info" style="flex: 1; display: flex; align-items: center; gap: 6px;">
                    <span class="comment-author-name" style="font-size: 14px; font-weight: 600; color: #1c1c1c;">
                        ${escapeHtml(reply.author_name || reply.author_username)}
                    </span>
                    <span style="font-size: 12px; color: #6b7280;">•</span>
                    <span class="comment-time" style="font-size: 12px; color: #6b7280;">
                        ${formatTime(reply.created_at)}
                    </span>
                </div>
                ${reply.reaction_count > 0 ? `
                    <span style="font-size: 12px; color: #6b7280; display: flex; align-items: center; gap: 4px;" title="Likes">
                        <i class="fas fa-heart" style="color: #ff4500; font-size: 11px;"></i>
                        <span>${reply.reaction_count}</span>
                    </span>
                ` : ''}
            </div>
            
            <div class="comment-body" style="font-size: 15px; margin-left: 40px; margin-top: 4px; color: #1c1c1c; word-wrap: break-word; overflow-wrap: break-word; line-height: 1.6;">
                ${parentAuthor && isReply ? `<span style="color: #2454FF; font-weight: 600; margin-right: 4px;">@${escapeHtml(parentAuthor)}</span>` : ''}${escapeHtml(reply.content)}
                ${reply.is_edited ? '<span style="font-style: italic; color: #6b7280; margin-left: 4px; font-size: 12px;">(edited)</span>' : ''}
            </div>
            
            <div class="comment-actions" style="margin-top: 8px; margin-left: 40px; display: flex; gap: 16px; align-items: center;">
                <button class="comment-action-btn" onclick="toggleCommentLike(${reply.id})" style="background: transparent; border: none; color: #6b7280; cursor: pointer; font-size: 13px; padding: 4px 8px; display: flex; align-items: center; gap: 4px; transition: color 0.2s;">
                    <i class="far fa-heart"></i>
                    <span>Like</span>
                </button>
                <button class="comment-action-btn" onclick="toggleReplyForm(${reply.id})" style="background: transparent; border: none; color: #6b7280; cursor: pointer; font-size: 13px; padding: 4px 8px; display: flex; align-items: center; gap: 4px; transition: color 0.2s;">
                    <i class="far fa-comment"></i>
                    <span>Reply</span>
                </button>
                <button class="comment-action-btn" onclick="shareComment(${reply.id})" style="background: transparent; border: none; color: #6b7280; cursor: pointer; font-size: 13px; padding: 4px 8px; display: flex; align-items: center; gap: 4px; transition: color 0.2s;">
                    <i class="fas fa-share"></i>
                    <span>Share</span>
                </button>
            </div>
            
            <!-- Reply Form -->
            <div class="reply-form-container" id="replyForm_${reply.id}" style="display: none; margin-top: 12px; margin-left: 40px; padding: 12px; background: #f7f9fa; border-radius: 4px;">
                <div class="comment-input-container">
                    <div class="comment-input-avatar" style="width: 32px; height: 32px; font-size: 12px;">
                        ${getCurrentUserInitials()}
                    </div>
                    <div class="comment-input-wrapper" style="flex: 1;">
                        <textarea id="replyInput_${reply.id}" class="comment-input" placeholder="Write a reply..." style="min-height: 60px;" required></textarea>
                        <div style="display: flex; gap: 8px; margin-top: 8px;">
                            <button type="button" class="comment-submit-btn" onclick="submitReply(${reply.id}, ${commentState.postId})">Reply</button>
                            <button type="button" class="comment-cancel-btn" onclick="cancelReply(${reply.id})" style="padding: 8px 16px; background: #e4e6eb; color: #1c1c1c; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
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
            toggleReplyForm(commentId);
            await loadCommentDetail();
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
            await loadCommentDetail();
        }
    } catch (error) {
        console.error('Error toggling comment like:', error);
    }
}

function shareComment(commentId) {
    const url = window.location.href;
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

window.toggleCommentCollapse = toggleCommentCollapse;

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

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showError(message) {
    alert(message);
}

