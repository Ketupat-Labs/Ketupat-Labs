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

// Mention state
let mentionState = {
    isActive: false,
    searchTerm: '',
    users: [],
    selectedIndex: -1,
    mentionStart: -1,
    currentTextarea: null
};

// Helper function to extract YouTube video ID from URL
function extractYouTubeVideoId(url) {
    if (!url) return null;

    const patterns = [
        /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/,
        /youtube\.com\/embed\/([^"&?\/\s]{11})/,
        /youtube\.com\/v\/([^"&?\/\s]{11})/
    ];

    for (const pattern of patterns) {
        const match = url.match(pattern);
        if (match && match[1]) {
            return match[1];
        }
    }

    return null;
}

// Helper function to extract video ID from TikTok URL
function extractTikTokVideoId(url) {
    const patterns = [
        /(?:tiktok\.com\/@[\w.-]+\/video\/|vm\.tiktok\.com\/|tiktok\.com\/t\/)([a-zA-Z0-9]+)/i,
        /tiktok\.com\/.*\/video\/(\d+)/i
    ];

    for (const pattern of patterns) {
        const match = url.match(pattern);
        if (match && match[1]) {
            return match[1];
        }
    }

    return null;
}

// Helper function to extract video ID from RedNote (Xiaohongshu) URL
function extractRedNoteVideoId(url) {
    const patterns = [
        /xiaohongshu\.com\/explore\/([a-zA-Z0-9]+)/i,
        /xhslink\.com\/([a-zA-Z0-9]+)/i
    ];

    for (const pattern of patterns) {
        const match = url.match(pattern);
        if (match && match[1]) {
            return match[1];
        }
    }

    return null;
}

// Helper function to convert video URLs (YouTube, TikTok, RedNote, etc.) to embedded players
function processVideoLinks(content) {
    if (!content) return content;

    // Split content by newlines to separate URL from description
    const lines = content.split('\n');
    let urlLine = '';
    let descriptionLines = [];
    let urlLineIndex = -1;
    let foundVideoUrl = false;

    // Find the first line that contains a video URL (YouTube, TikTok, RedNote, etc.)
    for (let i = 0; i < lines.length; i++) {
        const line = lines[i].trim();
        if (!foundVideoUrl && /(?:youtube\.com|youtu\.be|tiktok\.com|vm\.tiktok\.com|xiaohongshu\.com|xhslink\.com|vt\.tiktok\.com)/.test(line)) {
            urlLine = line;
            urlLineIndex = i;
            foundVideoUrl = true;
            // Get description from lines AFTER the URL (since link posts store URL first, then description)
            // Skip empty lines immediately after URL (the '\n\n' separator)
            let afterUrlLines = lines.slice(i + 1);
            // Remove leading empty lines
            while (afterUrlLines.length > 0 && afterUrlLines[0].trim() === '') {
                afterUrlLines.shift();
            }
            descriptionLines = afterUrlLines;
            break;
        }
    }

    // If no video URL found, check if there's a regular URL and create link preview
    if (!foundVideoUrl) {
        // Try to find any URL in the content
        const urlRegex = /(https?:\/\/[^\s<>"']+)/gi;
        const urlMatch = content.match(urlRegex);

        if (urlMatch && urlMatch.length > 0) {
            const firstUrl = urlMatch[0];
            try {
                const urlObj = new URL(firstUrl);
                const domain = urlObj.hostname.replace('www.', '');

                // Extract description (everything after the URL)
                const urlIndex = content.indexOf(firstUrl);
                const description = content.substring(urlIndex + firstUrl.length).trim();

                // Display description as separate paragraph, then link preview
                const descriptionHtml = description
                    ? '<div class="post-description" style="margin-bottom: 16px; color: #333; line-height: 1.6; white-space: pre-wrap;">' +
                      escapeHtml(description) +
                      '</div>'
                    : '';

                // Create compact link preview (without description inside)
                return descriptionHtml + createLinkPreview(firstUrl, domain, '');
            } catch (e) {
                // Invalid URL, just escape and return
                return escapeHtml(content);
            }
        }

        // No URL found, just escape and return the content
        return escapeHtml(content);
    }

    // Process description - join lines and preserve paragraph breaks
    const descriptionText = descriptionLines.join('\n').trim();
    const escapedDescription = descriptionText
        ? '<div class="post-description" style="margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid #e0e0e0; color: #333; line-height: 1.6; white-space: pre-wrap;">' +
        escapeHtml(descriptionText) +
        '</div>'
        : '';

    // Escape HTML to prevent XSS for the URL line
    const escapedUrlLine = escapeHtml(urlLine);
    let processedUrl = escapedUrlLine;

    // Process YouTube URLs
    const youtubeRegex = /(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:watch\?v=|embed\/|v\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})(?:[?&]t=(\d+[smh]?|[0-9]+m[0-9]+s)?)?[^\s<>"']*/gi;
    processedUrl = processedUrl.replace(youtubeRegex, (match, videoId, startTime) => {
        let timeInSeconds = null;
        if (startTime) {
            const cleanTime = startTime.toString().replace(/s$/, '');
            if (cleanTime.includes('m')) {
                const parts = cleanTime.match(/(\d+)m(?:(\d+))?/);
                if (parts) {
                    const minutes = parseInt(parts[1]) || 0;
                    const seconds = parseInt(parts[2]) || 0;
                    timeInSeconds = minutes * 60 + seconds;
                }
            } else {
                timeInSeconds = parseInt(cleanTime) || null;
            }
        }

        const timeParam = timeInSeconds ? `?start=${timeInSeconds}` : '';
        return `<div class="video-embed-container" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; margin: 16px 0; border-radius: 8px; overflow: hidden;">
            <iframe style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" 
                    src="https://www.youtube.com/embed/${videoId}${timeParam}" 
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen>
            </iframe>
        </div>`;
    });

    // Process TikTok URLs
    const tiktokRegex = /(?:https?:\/\/)?(?:www\.)?(?:tiktok\.com\/@[\w.-]+\/video\/|vm\.tiktok\.com\/|tiktok\.com\/t\/)([a-zA-Z0-9]+)[^\s<>"']*/gi;
    processedUrl = processedUrl.replace(tiktokRegex, (match) => {
        const videoId = extractTikTokVideoId(match);
        if (videoId) {
            return `<div class="video-embed-container" style="position: relative; padding-bottom: 125%; height: 0; overflow: hidden; max-width: 100%; margin: 16px 0; border-radius: 8px; overflow: hidden;">
                <blockquote class="tiktok-embed" cite="${match}" data-video-id="${videoId}" style="max-width: 100%; min-width: 325px;">
                    <section>
                        <a href="${match}" target="_blank" title="@username">View on TikTok</a>
                    </section>
                </blockquote>
                <script async src="https://www.tiktok.com/embed.js"></script>
            </div>`;
        }
        return match;
    });

    // Process RedNote (Xiaohongshu) URLs
    const rednoteRegex = /(?:https?:\/\/)?(?:www\.)?(?:xiaohongshu\.com\/explore\/|xhslink\.com\/)([a-zA-Z0-9]+)[^\s<>"']*/gi;
    processedUrl = processedUrl.replace(rednoteRegex, (match) => {
        const videoId = extractRedNoteVideoId(match);
        if (videoId) {
            return `<div class="video-embed-container" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; margin: 16px 0; border-radius: 8px; overflow: hidden; border: 1px solid #e0e0e0;">
                <iframe style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" 
                        src="${match}" 
                        frameborder="0" 
                        allowfullscreen
                        scrolling="no">
                </iframe>
                <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 8px; background: rgba(255,255,255,0.9); text-align: center;">
                    <a href="${match}" target="_blank" style="color: #ff2442; text-decoration: none; font-size: 12px;">View on RedNote</a>
                </div>
            </div>`;
        }
        return match;
    });

    // Check if the URL was actually processed (video embed created)
    // If not, it means it's a non-video link, so create a link preview instead
    const isVideoEmbed = processedUrl.includes('video-embed-container') ||
        processedUrl.includes('tiktok-embed') ||
        processedUrl.includes('iframe');

    if (!isVideoEmbed && urlLine) {
        // It's a link but not a recognized video, create compact link preview
        try {
            const urlObj = new URL(urlLine);
            const domain = urlObj.hostname.replace('www.', '');
            return escapedDescription + createLinkPreview(urlLine, domain, '');
        } catch (e) {
            // Invalid URL, just return escaped content
            return escapedDescription + escapeHtml(urlLine);
        }
    }

    // Return description + embed (description above the video)
    return escapedDescription + processedUrl;
}

// Cache for link previews to avoid multiple requests
const linkPreviewCache = {};

// Fetch link preview data
async function fetchLinkPreview(url) {
    // Check cache first
    if (linkPreviewCache[url]) {
        return linkPreviewCache[url];
    }

    const apiBaseUrl = window.location.origin;
    try {
        const response = await fetch(`${apiBaseUrl}/api/link-preview?url=${encodeURIComponent(url)}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'include',
        });

        if (response.ok) {
            const data = await response.json();
            if (data.status === 200 && data.data) {
                // Cache the result
                linkPreviewCache[url] = data.data;
                return data.data;
            }
        }
    } catch (error) {
        console.error('Error fetching link preview:', error);
    }

    // Return fallback
    return {
        title: new URL(url).hostname,
        site_name: new URL(url).hostname,
    };
}

// Helper function to create a compact link preview (for non-video links)
// Note: description parameter is kept for backward compatibility but is no longer used
// Description should be displayed separately as a paragraph before calling this function
function createLinkPreview(url, domain, description) {
    // Use domain as initial title (will be updated async)
    const containerId = 'link-preview-' + Math.random().toString(36).substr(2, 9);
    
    // Fetch preview asynchronously and update
    fetchLinkPreview(url).then(preview => {
        const container = document.getElementById(containerId);
        if (container) {
            const titleLink = container.querySelector('.link-preview-title a');
            if (titleLink && preview.title) {
                titleLink.textContent = preview.title;
            }
            const siteElement = container.querySelector('.link-preview-site');
            if (siteElement && preview.site_name) {
                siteElement.textContent = preview.site_name;
            }
        }
    });

    return `
        <div id="${containerId}" class="link-preview-container" style="margin: 16px 0; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; background: #fff; display: flex; cursor: pointer; transition: box-shadow 0.2s;" 
             onclick="event.stopPropagation(); window.open('${escapeHtml(url)}', '_blank')" 
             onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'" 
             onmouseout="this.style.boxShadow='none'">
            <div style="padding: 12px; display: flex; align-items: center; color: #666; flex-shrink: 0;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
                    <polyline points="16 6 12 2 8 6"></polyline>
                    <line x1="12" y1="2" x2="12" y2="15"></line>
                </svg>
            </div>
            <div style="flex: 1; padding: 12px; padding-left: 0; min-width: 0;">
                <div class="link-preview-title" style="font-weight: 500; color: #333; margin-bottom: 4px; word-wrap: break-word; line-height: 1.4;">
                    <a href="${escapeHtml(url)}" target="_blank" rel="noopener noreferrer" style="color: #1877f2; text-decoration: none;" onclick="event.stopPropagation();">
                        ${escapeHtml(domain)}
                    </a>
                </div>
                <div class="link-preview-site" style="font-size: 0.85em; color: #999; margin-top: 4px;">
                    ${escapeHtml(domain)}
                </div>
            </div>
        </div>
    `;
}

// Keep backward compatibility
function processYouTubeLinks(content) {
    return processVideoLinks(content);
}

document.addEventListener('DOMContentLoaded', () => {
    // Check if user is logged in
    if (sessionStorage.getItem('userLoggedIn') !== 'true') {
        window.location.href = '/login';
        return;
    }

    // Load current user avatar if not in sessionStorage
    loadCurrentUserAvatar();

    initEventListeners();
    initMentionHandlers();
    loadPostDetail();
});

// Load current user avatar from API
async function loadCurrentUserAvatar() {
    if (sessionStorage.getItem('userAvatar')) {
        return; // Already loaded
    }

    try {
        const response = await fetch('/api/auth/me', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            credentials: 'include',
        });

        if (response.ok) {
            const data = await response.json();
            if (data.status === 200 && data.data && data.data.avatar_url) {
                sessionStorage.setItem('userAvatar', data.data.avatar_url);
                // Update comment input avatar if page is loaded
                updateCommentInputAvatar();
            }
        }
    } catch (error) {
        console.error('Error loading user avatar:', error);
    }
}

// Update comment input avatar display
function updateCommentInputAvatar() {
    const avatarElements = document.querySelectorAll('.comment-input-avatar');
    avatarElements.forEach(el => {
        const avatarUrl = getCurrentUserAvatar();
        const userName = sessionStorage.getItem('userName') || sessionStorage.getItem('userEmail') || 'User';

        if (avatarUrl) {
            el.innerHTML = `<img src="${escapeHtml(avatarUrl)}" alt="${escapeHtml(userName)}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">`;
        } else {
            el.innerHTML = getUserInitials(userName);
        }
    });
}

function initMentionHandlers() {
    // Use event delegation for dynamically created textareas
    // Remove existing listeners first to avoid duplicates
    document.removeEventListener('input', handleMentionInput);
    document.removeEventListener('keydown', handleMentionKeydown);
    document.removeEventListener('click', handleMentionClickOutside);
    
    // Add event listeners
    document.addEventListener('input', handleMentionInput);
    document.addEventListener('keydown', handleMentionKeydown);
    document.addEventListener('click', handleMentionClickOutside);
    
    console.log('Mention handlers initialized');
}

function handleMentionInput(event) {
    const textarea = event.target;
    
    // Check if it's a textarea with comment-input class
    if (!textarea || textarea.tagName !== 'TEXTAREA' || !textarea.classList.contains('comment-input')) {
        // If mention is active but focus moved away, hide it
        if (mentionState.isActive && mentionState.currentTextarea && mentionState.currentTextarea !== textarea) {
            hideMentionDropdown();
        }
        return;
    }

    const value = textarea.value;
    const cursorPos = textarea.selectionStart || 0;

    // Find @ symbol before cursor
    const textBeforeCursor = value.substring(0, cursorPos);
    const lastAtIndex = textBeforeCursor.lastIndexOf('@');

    if (lastAtIndex === -1) {
        hideMentionDropdown();
        return;
    }

    // Check if @ is part of a word (not a mention)
    const charBefore = lastAtIndex > 0 ? textBeforeCursor[lastAtIndex - 1] : ' ';
    if (/\w/.test(charBefore)) {
        hideMentionDropdown();
        return;
    }

    // Get text after @
    const textAfterAt = textBeforeCursor.substring(lastAtIndex + 1);

    // Check if there's a space or newline after @ (not a mention)
    if (textAfterAt.includes(' ') || textAfterAt.includes('\n')) {
        hideMentionDropdown();
        return;
    }

    // Show mention dropdown
    mentionState.isActive = true;
    mentionState.searchTerm = textAfterAt;
    mentionState.mentionStart = lastAtIndex;
    mentionState.currentTextarea = textarea;
    mentionState.selectedIndex = -1;

    console.log('Mention detected, loading users for search:', textAfterAt);
    // Always load users when @ is detected
    loadMentionableUsers(textAfterAt);
}

function handleMentionKeydown(event) {
    // Check if the event target is a comment input textarea
    const textarea = event.target;
    if (!textarea || !textarea.classList.contains('comment-input')) {
        // If mention is active but focus moved away, hide it
        if (mentionState.isActive && mentionState.currentTextarea !== textarea) {
            hideMentionDropdown();
        }
        return;
    }

    if (!mentionState.isActive || !mentionState.currentTextarea) {
        return;
    }

    const dropdown = document.getElementById('mentionDropdown');
    if (!dropdown) return;

    const items = dropdown.querySelectorAll('.mention-item');
    if (items.length === 0) return;

    switch (event.key) {
        case 'ArrowDown':
            event.preventDefault();
            mentionState.selectedIndex = Math.min(mentionState.selectedIndex + 1, items.length - 1);
            updateMentionSelection();
            break;
        case 'ArrowUp':
            event.preventDefault();
            mentionState.selectedIndex = Math.max(mentionState.selectedIndex - 1, -1);
            updateMentionSelection();
            break;
        case 'Enter':
        case 'Tab':
            event.preventDefault();
            if (mentionState.selectedIndex >= 0 && items[mentionState.selectedIndex]) {
                selectMention(items[mentionState.selectedIndex].dataset.userId, items[mentionState.selectedIndex].dataset.username);
            }
            break;
        case 'Escape':
            event.preventDefault();
            hideMentionDropdown();
            break;
    }
}

function handleMentionClickOutside(event) {
    const dropdown = document.getElementById('mentionDropdown');
    if (dropdown && !dropdown.contains(event.target) &&
        event.target !== mentionState.currentTextarea &&
        !event.target.closest('.mention-dropdown')) {
        hideMentionDropdown();
    }
}

async function loadMentionableUsers(search = '') {
    try {
        const params = new URLSearchParams({
            search: search,
            limit: '20'
        });

        const response = await fetch(`/api/mentions/users?${params.toString()}`, {
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
            mentionState.users = data.data.users || [];
            // Always show dropdown when mention is active, even if no users found
            showMentionDropdown();
        } else {
            // Still show dropdown with empty state
            mentionState.users = [];
            showMentionDropdown();
        }
    } catch (error) {
        console.error('Error loading mentionable users:', error);
        // Show dropdown with error state
        mentionState.users = [];
        showMentionDropdown();
    }
}

function showMentionDropdown() {
    if (!mentionState.currentTextarea) {
        console.log('Cannot show mention dropdown: no current textarea');
        return;
    }

    let dropdown = document.getElementById('mentionDropdown');
    if (!dropdown) {
        dropdown = document.createElement('div');
        dropdown.id = 'mentionDropdown';
        dropdown.className = 'mention-dropdown';
        document.body.appendChild(dropdown);
    }

    // Position dropdown near textarea
    const rect = mentionState.currentTextarea.getBoundingClientRect();
    dropdown.style.position = 'fixed';
    dropdown.style.top = `${rect.bottom + window.scrollY + 5}px`;
    dropdown.style.left = `${rect.left + window.scrollX}px`;
    dropdown.style.width = `${Math.min(rect.width, 300)}px`;
    dropdown.style.maxHeight = '200px';
    dropdown.style.overflowY = 'auto';
    dropdown.style.zIndex = '10000';
    dropdown.style.display = 'block';
    dropdown.style.background = 'white';
    dropdown.style.border = '1px solid #e4e6eb';
    dropdown.style.borderRadius = '8px';
    dropdown.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.15)';

    // Render users
    if (mentionState.users.length === 0) {
        dropdown.innerHTML = '<div class="mention-item" style="padding: 8px 12px; color: #65676b;">No users found. Start typing to search.</div>';
    } else {
        dropdown.innerHTML = mentionState.users.map((user, index) => `
            <div class="mention-item ${index === mentionState.selectedIndex ? 'selected' : ''}" 
                 data-user-id="${user.id}" 
                 data-username="${escapeHtml(user.username)}"
                 data-full-name="${escapeHtml(user.full_name)}"
                 onclick="selectMention(${user.id}, '${escapeHtml(user.username)}')"
                 style="padding: 8px 12px; cursor: pointer; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #f0f2f5;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #1877f2 0%, #42a5f5 100%); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; flex-shrink: 0;">
                    ${user.avatar_url ? `<img src="${escapeHtml(user.avatar_url)}" alt="${escapeHtml(user.full_name)}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">` : getUserInitials(user.full_name)}
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 600; font-size: 14px; color: #050505;">${escapeHtml(user.full_name)}</div>
                    <div style="font-size: 12px; color: #65676b;">@${escapeHtml(user.username)}${user.is_dm_contact ? ' • DM Contact' : ''}</div>
                </div>
            </div>
        `).join('');
    }

    updateMentionSelection();
    console.log('Mention dropdown shown with', mentionState.users.length, 'users');
}

function updateMentionSelection() {
    const dropdown = document.getElementById('mentionDropdown');
    if (!dropdown) return;

    const items = dropdown.querySelectorAll('.mention-item');
    items.forEach((item, index) => {
        if (index === mentionState.selectedIndex) {
            item.classList.add('selected');
            item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        } else {
            item.classList.remove('selected');
        }
    });
}

function selectMention(userId, username) {
    if (!mentionState.currentTextarea) return;

    const textarea = mentionState.currentTextarea;
    const value = textarea.value;
    const start = mentionState.mentionStart;
    const cursorPos = textarea.selectionStart;

    // Replace @searchTerm with @username
    const beforeMention = value.substring(0, start);
    const afterMention = value.substring(cursorPos);
    const newValue = beforeMention + '@' + username + ' ' + afterMention;

    textarea.value = newValue;

    // Set cursor position after the mention
    const newCursorPos = start + username.length + 2; // +2 for @ and space
    textarea.setSelectionRange(newCursorPos, newCursorPos);
    textarea.focus();

    hideMentionDropdown();

    // Trigger input event to update any listeners
    textarea.dispatchEvent(new Event('input', { bubbles: true }));
}

function hideMentionDropdown() {
    mentionState.isActive = false;
    mentionState.searchTerm = '';
    mentionState.selectedIndex = -1;
    mentionState.mentionStart = -1;
    mentionState.currentTextarea = null;

    const dropdown = document.getElementById('mentionDropdown');
    if (dropdown) {
        dropdown.style.display = 'none';
    }
}

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
            if (response.status === 401) {
                // User not authenticated, redirect to login
                console.error('Unauthorized - redirecting to login');
                sessionStorage.removeItem('userLoggedIn');
                window.location.href = '/login';
                return;
            }
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
        if (error.message.includes('401')) {
            sessionStorage.removeItem('userLoggedIn');
            window.location.href = '/login';
        } else {
            showError('Failed to load post');
        }
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
                                ${post.author_name || post.author_username ? `
                                    <span class="post-time">•</span>
                                    <span class="post-author" onclick="event.stopPropagation(); if (${post.author_id || 'null'}) { window.location.href = '/profile/' + ${post.author_id}; }" style="cursor: pointer;">
                                        ${escapeHtml(post.author_name || post.author_username)}
                                    </span>
                                ` : ''}
                                <span class="post-time">•</span>
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
                                <i class="fas fa-flag"></i> Lapor
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
                    // Tags can be either an array (from API) or a JSON string
                    let tags = post.tags;
                    if (typeof tags === 'string') {
                        tags = JSON.parse(tags);
                    }
                    return Array.isArray(tags) ? tags : [];
                } catch (e) {
                    return [];
                }
            })().map(tag => `
                            <span class="post-tag">#${escapeHtml(tag)}</span>
                        `).join('')}
                    </div>
                ` : ''}
                ${post.lesson_id ? `
                    <div class="post-lesson-attachment" style="margin-top: 24px; margin-bottom: 24px; padding: 16px; border: 1px solid #e0e0e0; border-radius: 12px; background: #f0f7ff; display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="background: #1877f2; padding: 12px; border-radius: 50%; color: white; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-chalkboard-teacher" style="font-size: 20px;"></i>
                            </div>
                            <div>
                                <div style="font-weight: 600; color: #333; font-size: 16px; margin-bottom: 2px;">Lesson Attached</div>
                                <div style="font-size: 14px; color: #666;">This post contains a reusable lesson plan.</div>
                            </div>
                        </div>
                        <button class="btn-save-lesson" onclick="saveSharedLesson(${post.lesson_id})" style="padding: 10px 20px; background: #1877f2; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: background 0.2s; box-shadow: 0 2px 4px rgba(24, 119, 242, 0.2);">
                            <i class="fas fa-download"></i> Save to Inventory
                        </button>
                    </div>
                ` : ''}
                ${post.post_type === 'poll' && post.poll_options ? `
                        <div class="post-poll-container" style="margin-top: 16px;">
                            <div style="font-weight: 600; margin-bottom: 12px; color: #1c1c1c; font-size: 16px;">Poll Options:</div>
                            ${(() => {
                try {
                    const pollOptions = typeof post.poll_options === 'string'
                        ? JSON.parse(post.poll_options)
                        : post.poll_options;
                    if (!Array.isArray(pollOptions)) return '';

                    // Get user's vote if exists
                    const userVote = post.user_poll_vote || null;

                    return pollOptions.map((option, idx) => {
                        const optionId = option.id || idx;
                        const optionText = option.option_text || option.text || option.option_text || '';
                        const voteCount = option.vote_count || 0;
                        const totalVotes = post.total_poll_votes || pollOptions.reduce((sum, opt) => sum + (opt.vote_count || 0), 0);
                        const percentage = totalVotes > 0 ? Math.round((voteCount / totalVotes) * 100) : 0;
                        const isVoted = userVote === optionId;
                        const hasVoted = userVote !== null;
                        const isClickable = !hasVoted;

                        return `
                                            <div class="poll-option-item" style="padding: 12px; margin: 8px 0; background: ${isVoted ? '#e3f2fd' : '#f5f5f5'}; border: 2px solid ${isVoted ? '#1877f2' : '#e0e0e0'}; border-radius: 8px; cursor: ${isClickable ? 'pointer' : 'default'}; transition: all 0.2s; opacity: ${isClickable ? '1' : '0.8'};" 
                                                 ${isClickable ? `onclick="votePoll(${post.id}, ${optionId})"` : `onclick="event.stopPropagation();"`}
                                                 ${isClickable ? `onmouseover="this.style.background='${isVoted ? '#bbdefb' : '#eeeeee'}'" onmouseout="this.style.background='${isVoted ? '#e3f2fd' : '#f5f5f5'}'"` : ''}>
                                                <div style="display: flex; align-items: center; gap: 12px;">
                                                    <div style="flex: 1;">
                                                        <div style="font-weight: ${isVoted ? '600' : '500'}; color: #1c1c1c; margin-bottom: 4px;">${escapeHtml(optionText)}</div>
                                                        ${totalVotes > 0 ? `
                                                            <div style="display: flex; align-items: center; gap: 8px; margin-top: 6px;">
                                                                <div style="flex: 1; height: 6px; background: #e0e0e0; border-radius: 3px; overflow: hidden;">
                                                                    <div style="height: 100%; width: ${percentage}%; background: ${isVoted ? '#1877f2' : '#9e9e9e'}; transition: width 0.3s;"></div>
                                                                </div>
                                                                <span style="font-size: 13px; color: #666; min-width: 60px; text-align: right;">${percentage}%</span>
                                                            </div>
                                                        ` : ''}
                                                    </div>
                                                    <div style="display: flex; align-items: center; gap: 8px;">
                                                        ${isVoted ? '<i class="fas fa-check-circle" style="color: #1877f2;"></i>' : ''}
                                                        <span style="font-size: 14px; color: #666; min-width: 40px; text-align: right;">${voteCount} vote${voteCount !== 1 ? 's' : ''}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        `;
                    }).join('');
                } catch (e) {
                    console.error('Error parsing poll options:', e);
                    return '';
                }
            })()}
                            ${post.total_poll_votes > 0 ? `
                                <div style="margin-top: ${post.user_poll_vote ? '8px' : '12px'}; padding-top: ${post.user_poll_vote ? '0' : '12px'}; ${post.user_poll_vote ? '' : 'border-top: 1px solid #e0e0e0;'} color: #666; font-size: 14px;">
                                    Total votes: ${post.total_poll_votes}
                                </div>
                            ` : ''}
                        </div>
                        ` : `
                <div class="post-detail-body">
                    ${post.content ? processYouTubeLinks(post.content || '') : ''}
                </div>
                `}
                ${post.attachments && post.post_type !== 'link' ? `
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
                            ${getCurrentUserAvatarHTML()}
                        </div>
                        <div class="comment-input-wrapper" style="position: relative;">
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
        } else {
            console.error('Failed to load comments:', data);
            const container = document.getElementById('commentsContainer');
            if (container) {
                container.innerHTML = '<p style="color: #878a8c; text-align: center; padding: 20px;">Error loading comments</p>';
            }
        }
    } catch (error) {
        console.error('Error loading comments:', error);
        const container = document.getElementById('commentsContainer');
        if (container) {
            container.innerHTML = '<p style="color: #878a8c; text-align: center; padding: 20px;">Error loading comments. Please refresh the page.</p>';
        }
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
    if (!container) {
        console.error('Comments container not found!');
        return;
    }

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
                            ${comment.author_id ? `
                                <span class="comment-author-name" onclick="event.stopPropagation(); window.location.href = '/profile/' + ${comment.author_id};" style="font-size: 13px; font-weight: 600; color: #050505; cursor: pointer;">
                                    ${escapeHtml(comment.author_name || comment.author_username)}
                                </span>
                            ` : `
                                <span class="comment-author-name" style="font-size: 13px; font-weight: 600; color: #050505;">
                                    ${escapeHtml(comment.author_name || comment.author_username)}
                                </span>
                            `}
                        </div>
                        <div class="comment-body" id="commentBody_${comment.id}" style="font-size: 14px; color: #050505; word-wrap: break-word; overflow-wrap: break-word; line-height: 1.38;">
                            ${comment.quoted_content && comment.quoted_author ? `
                                <div style="background: #e4e6eb; border-left: 3px solid #1877f2; padding: 6px 10px; margin-bottom: 6px; border-radius: 4px; font-size: 13px;">
                                    <span style="color: #1877f2; font-weight: 600; cursor: pointer;" onclick="event.stopPropagation(); searchUserProfile('${escapeHtml(comment.quoted_author)}'); return false;">@${escapeHtml(comment.quoted_author)}</span>
                                    <div style="color: #65676b; margin-top: 4px; font-style: italic; max-height: 60px; overflow: hidden; text-overflow: ellipsis;">${formatCommentContent(comment.quoted_content.length > 100 ? comment.quoted_content.substring(0, 100) + '...' : comment.quoted_content)}</div>
                                </div>
                            ` : parentAuthor ? `<span style="color: #1877f2; font-weight: 600; font-size: 13px; margin-right: 4px; cursor: pointer;" onclick="event.stopPropagation(); searchUserProfile('${escapeHtml(parentAuthor)}'); return false;">@${escapeHtml(parentAuthor)}</span>` : ''}
                            <span id="commentContent_${comment.id}">${formatCommentContent(comment.content, comment.mentions)}</span>
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
                            <div class="comment-input-avatar" style="width: 32px; height: 32px; flex-shrink: 0; border-radius: 50%; background: linear-gradient(135deg, #1877f2 0%, #42a5f5 100%); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; overflow: hidden;">
                        ${getCurrentUserAvatarHTML()}
                    </div>
                    <div class="comment-input-wrapper" style="flex: 1; position: relative;">
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
                            <div class="comment-input-avatar" style="width: 32px; height: 32px; flex-shrink: 0; border-radius: 50%; background: linear-gradient(135deg, #1877f2 0%, #42a5f5 100%); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; overflow: hidden;">
                        ${getCurrentUserAvatarHTML()}
                    </div>
                    <div class="comment-input-wrapper" style="flex: 1; position: relative;">
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
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const response = await fetch('/api/forum/comment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
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
            
            // Update post reply count
            if (postState.post) {
                postState.post.reply_count = (postState.post.reply_count || 0) + 1;
                // Update the comment count display
                const commentCountElement = document.querySelector('.comments-header span');
                if (commentCountElement) {
                    commentCountElement.textContent = `${postState.post.reply_count} Comments`;
                }
            }
            
            // Preserve expanded replies state before reloading
            const currentExpanded = Array.from(expandedReplies);
            
            // Reload comments to show the new comment
            commentState.offset = 0;
            await loadComments(postState.postId, true);
            
            // Restore expanded replies state after reloading
            currentExpanded.forEach(id => {
                expandedReplies.add(id);
            });
            
            // Re-render comments to show expanded state
            if (currentExpanded.length > 0) {
                renderComments(commentState.comments);
            }
        } else {
            alert(data.message || 'Failed to post comment');
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
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const response = await fetch('/api/forum/comment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
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
            
            // Update post reply count
            if (postState.post) {
                postState.post.reply_count = (postState.post.reply_count || 0) + 1;
                // Update the comment count display
                const commentCountElement = document.querySelector('.comments-header span');
                if (commentCountElement) {
                    commentCountElement.textContent = `${postState.post.reply_count} Comments`;
                }
            }
            
            // Preserve expanded replies state before reloading
            const currentExpanded = Array.from(expandedReplies);
            // Make sure the parent comment is expanded so we can see the new reply
            if (!currentExpanded.includes(commentId)) {
                currentExpanded.push(commentId);
            }
            
            // Reload comments to show the new reply
            commentState.offset = 0;
            await loadComments(postId, true);
            
            // Restore expanded replies state after reloading
            currentExpanded.forEach(id => {
                expandedReplies.add(id);
            });
            
            // Re-render comments to show expanded state
            renderComments(commentState.comments);
        } else {
            alert(data.message || 'Failed to post reply');
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

function getCurrentUserAvatar() {
    return sessionStorage.getItem('userAvatar') || null;
}

function getCurrentUserAvatarHTML() {
    const avatarUrl = getCurrentUserAvatar();
    const userName = sessionStorage.getItem('userName') || sessionStorage.getItem('userEmail') || 'User';

    if (avatarUrl) {
        return `<img src="${escapeHtml(avatarUrl)}" alt="${escapeHtml(userName)}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">`;
    }
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
                Tiada forum lagi. Cipta satu untuk bermula!
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

    // First, try to show tags from the current post
    if (postState.post.tags) {
        try {
            // Tags can be either an array (from API) or a JSON string
            let tags = postState.post.tags;
            if (typeof tags === 'string') {
                tags = JSON.parse(tags);
            }

            if (Array.isArray(tags) && tags.length > 0) {
                // Show post tags in the sidebar
                renderTagsToSidebar(tags);
                return;
            }
        } catch (e) {
            // If parsing fails, fall through to load popular tags
        }
    }

    // If no post tags, load popular tags from the forum
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

        if (data.status === 200 && data.data && data.data.tags && data.data.tags.length > 0) {
            const tags = data.data.tags.map(t => t.name).slice(0, 10);
            renderTagsToSidebar(tags);
        } else {
            // No tags available
            renderTagsToSidebar([]);
        }
    } catch (error) {
        console.error('Error loading tags from API:', error);
        // Show empty state
        renderTagsToSidebar([]);
    }
}

function renderTagsToSidebar(tags) {
    const container = document.getElementById('tagCloud');

    if (!tags || tags.length === 0) {
        container.innerHTML = `
            <p style="padding: 8px 16px; color: #878a8c; font-size: 12px;">
                Tiada tag lagi
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
                Tiada tag lagi
            </p>
        `;
        return;
    }

    try {
        const tags = typeof postState.post.tags === 'string' ? JSON.parse(postState.post.tags) : postState.post.tags;

        if (!Array.isArray(tags) || tags.length === 0) {
            container.innerHTML = `
                <p style="padding: 8px 16px; color: #878a8c; font-size: 12px;">
                    Tiada tag lagi
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
                Tiada tag lagi
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

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        // Send the message with shared_post type - post_id stored in attachment_url
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
                content: `📌 Shared Post: ${postTitle}\n\nForum: ${forumName}`,
                message_type: 'shared_post',
                attachment_url: postId.toString(), // Store post_id in attachment_url
                attachment_name: postUrl // Store post URL in attachment_name for fallback
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

// Format comment content with basic text formatting and mentions
function formatCommentContent(content, mentions = null) {
    if (!content) return '';

    // Escape HTML first to prevent XSS
    let formatted = escapeHtml(content);

    // Process mentions first (before other formatting to avoid conflicts)
    // Mentions format: @username
    if (mentions && typeof mentions === 'object') {
        // mentions is an object like { username: userId, ... }
        Object.keys(mentions).forEach(username => {
            const userId = mentions[username];
            const mentionPattern = new RegExp(`@${escapeRegex(username)}(?![\\w@])`, 'g');
            formatted = formatted.replace(mentionPattern, (match) => {
                return `<a href="/profile/${userId}" class="mention-link" style="color: #1877f2; font-weight: 600; text-decoration: none; cursor: pointer;" onclick="event.stopPropagation(); window.location.href='/profile/${userId}'; return false;">${match}</a>`;
            });
        });
    } else {
        // Fallback: detect mentions without user IDs (will link to search)
        formatted = formatted.replace(/@(\w+)/g, (match, username) => {
            return `<span class="mention-link" style="color: #1877f2; font-weight: 600; cursor: pointer;" onclick="event.stopPropagation(); searchUserProfile('${username}'); return false;">${match}</span>`;
        });
    }

    // Convert markdown-style formatting to HTML
    // Bold: **text** or __text__
    formatted = formatted.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    formatted = formatted.replace(/__(.+?)__/g, '<strong>$1</strong>');

    // Italic: *text* or _text_ (but not if it's part of **bold**)
    formatted = formatted.replace(/(?<!\*)\*(?!\*)([^*]+?)(?<!\*)\*(?!\*)/g, '<em>$1</em>');
    formatted = formatted.replace(/(?<!_)_([^_]+?)_(?!_)/g, '<em>$1</em>');

    // Links: [text](url) or just URLs (but skip if already a mention link)
    formatted = formatted.replace(/\[([^\]]+)\]\(([^)]+)\)/g, (match, text, url) => {
        // Skip if this is inside a mention link
        if (match.includes('mention-link')) return match;
        return `<a href="${escapeHtml(url)}" target="_blank" rel="noopener noreferrer" style="color: #0079d3; text-decoration: underline;">${escapeHtml(text)}</a>`;
    });

    // Auto-detect URLs (http://, https://, www.) - but skip if already a mention link
    const urlRegex = /(https?:\/\/[^\s<>]+|www\.[^\s<>]+)/g;
    formatted = formatted.replace(urlRegex, (url) => {
        // Skip if already inside an <a> tag or mention link
        if (url.includes('<a') || url.includes('</a>') || url.includes('mention-link')) return url;
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

// Helper function to escape regex special characters
function escapeRegex(str) {
    return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

// Helper function to search for user profile by username
async function searchUserProfile(username) {
    try {
        // Try to get user ID from username
        const response = await fetch(`/api/user/search?username=${encodeURIComponent(username)}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            credentials: 'include',
        });

        if (response.ok) {
            const data = await response.json();
            if (data.status === 200 && data.data && data.data.user_id) {
                window.location.href = `/profile/${data.data.user_id}`;
                return;
            }
        }
    } catch (error) {
        console.error('Error searching user:', error);
    }

    // Fallback: show error or redirect to profile search
    alert(`User @${username} not found`);
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
                    <h3>Lapor Post</h3>
                    <button class="report-modal-close" onclick="closeReportModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="report-modal-body">
                    <div class="report-post-preview">
                        <div class="report-post-title">${escapeHtml(postTitle)}</div>
                    </div>
                    <div class="report-reason-section">
                        <label class="report-label">Mengapa anda melaporkan post ini?</label>
                        <div class="report-reasons">
                            <label class="report-reason-option">
                                <input type="radio" name="reportReason" value="spam" required>
                                <span>Spam</span>
                            </label>
                            <label class="report-reason-option">
                                <input type="radio" name="reportReason" value="harassment" required>
                                <span>Gangguan atau Buli</span>
                            </label>
                            <label class="report-reason-option">
                                <input type="radio" name="reportReason" value="inappropriate" required>
                                <span>Kandungan Tidak Sesuai</span>
                            </label>
                            <label class="report-reason-option">
                                <input type="radio" name="reportReason" value="misinformation" required>
                                <span>Maklumat Palsu</span>
                            </label>
                            <label class="report-reason-option">
                                <input type="radio" name="reportReason" value="other" required>
                                <span>Lain-lain</span>
                            </label>
                        </div>
                    </div>
                    <div class="report-details-section">
                        <label class="report-label" for="reportDetails">Butiran tambahan (pilihan)</label>
                        <textarea id="reportDetails" class="report-details-input" placeholder="Berikan maklumat lanjut tentang mengapa anda melaporkan post ini..." maxlength="500"></textarea>
                        <div class="report-char-count"><span id="reportCharCount">0</span>/500</div>
                    </div>
                    <div id="reportError" class="report-error" style="display: none;"></div>
                </div>
                <div class="report-modal-footer">
                    <button class="report-cancel-btn" onclick="closeReportModal()">Batal</button>
                    <button class="report-submit-btn" onclick="submitReport()">Hantar Laporan</button>
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
            errorDiv.textContent = 'Sila pilih sebab untuk melaporkan';
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
            alert('Post berjaya dilaporkan. Terima kasih kerana membantu menjaga keselamatan komuniti kami.');

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
                errorDiv.textContent = data.message || 'Gagal menghantar laporan';
                errorDiv.style.display = 'block';
            }
        }
    } catch (error) {
        console.error('Error submitting report:', error);
        if (errorDiv) {
            errorDiv.textContent = 'Gagal menghantar laporan. Sila cuba lagi.';
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

// Vote on poll option
async function votePoll(postId, optionId) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const response = await fetch('/api/forum/poll/vote', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            credentials: 'include',
            body: JSON.stringify({
                post_id: postId,
                option_id: optionId,
            }),
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.status === 200) {
            // Update post state with new poll data
            if (postState.post && postState.post.id === postId) {
                postState.post.poll_options = data.data.poll_options;
                postState.post.user_poll_vote = data.data.user_poll_vote;
                postState.post.total_poll_votes = data.data.total_poll_votes;
                // Re-render the post
                renderPostDetail(postState.post);
            }
        } else {
            // Silently fail if user already voted (no error message shown)
            if (data.status === 400 && data.message && data.message.includes('already voted')) {
                // Just reload the post to get updated state
                loadPostDetail();
                return;
            }
            showError(data.message || 'Failed to vote');
        }
    } catch (error) {
        console.error('Error voting on poll:', error);
        showError('Failed to vote on poll');
    }
}

window.toggleReaction = toggleReaction;
window.toggleBookmark = toggleBookmark;
window.togglePostOptions = togglePostOptions;
window.confirmDeletePost = confirmDeletePost;
window.openShareModal = openShareModal;
window.closeShareModal = closeShareModal;
window.shareToConversation = shareToConversation;
window.openReportModal = openReportModal;
window.closeReportModal = closeReportModal;
window.submitReport = submitReport;
window.toggleHidePost = toggleHidePost;
window.votePoll = votePoll;
window.saveSharedLesson = saveSharedLesson;

// Helper to save shared lesson
function saveSharedLesson(lessonId) {
    if (!confirm('Would you like to save a copy of this lesson to your inventory?')) return;

    // Create a form to submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/lessons/${lessonId}/clone`;

    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = '_token';
        input.value = csrfToken;
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
}

// Make functions globally accessible
window.openReportModal = openReportModal;
window.closeReportModal = closeReportModal;
window.submitReport = submitReport;
window.toggleHidePost = toggleHidePost;
window.goBackToReferrer = goBackToReferrer;
window.selectMention = selectMention;
window.searchUserProfile = searchUserProfile;
window.votePoll = votePoll;




