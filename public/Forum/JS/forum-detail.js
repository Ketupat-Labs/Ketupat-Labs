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
        window.location.href = '../login.html';
        return;
    }

    // Check if user came from search page or main forum page and store the info
    const referrer = document.referrer;
    if (referrer && referrer.includes('/forum/search')) {
        // Extract search query from referrer URL
        try {
            const referrerUrl = new URL(referrer);
            const searchQuery = referrerUrl.searchParams.get('q') || '';
            if (searchQuery) {
                sessionStorage.setItem('forumSearchQuery', searchQuery);
            }
            sessionStorage.setItem('cameFromSearch', 'true');
            sessionStorage.removeItem('cameFromForumMain');
        } catch (e) {
            // If URL parsing fails, just mark that we came from search
            sessionStorage.setItem('cameFromSearch', 'true');
            sessionStorage.removeItem('cameFromForumMain');
        }
    } else if (referrer && (referrer.includes('/forum') || referrer.includes('/forums'))) {
        // User came from main forum page
        sessionStorage.setItem('cameFromForumMain', 'true');
        sessionStorage.removeItem('cameFromSearch');
        sessionStorage.removeItem('forumSearchQuery');
    } else {
        // Clear any previous navigation state if coming from elsewhere
        sessionStorage.removeItem('cameFromSearch');
        sessionStorage.removeItem('cameFromForumMain');
        sessionStorage.removeItem('forumSearchQuery');
    }

    initEventListeners();
    loadForumDetails();
});

function initEventListeners() {
    // Update back button to return to appropriate page based on where user came from
    const backLink = document.querySelector('.back-link');
    if (backLink) {
        backLink.addEventListener('click', (e) => {
            const cameFromSearch = sessionStorage.getItem('cameFromSearch') === 'true';
            const cameFromForumMain = sessionStorage.getItem('cameFromForumMain') === 'true';
            
            if (cameFromSearch) {
                // Return to search page with query
                e.preventDefault();
                const searchQuery = sessionStorage.getItem('forumSearchQuery') || '';
                window.location.href = `/forum/search${searchQuery ? '?q=' + encodeURIComponent(searchQuery) : ''}`;
            } else if (cameFromForumMain) {
                // Return to main forum page (use /forums to avoid conflict with public/Forum directory)
                e.preventDefault();
                window.location.href = '/forums';
            }
            // If neither flag is set, use default href (goes to forum.index)
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
            window.location.href = '../login.html';
        });
    }

    const btnCreatePost = document.getElementById('btnCreatePost');
    if (btnCreatePost) {
        btnCreatePost.addEventListener('click', () => {
            const forumId = forumState.forumId || new URLSearchParams(window.location.search).get('id');
            window.location.href = `/forum/post/create?forum=${forumId}`;
        });
    }

    const btnCreatePostForForum = document.getElementById('btnCreatePostForForum');
    if (btnCreatePostForForum) {
        btnCreatePostForForum.addEventListener('click', () => {
            const forumId = forumState.forumId || new URLSearchParams(window.location.search).get('id');
            window.location.href = `/forum/post/create?forum=${forumId}`;
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
    // Extract forum ID from URL path (e.g., /forum/1)
    let forumId = null;
    const pathParts = window.location.pathname.split('/').filter(part => part);
    
    // Look for 'forum' in the path and get the ID after it
    const forumIndex = pathParts.indexOf('forum');
    if (forumIndex !== -1 && forumIndex + 1 < pathParts.length) {
        forumId = pathParts[forumIndex + 1];
    }
    
    // Fallback to query parameter if path extraction fails
    if (!forumId) {
        const urlParams = new URLSearchParams(window.location.search);
        forumId = urlParams.get('id');
    }

    if (!forumId) {
        showError('No forum selected');
        return;
    }

    forumState.forumId = forumId;

    try {
        // Load forum details from Laravel API
        const forumResponse = await fetch(`/api/forum/${forumId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'include',
        });
        
        if (!forumResponse.ok) {
            throw new Error(`HTTP error! status: ${forumResponse.status}`);
        }
        
        const forumData = await forumResponse.json();

        if (forumData.status === 200 && forumData.data && forumData.data.forum) {
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
    const btnMore = document.getElementById('btnMore');
    const moreMenuContainer = btnMore ? btnMore.closest('.more-menu-container') : null;
    const manageOption = document.getElementById('manageOption');
    const leaveOption = document.getElementById('leaveOption');
    const muteOption = document.getElementById('muteOption');
    const favoriteOption = document.getElementById('favoriteOption');

    if (forumState.isMember) {
        if (createPostBtn) createPostBtn.style.display = 'flex';
        if (joinedBtn) joinedBtn.style.display = 'block';
        if (joinBtn) joinBtn.style.display = 'none';
        if (btnMore) btnMore.style.display = 'block';
        if (moreMenuContainer) moreMenuContainer.style.display = 'block';
        if (leaveOption) leaveOption.style.display = 'flex';
        if (muteOption) muteOption.style.display = 'flex';
        if (favoriteOption) favoriteOption.style.display = 'flex';

        // Show manage option for admins/moderators
        if (forumState.userRole === 'admin' || forumState.userRole === 'moderator') {
            if (manageOption) manageOption.style.display = 'flex';
        } else {
            if (manageOption) manageOption.style.display = 'none';
        }
    } else {
        if (createPostBtn) createPostBtn.style.display = 'none';
        if (joinedBtn) joinedBtn.style.display = 'none';
        if (joinBtn) joinBtn.style.display = 'block';
        // Hide the entire more options button and container for non-members
        if (btnMore) btnMore.style.display = 'none';
        if (moreMenuContainer) moreMenuContainer.style.display = 'none';
        if (leaveOption) leaveOption.style.display = 'none';
        if (manageOption) manageOption.style.display = 'none';
        // Hide mute and favorite options for non-members
        if (muteOption) muteOption.style.display = 'none';
        if (favoriteOption) favoriteOption.style.display = 'none';
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
    if (!dateString) return 'Unknown date';
    
    const date = new Date(dateString);
    
    // Check if date is valid
    if (isNaN(date.getTime())) {
        return 'Unknown date';
    }
    
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return `${months[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;
}

async function loadPosts() {
    try {
        const response = await fetch(`/api/forum/post?forum_id=${forumState.forumId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'include',
        });
        
        // Handle 403 - requires membership (check before parsing JSON)
        if (response.status === 403) {
            const data = await response.json();
            if (data.data && data.data.requires_membership) {
                renderJoinMessage(data.message || 'You must join this forum to view posts');
                return;
            }
        }
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();

        if (data.status === 200 && data.data && data.data.posts) {
            renderPosts(data.data.posts);
        } else {
            showError(data.message || 'Failed to load posts');
        }
    } catch (error) {
        console.error('Error loading posts:', error);
        showError('Failed to load posts');
    }
}

function renderJoinMessage(message) {
    const container = document.getElementById('postsContent');
    if (!container) return;
    
    container.innerHTML = `
        <div class="empty-state" style="text-align: center; padding: 40px 20px;">
            <i class="fas fa-lock" style="font-size: 48px; color: #878a8c; margin-bottom: 16px;"></i>
            <h3 style="margin-bottom: 12px; color: #1c1c1c;">${escapeHtml(message)}</h3>
            <p style="color: #878a8c; margin-bottom: 24px;">Join this forum to view and participate in discussions.</p>
            <button onclick="joinForum()" class="btn-primary" style="padding: 10px 24px; font-size: 14px; font-weight: 600; border-radius: 4px; background-color: #ff4500; color: white; border: none; cursor: pointer;">
                <i class="fas fa-user-plus"></i> Join Forum
            </button>
        </div>
    `;
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
                                ${post.author_id ? `
                                    <span class="post-author" onclick="event.stopPropagation(); window.location.href = '/profile/' + ${post.author_id};" style="cursor: pointer;">${escapeHtml(post.author_name || post.author_username)}</span>
                                ` : `
                                    <span class="post-author">${escapeHtml(post.author_name || post.author_username)}</span>
                                `}
                                <span class="post-time">${formatTime(post.created_at)}</span>
                                ${post.is_edited ? '<span class="post-time">(edited)</span>' : ''}
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
                            ${post.content ? processYouTubeLinks(post.content) : ''}
                        </div>
                        `}
                        ${post.attachments && post.post_type !== 'link' ? `
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
        const response = await fetch('/api/forum/join', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify({
                forum_id: forumState.forumId
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.status === 200) {
            // Reload the page to show updated forum state
            window.location.reload();
        } else {
            showError(data.message || 'Failed to join forum');
        }
    } catch (error) {
        console.error('Error joining forum:', error);
        showError('Failed to join forum');
    }
}

async function toggleMute() {
    const endpoint = forumState.isMuted ? 'unmute' : 'mute';
    const forumId = forumState.forumId;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const response = await fetch(`/api/forum/${forumId}/${endpoint}`, {
            method: 'POST',
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
            forumState.isMuted = !forumState.isMuted;
            renderForumHeader();
            alert(data.message || `Forum ${forumState.isMuted ? 'muted' : 'unmuted'} successfully`);
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
    window.location.href = `/forum/manage/${forumState.forumId}`;
}

async function leaveForum() {
    if (!confirm('Are you sure you want to leave this forum?')) {
        return;
    }

    try {
        const response = await fetch('/api/forum/leave', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify({
                forum_id: forumState.forumId
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.status === 200) {
            // Reload the current page to show updated forum state
            window.location.reload();
        } else {
            showError(data.message || 'Failed to leave forum');
        }
    } catch (error) {
        console.error('Error leaving forum:', error);
        showError('Failed to leave forum');
    }
}

async function openPost(postId) {
    // Use Laravel route for post detail
    window.location.href = `/forum/post/${postId}`;
}

async function renderPostDetail(post) {
    const container = document.getElementById('postDetailContent');

    container.innerHTML = `
        <div class="post-detail">
            <div class="post-detail-header">
                <div class="post-header">
                    ${post.is_pinned ? '<span class="post-pinned"><i class="fas fa-thumbtack"></i> pinned</span>' : ''}
                    <span class="post-author">${escapeHtml(post.author_name || post.author_username)}</span>
                    <span class="post-time"><i class="fas fa-clock"></i> ${formatTime(post.created_at)}</span>
                    ${post.is_edited ? '<span class="post-time">(edited)</span>' : ''}
                </div>
                <div class="post-detail-title">
                    ${escapeHtml(post.title)}
                </div>
            </div>
            
            <div class="post-detail-content">
                ${post.content ? processYouTubeLinks(post.content) : ''}
            </div>
            
            ${post.attachments && post.post_type !== 'link' ? `
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
                        <div style="font-weight: 600; font-size: 14px; color: #1c1c1c;">${escapeHtml(comment.author_name || comment.author_username)}</div>
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
                post_id: postId
            })
        });

        const data = await response.json();

        if (data.status === 200) {
            // Reload posts to get updated bookmark status
            await loadPosts();
        } else {
            alert(data.message || 'Failed to toggle bookmark');
        }
    } catch (error) {
        console.error('Error toggling bookmark:', error);
        alert('Failed to toggle bookmark');
    }
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
            if (forumState.forumId) {
                await loadPosts();
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

// Make functions globally accessible
window.openReportModal = openReportModal;
window.closeReportModal = closeReportModal;
window.submitReport = submitReport;

