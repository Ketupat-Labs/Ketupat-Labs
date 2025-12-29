// Profile page JavaScript

function showSection(sectionName) {
    // Hide all sections
    document.querySelectorAll('.section-content').forEach(section => {
        section.classList.add('hidden');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.section-tab').forEach(tab => {
        tab.classList.remove('active', 'border-blue-500', 'text-blue-600');
        tab.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected section
    const section = document.getElementById('section-' + sectionName);
    if (section) {
        section.classList.remove('hidden');
    }
    
    // Add active class to selected tab
    const tab = document.getElementById('tab-' + sectionName);
    if (tab) {
        tab.classList.add('active', 'border-blue-500', 'text-blue-600');
        tab.classList.remove('border-transparent', 'text-gray-500');
    }
}

async function addFriend(friendId) {
    try {
        const response = await fetch('/api/friends/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'include',
            body: JSON.stringify({ friend_id: friendId })
        });

        const data = await response.json();
        
        if (data.status === 200) {
            alert('Friend request sent!');
            window.location.reload();
        } else {
            alert(data.message || 'Failed to send friend request');
        }
    } catch (error) {
        console.error('Error adding friend:', error);
        alert('Failed to send friend request');
    }
}

async function removeFriend(friendId) {
    if (!confirm('Are you sure you want to remove this friend?')) {
        return;
    }

    try {
        const response = await fetch('/api/friends/remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'include',
            body: JSON.stringify({ friend_id: friendId })
        });

        const data = await response.json();
        
        if (data.status === 200) {
            alert('Friend removed');
            window.location.reload();
        } else {
            alert(data.message || 'Failed to remove friend');
        }
    } catch (error) {
        console.error('Error removing friend:', error);
        alert('Failed to remove friend');
    }
}

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
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
                
                // Create compact link preview
                return createLinkPreview(firstUrl, domain, description);
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
                <script async src="https://www.tiktok.com/embed.js"><\/script>
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

// Helper function to create a compact link preview (for non-video links)
function createLinkPreview(url, domain, description) {
    // Extract title from description or use domain
    const title = description ? description.split('\n')[0].substring(0, 100) : domain;
    const fullDescription = description ? description.substring(title.length).trim() : '';
    
    return `
        <div class="link-preview-container" style="margin: 16px 0; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; background: #fff; display: flex; cursor: pointer; transition: box-shadow 0.2s;" 
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
                <div style="font-weight: 500; color: #333; margin-bottom: 4px; word-wrap: break-word; line-height: 1.4;">
                    ${escapeHtml(title)}
                </div>
                ${fullDescription ? `
                    <div style="font-size: 0.9em; color: #666; margin-bottom: 4px; word-wrap: break-word; line-height: 1.4;">
                        ${escapeHtml(fullDescription.substring(0, 150))}${fullDescription.length > 150 ? '...' : ''}
                    </div>
                ` : ''}
                <div style="font-size: 0.85em; color: #999; margin-top: 4px;">
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

// Process YouTube links in post content previews
function processPostContentPreviews() {
    const previews = document.querySelectorAll('.post-content-preview');
    previews.forEach(preview => {
        const content = preview.getAttribute('data-content');
        if (content) {
            const processedContent = processYouTubeLinks(content);
            // Check if content contains YouTube link
            const hasYouTube = /(?:youtube\.com|youtu\.be)/.test(content);
            if (hasYouTube) {
                // Replace the preview with processed content
                preview.innerHTML = processedContent;
                preview.classList.remove('line-clamp-2');
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Process YouTube links in post previews
    processPostContentPreviews();

    // Profile Badge Filter Logic
    let currentCategory = 'all';
    let currentStatus = 'all';

    const categoryBtns = document.querySelectorAll('#profileCategoryFilters .filter-btn');
    const statusBtns = document.querySelectorAll('#profileStatusFilters .filter-btn');
    const badgeItems = document.querySelectorAll('#profileBadgesGrid .badge-item');

    function filterBadges() {
        badgeItems.forEach(item => {
            const itemCategory = item.getAttribute('data-category');
            const itemStatus = item.getAttribute('data-status-type');

            const matchCategory = (currentCategory === 'all' || itemCategory === currentCategory);
            const matchStatus = (currentStatus === 'all' || itemStatus === currentStatus);

            if (matchCategory && matchStatus) {
                item.classList.remove('hidden');
            } else {
                item.classList.add('hidden');
            }
        });
    }

    // Category Filter Events
    categoryBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            categoryBtns.forEach(b => {
                b.classList.remove('active', 'border-blue-500', 'bg-blue-500', 'text-white');
                b.classList.add('border-gray-300', 'text-gray-600', 'bg-white');
            });
            this.classList.remove('border-gray-300', 'text-gray-600', 'bg-white');
            this.classList.add('active', 'border-blue-500', 'bg-blue-500', 'text-white');
            currentCategory = this.getAttribute('data-filter');
            filterBadges();
        });
    });

    // Status Filter Events
    statusBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            statusBtns.forEach(b => {
                b.classList.remove('active', 'border-blue-500', 'bg-blue-500', 'text-white');
                b.classList.add('border-gray-300', 'text-gray-600', 'bg-white');
            });
            this.classList.remove('border-gray-300', 'text-gray-600', 'bg-white');
            this.classList.add('active', 'border-blue-500', 'bg-blue-500', 'text-white');
            currentStatus = this.getAttribute('data-filter');
            filterBadges();
        });
    });
});

