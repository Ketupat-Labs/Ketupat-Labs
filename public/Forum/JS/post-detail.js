let postState = {
    postId: null,
    post: null
};

let commentState = {
    comments: [],
    sort: 'recent', // 'recent', 'top', 'oldest'
    offset: 0,
    limit: 20,
    topLimit: 10, // Show only top 10 comments initially
    hasMore: true,
    isLoading: false,
    totalCount: 0
};

document.addEventListener('DOMContentLoaded', () => {
    // Check if user is logged in
    if (sessionStorage.getItem('userLoggedIn') !== 'true') {
        window.location.href = '/login';
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
            window.location.href = '../login.html';
        });
    }

    const btnCreatePost = document.getElementById('btnCreatePost');
    if (btnCreatePost) {
        btnCreatePost.addEventListener('click', () => {
            // Get current forum ID from post if available
            const forumId = postState.post?.forum_id;
            if (forumId) {
                const referrer = `forum-detail.html?id=${forumId}`;
                window.location.href = `create-post.html?forum=${forumId}&referrer=${encodeURIComponent(referrer)}`;
            } else {
                window.location.href = 'create-post.html?referrer=post-detail.html' + window.location.search;
            }
        });
    }

    const btnCreateForum = document.getElementById('btnCreateForum');
    if (btnCreateForum) {
        btnCreateForum.addEventListener('click', () => {
            window.location.href = '/forum/create';
        });
    }
}

async function loadPostDetail() {
    // Extract post ID from URL path (e.g., /forum/post/12)
    let postId = null;
    const pathParts = window.location.pathname.split('/').filter(part => part);
    
    // Look for 'post' in the path and get the ID after it
    const postIndex = pathParts.indexOf('post');
    if (postIndex !== -1 && postIndex + 1 < pathParts.length) {
        postId = pathParts[postIndex + 1];
    }
    
    // Fallback to query parameter if path extraction fails
    if (!postId) {
    const urlParams = new URLSearchParams(window.location.search);
        postId = urlParams.get('id');
    }

    if (!postId) {
        showError('No post selected');
        return;
    }

    postState.postId = postId;

    // Track visited post
    trackVisitedPost(postId);

    try {
        const response = await fetch(`/api/forum/post?post_id=${postId}`, {
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

        if (data.status === 200 && data.data && data.data.posts && data.data.posts.length > 0) {
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
                        <button class="post-back-link" onclick="goBackToReferrer()" title="Back">
                            <i class="fas fa-arrow-left"></i>
                        </button>
                        <div class="post-community-avatar">${forumInitials}</div>
                        <div>
                            <div class="post-community-info">
                                <span class="post-community-name">${escapeHtml(forumName)}</span>
                                <span class="post-time">${formatTime(post.created_at)}</span>
                            </div>
                        </div>
                    </div>
                    <div class="post-options-container" onclick="event.stopPropagation();">
                        <button class="post-options-btn" onclick="event.stopPropagation(); togglePostOptions(${post.id})" title="More options">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                        <div id="postOptions_${post.id}" class="post-options-menu hidden">
                            <button class="post-options-item" onclick="event.stopPropagation(); openShareModal(${post.id}, '${escapeHtml(post.title)}', '${escapeHtml(post.forum_name || 'Forum')}')">
                                <i class="fas fa-share"></i> Share
                            </button>
                            ${post.is_forum_member && getCurrentUserId() !== post.author_id ? `
                            <button class="post-options-item report-option" onclick="event.stopPropagation(); openReportModal(${post.id}, '${escapeHtml(post.title)}')">
                                <i class="fas fa-flag"></i> Report
                            </button>
                            ` : ''}
                            ${(getCurrentUserId() === post.author_id || (post.user_forum_role && ['admin', 'moderator'].includes(post.user_forum_role))) ? `
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
                </div>
                ${post.report_count >= 3 ? `
                <div class="report-warning-banner" style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; padding: 12px; margin: 12px 0; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-exclamation-triangle" style="color: #856404;"></i>
                    <span style="color: #856404; font-size: 14px;">This post has been reported ${post.report_count} times and is under review.</span>
                </div>
                ` : ''}
                <div class="post-detail-title">
                    ${escapeHtml(post.title)}
                </div>
                ${post.tags ? `
                    <div class="post-tags" style="margin-top: 0; margin-bottom: 6px;">
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
                    <div style="margin-bottom: 8px; margin-top: 8px;">
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
                        <button class="btn-post-action ${post.is_bookmarked ? 'bookmarked' : ''}" onclick="toggleBookmark(${post.id})">
                            <i class="${post.is_bookmarked ? 'fas' : 'far'} fa-bookmark"></i>
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
            <div class="comments-header" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-bottom: 1px solid #edeff1;">
                <div style="display: flex; align-items: center; gap: 8px;">
                <i class="far fa-comment"></i>
                <span>${post.reply_count || 0} Comments</span>
                </div>
                <div class="comment-sort-selector">
                    <select id="commentSortSelect" onchange="changeCommentSort(this.value)" style="padding: 4px 8px; border: 1px solid #edeff1; border-radius: 4px; font-size: 14px; background: white; cursor: pointer;">
                        <option value="recent" ${commentState.sort === 'recent' ? 'selected' : ''}>Newest</option>
                        <option value="top" ${commentState.sort === 'top' ? 'selected' : ''}>Top</option>
                        <option value="oldest" ${commentState.sort === 'oldest' ? 'selected' : ''}>Oldest</option>
                    </select>
                </div>
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

    // Reset comment state
    commentState.comments = [];
    commentState.offset = 0;
    commentState.hasMore = true;
    commentState.sort = 'recent';
    
    loadComments(post.id, true);
    
    // Initialize infinite scroll after a short delay to ensure container exists
    setTimeout(() => {
        initInfiniteScroll();
    }, 100);
}

async function loadComments(postId, reset = false) {
    if (commentState.isLoading) return;
    
    commentState.isLoading = true;
    
    if (reset) {
        commentState.offset = 0;
        commentState.comments = [];
        commentState.hasMore = true;
    }
    
    try {
        const params = new URLSearchParams({
            sort: commentState.sort,
            limit: commentState.limit.toString(),
            offset: commentState.offset.toString()
        });
        
        // If loading initial comments and sort is 'top', use top_limit
        if (reset && commentState.sort === 'top' && commentState.offset === 0) {
            params.append('top_limit', commentState.topLimit.toString());
        }
        
        const response = await fetch(`/api/forum/post/${postId}/comments?${params.toString()}`, {
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
            const newComments = data.data.comments || [];
            
            if (reset) {
                commentState.comments = newComments;
                } else {
                // Append new comments to existing ones
                commentState.comments = [...commentState.comments, ...newComments];
            }
            
            commentState.hasMore = data.data.has_more || false;
            commentState.totalCount = data.data.total || 0;
            commentState.offset = commentState.comments.length;
            
            renderComments(commentState.comments);
            updateLoadMoreButton();
        }
    } catch (error) {
        console.error('Error loading comments:', error);
    } finally {
        commentState.isLoading = false;
    }
}

let expandedReplies = new Set();
let collapsedComments = new Set();

// Sort function that maintains thread structure
function sortCommentsWithReplies(comments, sortType) {
    return comments.map(comment => {
        // Sort replies within this comment thread
        if (comment.replies && comment.replies.length > 0) {
            const sortedReplies = sortCommentsWithReplies(comment.replies, sortType);
            
            // Apply sort to replies based on sortType
            if (sortType === 'top' || sortType === 'popular') {
                sortedReplies.sort((a, b) => {
                    const scoreA = (a.reaction_count || 0) + (a.reply_count || 0);
                    const scoreB = (b.reaction_count || 0) + (b.reply_count || 0);
                    if (scoreB !== scoreA) return scoreB - scoreA;
                    return new Date(b.created_at) - new Date(a.created_at);
                });
            } else if (sortType === 'oldest') {
                sortedReplies.sort((a, b) => {
                    return new Date(a.created_at) - new Date(b.created_at);
                });
            } else { // recent (default)
                sortedReplies.sort((a, b) => {
                    return new Date(b.created_at) - new Date(a.created_at);
                });
            }
            
            comment.replies = sortedReplies;
        }
        
        return comment;
    });
}

function renderComments(comments) {
    const container = document.getElementById('commentsContainer');
    if (!container) return;

    if (!comments || comments.length === 0) {
        container.innerHTML = '<p style="color: #878a8c; text-align: center; padding: 20px;">No comments yet</p>';
        // Remove load more button if exists
        const loadMoreBtn = document.getElementById('loadMoreCommentsBtn');
        if (loadMoreBtn) loadMoreBtn.remove();
        const allLoadedMsg = document.querySelector('.all-comments-loaded');
        if (allLoadedMsg) allLoadedMsg.remove();
        return;
    }

    // Sort top-level comments
    let sortedComments = [...comments];
    if (commentState.sort === 'top' || commentState.sort === 'popular') {
        sortedComments.sort((a, b) => {
            const scoreA = (a.reaction_count || 0) + (a.reply_count || 0);
            const scoreB = (b.reaction_count || 0) + (b.reply_count || 0);
            if (scoreB !== scoreA) return scoreB - scoreA;
            return new Date(b.created_at) - new Date(a.created_at);
        });
    } else if (commentState.sort === 'oldest') {
        sortedComments.sort((a, b) => {
            return new Date(a.created_at) - new Date(b.created_at);
        });
    } else { // recent (default)
        sortedComments.sort((a, b) => {
            return new Date(b.created_at) - new Date(a.created_at);
        });
    }

    // Sort replies within each comment thread while maintaining structure
    sortedComments = sortCommentsWithReplies(sortedComments, commentState.sort);

    // Flatten for rendering while maintaining thread hierarchy
    const flattenComments = (comments, parentAuthor = null) => {
        const flatList = [];
        comments.forEach(comment => {
            // Add the comment itself
            flatList.push({ ...comment, parentAuthor, isReply: parentAuthor !== null });
            // Recursively add all replies (already sorted within thread)
            if (comment.replies && comment.replies.length > 0) {
                const parentName = comment.author_name || comment.author?.full_name || comment.author_username || comment.author?.username || 'Unknown';
                const replies = flattenComments(comment.replies, parentName);
                flatList.push(...replies);
            }
        });
        return flatList;
    };

    const flatComments = flattenComments(sortedComments);

    // Store current window scroll position
    const scrollTop = window.scrollY;
    const wasAtBottom = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 50;

    // Render comments with nested structure (Facebook-style)
    container.innerHTML = sortedComments.map((comment, index) => {
        return renderCommentItem(comment, 0, null, index === sortedComments.length - 1);
    }).join('');
    
    // Restore scroll position or scroll to bottom if was at bottom
    if (wasAtBottom) {
        window.scrollTo(0, document.documentElement.scrollHeight);
    } else {
        window.scrollTo(0, scrollTop);
    }
    
    // Add load more button if there are more comments
    updateLoadMoreButton();
}

function updateLoadMoreButton() {
    const container = document.getElementById('commentsContainer');
    if (!container) return;
    
    // Remove existing load more button and all loaded message
    const existingBtn = document.getElementById('loadMoreCommentsBtn');
    if (existingBtn) existingBtn.remove();
    const allLoadedMsg = document.querySelector('.all-comments-loaded');
    if (allLoadedMsg) allLoadedMsg.remove();
    
    // Add load more button if there are more comments
    if (commentState.hasMore && !commentState.isLoading) {
        const remaining = commentState.totalCount - commentState.comments.length;
        const loadMoreBtn = document.createElement('button');
        loadMoreBtn.id = 'loadMoreCommentsBtn';
        loadMoreBtn.className = 'load-more-comments-btn';
        loadMoreBtn.innerHTML = `
            <i class="fas fa-chevron-down"></i> 
            Load More Comments 
            ${remaining > 0 ? `(${remaining} remaining)` : ''}
        `;
        loadMoreBtn.onclick = () => loadMoreComments();
        container.appendChild(loadMoreBtn);
    } else if (!commentState.hasMore && commentState.comments.length > 0) {
        // Show "All comments loaded" message
        const allLoadedMsg = document.createElement('div');
        allLoadedMsg.className = 'all-comments-loaded';
        allLoadedMsg.style.cssText = 'text-align: center; padding: 16px; color: #878a8c; font-size: 14px;';
        allLoadedMsg.textContent = 'All comments loaded';
        container.appendChild(allLoadedMsg);
    }
}

async function loadMoreComments() {
    if (!commentState.hasMore || commentState.isLoading) return;
    await loadComments(postState.postId, false);
}

// Initialize infinite scroll using window scroll instead of container scroll
function initInfiniteScroll() {
    // Remove existing scroll listener if any
    window.removeEventListener('scroll', handleInfiniteScroll);
    
    // Add scroll listener to window instead of container
    window.addEventListener('scroll', handleInfiniteScroll);
}

function handleInfiniteScroll() {
    if (!commentState.hasMore || commentState.isLoading) return;
    
    // Check if user scrolled near bottom of the page (within 500px)
    const scrollBottom = window.innerHeight + window.scrollY;
    const documentHeight = document.documentElement.scrollHeight;
    
    if (documentHeight - scrollBottom < 500) {
        loadMoreComments();
    }
}

function renderCommentItem(comment, depth = 0, parentAuthor = null, isLastChild = false) {
    const hasReplies = comment.replies && comment.replies.length > 0;
    const hasNestedReplies = comment.nested_replies && comment.nested_replies.length > 0;
    const isExpanded = expandedReplies.has(comment.id);
    const isNestedExpanded = expandedReplies.has('nested_' + comment.id);
    const replyCount = hasReplies ? comment.replies.length : 0;
    const nestedReplyCount = hasNestedReplies ? comment.nested_replies_count : 0;
    const indentLeft = depth * 40; // 40px indentation per depth level

    return `
        <div class="comment-item fb-style" data-comment-id="${comment.id}" style="padding: ${depth === 0 ? '12px 0' : '8px 0'}; margin-left: ${indentLeft}px; ${depth === 0 ? 'border-bottom: 1px solid #e4e6eb;' : ''}">
            <div class="comment-content-wrapper" style="display: flex; gap: 8px;">
                <!-- Avatar -->
                <div class="comment-avatar" style="width: 32px; height: 32px; flex-shrink: 0; border-radius: 50%; background: linear-gradient(135deg, #1877f2 0%, #42a5f5 100%); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px;">
                    ${comment.author_avatar ? `<img src="${escapeHtml(comment.author_avatar)}" alt="${escapeHtml(comment.author_name || comment.author_username)}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">` : getUserInitials(comment.author_name || comment.author_username)}
                </div>
                
                <!-- Comment Content -->
                <div class="comment-main" style="flex: 1; min-width: 0;">
                    <!-- Comment Bubble -->
                    <div class="comment-bubble" style="background: #f0f2f5; border-radius: 18px; padding: 8px 12px; display: inline-block; max-width: 100%;">
                        <div class="comment-header-inline" style="display: flex; align-items: baseline; gap: 6px; margin-bottom: 4px;">
                            <span class="comment-author-name" style="font-size: 13px; font-weight: 600; color: #050505;">
                        ${escapeHtml(comment.author_name || comment.author_username)}
                    </span>
                        </div>
                        <div class="comment-body" id="commentBody_${comment.id}" style="font-size: 14px; color: #050505; word-wrap: break-word; overflow-wrap: break-word; line-height: 1.38;">
                            ${comment.quoted_content && comment.quoted_author ? `
                                <div style="background: #e4e6eb; border-left: 3px solid #1877f2; padding: 6px 10px; margin-bottom: 6px; border-radius: 4px; font-size: 13px;">
                                    <span style="color: #1877f2; font-weight: 600;">@${escapeHtml(comment.quoted_author)}</span>
                                    <div style="color: #65676b; margin-top: 4px; font-style: italic; max-height: 60px; overflow: hidden; text-overflow: ellipsis;">${escapeHtml(comment.quoted_content.length > 100 ? comment.quoted_content.substring(0, 100) + '...' : comment.quoted_content)}</div>
                                </div>
                            ` : parentAuthor ? `<span style="color: #1877f2; font-weight: 600; font-size: 13px; margin-right: 4px;">@${escapeHtml(parentAuthor)}</span>` : ''}
                            <span id="commentContent_${comment.id}">${formatCommentContent(comment.content)}</span>
                            ${comment.is_edited ? '<span style="font-style: italic; color: #65676b; margin-left: 4px; font-size: 12px;">(edited)</span>' : ''}
                        </div>
                    </div>
                    
                    <!-- Comment Actions -->
                    <div class="comment-actions" style="margin-top: 4px; margin-left: 4px; display: flex; align-items: center; gap: 16px; font-size: 12px; color: #65676b;" onclick="event.stopPropagation();">
                        <button class="comment-action-btn" onclick="toggleReplyForm(${comment.id})" style="background: transparent; border: none; color: #65676b; cursor: pointer; font-size: 12px; font-weight: 600; padding: 4px 8px; border-radius: 4px; transition: background 0.2s;">
                            Reply
                        </button>
                        ${comment.can_edit ? `
                            <button class="comment-action-btn" onclick="toggleCommentEdit(${comment.id})" style="background: transparent; border: none; color: #65676b; cursor: pointer; font-size: 12px; font-weight: 600; padding: 4px 8px; border-radius: 4px; transition: background 0.2s;">
                                Edit
                            </button>
                        ` : ''}
                        ${comment.can_delete ? `
                            <button class="comment-action-btn" onclick="confirmDeleteComment(${comment.id})" style="background: transparent; border: none; color: #dc2626; cursor: pointer; font-size: 12px; font-weight: 600; padding: 4px 8px; border-radius: 4px; transition: background 0.2s;">
                                Delete
                            </button>
                        ` : ''}
                        <span class="comment-time" style="font-size: 12px; color: #65676b;">
                        ${formatTime(comment.created_at)}
                    </span>
                </div>
                    
                    <!-- View Replies Button (for top-level comments) -->
                    ${hasReplies && !isExpanded && depth === 0 ? `
                        <button class="view-replies-btn" onclick="toggleReplies(${comment.id})" style="margin-top: 4px; margin-left: 4px; background: transparent; border: none; color: #1877f2; cursor: pointer; font-size: 12px; font-weight: 600; padding: 4px 8px; border-radius: 4px;">
                            <i class="fas fa-chevron-down" style="font-size: 10px; margin-right: 4px;"></i>
                            View ${replyCount} ${replyCount === 1 ? 'reply' : 'replies'}
                        </button>
                ` : ''}
                    
                    <!-- View Nested Replies Button (for replies that have nested replies) -->
                    ${hasNestedReplies && !isNestedExpanded && depth === 1 ? `
                        <button class="view-replies-btn" onclick="toggleReplies('nested_${comment.id}')" style="margin-top: 4px; margin-left: 4px; background: transparent; border: none; color: #1877f2; cursor: pointer; font-size: 12px; font-weight: 600; padding: 4px 8px; border-radius: 4px;">
                            <i class="fas fa-chevron-down" style="font-size: 10px; margin-right: 4px;"></i>
                            View ${nestedReplyCount} ${nestedReplyCount === 1 ? 'reply' : 'replies'}
                        </button>
                    ` : ''}
                    
                    <!-- Replies (all at level 1 with quotes) -->
                    ${hasReplies && isExpanded && depth === 0 ? `
                        <div class="comment-replies" style="margin-top: 8px;">
                            ${comment.replies.map((reply, index) => {
                                // All replies are at level 1, no parentAuthor (quote box will show)
                                return renderCommentItem(reply, 1, null, index === comment.replies.length - 1);
                            }).join('')}
                            <button class="hide-replies-btn" onclick="toggleReplies(${comment.id})" style="margin-top: 4px; margin-left: 4px; background: transparent; border: none; color: #1877f2; cursor: pointer; font-size: 12px; font-weight: 600; padding: 4px 8px; border-radius: 4px;">
                                <i class="fas fa-chevron-up" style="font-size: 10px; margin-right: 4px;"></i>
                                Hide replies
                            </button>
            </div>
                    ` : ''}
                    
                    <!-- Nested Replies (all at level 1 with quotes) -->
                    ${hasNestedReplies && isNestedExpanded && depth === 1 ? `
                        <div class="comment-replies" style="margin-top: 8px;">
                            ${comment.nested_replies.map((nestedReply, index) => {
                                // All nested replies are at level 1, no parentAuthor (quote box will show)
                                return renderCommentItem(nestedReply, 1, null, index === comment.nested_replies.length - 1);
                            }).join('')}
                            <button class="hide-replies-btn" onclick="toggleReplies('nested_${comment.id}')" style="margin-top: 4px; margin-left: 4px; background: transparent; border: none; color: #1877f2; cursor: pointer; font-size: 12px; font-weight: 600; padding: 4px 8px; border-radius: 4px;">
                                <i class="fas fa-chevron-up" style="font-size: 10px; margin-right: 4px;"></i>
                                Hide replies
                            </button>
            </div>
                    ` : ''}
                    
                    <!-- Edit Form (hidden by default) -->
                    <div class="edit-comment-form" id="editForm_${comment.id}" style="display: none; margin-top: 8px;">
                        <div class="comment-input-container" style="display: flex; gap: 8px;">
                            <div class="comment-input-avatar" style="width: 32px; height: 32px; flex-shrink: 0; border-radius: 50%; background: linear-gradient(135deg, #1877f2 0%, #42a5f5 100%); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px;">
                        ${getCurrentUserInitials()}
                    </div>
                    <div class="comment-input-wrapper" style="flex: 1;">
                                <textarea id="editInput_${comment.id}" class="comment-input" placeholder="Edit your comment..." style="width: 100%; padding: 8px 12px; border: 1px solid #ccd0d5; border-radius: 18px; font-size: 14px; font-family: inherit; resize: none; min-height: 36px; outline: none; background-color: #f0f2f5;" required>${escapeHtml(comment.content)}</textarea>
                        <div style="display: flex; gap: 8px; margin-top: 8px;">
                                    <button type="button" class="comment-submit-btn" onclick="event.stopPropagation(); saveCommentEdit(${comment.id})" style="padding: 6px 16px; font-size: 13px; background: #1877f2; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Save</button>
                                    <button type="button" class="comment-cancel-btn" onclick="event.stopPropagation(); cancelCommentEdit(${comment.id})" style="padding: 6px 16px; background: transparent; color: #65676b; border: none; cursor: pointer; font-size: 13px; font-weight: 600;">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Reply Form (hidden by default) -->
                    <div class="reply-form-container" id="replyForm_${comment.id}" style="display: none; margin-top: 8px;">
                        <div class="comment-input-container" style="display: flex; gap: 8px;">
                            <div class="comment-input-avatar" style="width: 32px; height: 32px; flex-shrink: 0; border-radius: 50%; background: linear-gradient(135deg, #1877f2 0%, #42a5f5 100%); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px;">
                        ${getCurrentUserInitials()}
                    </div>
                    <div class="comment-input-wrapper" style="flex: 1;">
                                <textarea id="replyInput_${comment.id}" class="comment-input" placeholder="Write a reply..." style="width: 100%; padding: 8px 12px; border: 1px solid #ccd0d5; border-radius: 18px; font-size: 14px; font-family: inherit; resize: none; min-height: 36px; outline: none; background-color: #f0f2f5;" required></textarea>
                        <div style="display: flex; gap: 8px; margin-top: 8px;">
                                    <button type="button" class="comment-submit-btn" onclick="event.stopPropagation(); submitReply(${comment.id}, ${postState.postId})" style="padding: 6px 16px; font-size: 13px; background: #1877f2; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Reply</button>
                                    <button type="button" class="comment-cancel-btn" onclick="event.stopPropagation(); cancelReply(${comment.id})" style="padding: 6px 16px; background: transparent; color: #65676b; border: none; cursor: pointer; font-size: 13px; font-weight: 600;">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
                </div>
            </div>
        </div>
    `;
}

function toggleReplies(commentId) {
    // Handle both regular comment IDs and nested reply IDs (strings like 'nested_123')
    const id = typeof commentId === 'string' ? commentId : commentId;
    
    if (expandedReplies.has(id)) {
        expandedReplies.delete(id);
    } else {
        expandedReplies.add(id);
    }
    
    // Re-render comments to show/hide replies (only if it's a top-level comment)
    // For nested replies, we just need to re-render the current view
    if (typeof id === 'number' || (typeof id === 'string' && !id.startsWith('nested_'))) {
        loadComments(postState.postId, false);
    } else {
        // For nested replies, just re-render the comments without reloading
        renderComments(commentState.comments);
    }
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
            await loadPostDetail();
        }
    } catch (error) {
        console.error('Error toggling reaction:', error);
    }
}

async function toggleBookmark(postId) {
    try {
        const response = await fetch('/api/forum/bookmark', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'include',
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
        const response = await fetch('/api/forum/comment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify({
                post_id: postState.postId,
                content: content.trim()
            })
        });

        const data = await response.json();

        if (data.status === 200) {
            document.getElementById('commentInput').value = '';
            await loadComments(postState.postId, true);
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
            await loadComments(postState.postId, true);
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
    if (!dateString) return 'Unknown date';
    
    const date = new Date(dateString);
    
    // Check if date is valid
    if (isNaN(date.getTime())) {
        return 'Unknown date';
    }
    
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

async function loadSidebarData() {
    await loadForumsToSidebar();
    await loadTagsForForum();
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
             onclick="window.location.href='/forum/${forum.id}'">
            <i class="fas fa-comments" style="color: #878a8c;"></i>
            <span>${escapeHtml(forum.title)}</span>
        </div>
    `).join('');
}

async function loadTagsForForum() {
    if (!postState.post) return;

    try {
        // Load tags dynamically from API
        const response = await fetch('/api/forum/tags?limit=20', {
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

        if (data.status === 200 && data.data && data.data.tags) {
            const tags = data.data.tags.map(t => t.name).slice(0, 10);
            renderTagsToSidebar(tags);
        } else {
            // Fallback: try to parse tags from the post
            renderTagsFromPost();
        }
    } catch (error) {
        console.error('Error loading tags from API:', error);
        // Fallback: try to parse tags from the post
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
        const response = await fetch(`/api/forum/${postState.post.forum_id}`, {
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
    window.location.href = '/messaging';
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
            // Redirect back to forum or forum detail page
            if (postState.post && postState.post.forum_id) {
                window.location.href = `/forum/${postState.post.forum_id}`;
            } else {
                window.location.href = '/forum';
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
                        <div class="share-post-forum">${escapeHtml(forumName)}</div>
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
    modal.querySelector('.share-post-forum').textContent = forumName;
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

// Format comment content with basic text formatting
function formatCommentContent(content) {
    if (!content) return '';
    
    // Escape HTML first to prevent XSS
    let formatted = escapeHtml(content);
    
    // Convert markdown-style formatting to HTML
    // Bold: **text** or __text__
    formatted = formatted.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    formatted = formatted.replace(/__(.+?)__/g, '<strong>$1</strong>');
    
    // Italic: *text* or _text_ (but not if it's part of **bold**)
    formatted = formatted.replace(/(?<!\*)\*(?!\*)([^*]+?)(?<!\*)\*(?!\*)/g, '<em>$1</em>');
    formatted = formatted.replace(/(?<!_)_([^_]+?)_(?!_)/g, '<em>$1</em>');
    
    // Links: [text](url) or just URLs
    formatted = formatted.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer" style="color: #0079d3; text-decoration: underline;">$1</a>');
    
    // Auto-detect URLs (http://, https://, www.)
    const urlRegex = /(https?:\/\/[^\s<>]+|www\.[^\s<>]+)/g;
    formatted = formatted.replace(urlRegex, (url) => {
        // Skip if already inside an <a> tag
        if (url.includes('<a') || url.includes('</a>')) return url;
        let href = url;
        if (!url.startsWith('http://') && !url.startsWith('https://')) {
            href = 'https://' + url;
        }
        return `<a href="${escapeHtml(href)}" target="_blank" rel="noopener noreferrer" style="color: #0079d3; text-decoration: underline;">${escapeHtml(url)}</a>`;
    });
    
    // Line breaks
    formatted = formatted.replace(/\n/g, '<br>');
    
    return formatted;
}

// Comment edit/delete functions
function toggleCommentEdit(commentId) {
    const editForm = document.getElementById(`editForm_${commentId}`);
    const commentBody = document.getElementById(`commentBody_${commentId}`);
    
    if (editForm && commentBody) {
        if (editForm.style.display === 'none' || !editForm.style.display) {
            editForm.style.display = 'block';
            commentBody.style.display = 'none';
            const editInput = document.getElementById(`editInput_${commentId}`);
            if (editInput) {
                editInput.focus();
                // Move cursor to end
                editInput.setSelectionRange(editInput.value.length, editInput.value.length);
            }
        } else {
            editForm.style.display = 'none';
            commentBody.style.display = 'block';
        }
    }
}

function cancelCommentEdit(commentId) {
    const editForm = document.getElementById(`editForm_${commentId}`);
    const commentBody = document.getElementById(`commentBody_${commentId}`);
    
    if (editForm && commentBody) {
        editForm.style.display = 'none';
        commentBody.style.display = 'block';
    }
}

async function saveCommentEdit(commentId) {
    const editInput = document.getElementById(`editInput_${commentId}`);
    if (!editInput) return;
    
    const content = editInput.value.trim();
    if (!content) {
        alert('Comment cannot be empty');
        return;
    }
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const response = await fetch(`/api/forum/comment/${commentId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'include',
            body: JSON.stringify({ content })
        });
        
        const data = await response.json();
        
        if (data.status === 200) {
            // Reload comments to show updated content
            await loadComments(postState.postId, true);
        } else {
            alert(data.message || 'Failed to update comment');
        }
    } catch (error) {
        console.error('Error updating comment:', error);
        alert('Failed to update comment');
    }
}

function confirmDeleteComment(commentId) {
    if (!confirm('Are you sure you want to delete this comment? This action cannot be undone.')) {
        return;
    }
    
    deleteComment(commentId);
}

async function deleteComment(commentId) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const response = await fetch(`/api/forum/comment/${commentId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.status === 200) {
            // Reload comments to reflect deletion
            await loadComments(postState.postId, true);
        } else {
            alert(data.message || 'Failed to delete comment');
        }
    } catch (error) {
        console.error('Error deleting comment:', error);
        alert('Failed to delete comment');
    }
}

function changeCommentSort(sort) {
    commentState.sort = sort;
    commentState.offset = 0;
    commentState.comments = [];
    commentState.hasMore = true;
    loadComments(postState.postId, true);
}

async function loadMoreComments() {
    if (!commentState.hasMore || commentState.isLoading) return;
    await loadComments(postState.postId, false);
}

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
window.changeCommentSort = changeCommentSort;
window.loadMoreComments = loadMoreComments;
window.toggleCommentEdit = toggleCommentEdit;
window.cancelCommentEdit = cancelCommentEdit;
window.saveCommentEdit = saveCommentEdit;
window.confirmDeleteComment = confirmDeleteComment;
window.deleteComment = deleteComment;
window.formatCommentContent = formatCommentContent;

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

// Function to handle smart back navigation
function goBackToReferrer() {
    const urlParams = new URLSearchParams(window.location.search);
    const referrer = urlParams.get('referrer');
    
    if (referrer) {
        // Decode and navigate to the referrer page
        const referrerPath = decodeURIComponent(referrer);
        window.location.href = referrerPath;
    } else {
        // Fallback to browser history
        window.history.back();
    }
}

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
    modal.querySelector('.report-post-title').textContent = postTitle;
    modal.dataset.postId = postId;
    
    // Reset form
    const form = modal.querySelector('form');
    if (form) form.reset();
    const detailsInput = document.getElementById('reportDetails');
    if (detailsInput) {
        detailsInput.value = '';
        document.getElementById('reportCharCount').textContent = '0';
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
            
            // Show warning if multiple reports
            if (data.data && data.data.report_count >= 3) {
                // Optionally show a warning banner
                showReportWarning(postId, data.data.report_count);
            }
            
            // Reload post to update report count display
            if (postState.postId) {
                await loadPostDetail();
            }
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

function showReportWarning(postId, reportCount) {
    // Create or update warning banner
    let warningBanner = document.getElementById('reportWarningBanner');
    if (!warningBanner) {
        warningBanner = document.createElement('div');
        warningBanner.id = 'reportWarningBanner';
        warningBanner.className = 'report-warning-banner';
        warningBanner.innerHTML = `
            <div class="report-warning-content">
                <i class="fas fa-exclamation-triangle"></i>
                <span>This post has been reported ${reportCount} times and is under review.</span>
            </div>
        `;
        const container = document.getElementById('postDetailContent');
        if (container) {
            container.insertBefore(warningBanner, container.firstChild);
        }
    } else {
        warningBanner.querySelector('span').textContent = `This post has been reported ${reportCount} times and is under review.`;
    }
}

async function toggleHidePost(postId, hide) {
    if (!confirm(`Are you sure you want to ${hide ? 'hide' : 'unhide'} this post?`)) {
        return;
    }
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const response = await fetch(`/api/forum/post/${postId}/hide`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'include',
            body: JSON.stringify({
                hide: hide
            })
        });
        
        const data = await response.json();
        
        if (data.status === 200) {
            alert(data.message || `Post ${hide ? 'hidden' : 'unhidden'} successfully`);
            // Reload post detail
            await loadPostDetail();
        } else {
            alert(data.message || `Failed to ${hide ? 'hide' : 'unhide'} post`);
        }
    } catch (error) {
        console.error('Error toggling hide post:', error);
        alert(`Failed to ${hide ? 'hide' : 'unhide'} post`);
    }
}

// Make functions globally accessible
window.openReportModal = openReportModal;
window.closeReportModal = closeReportModal;
window.submitReport = submitReport;
window.toggleHidePost = toggleHidePost;
window.goBackToReferrer = goBackToReferrer;




