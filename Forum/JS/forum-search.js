document.addEventListener('DOMContentLoaded', () => {
    // Check if user is logged in
    if (sessionStorage.getItem('userLoggedIn') !== 'true') {
        window.location.href = 'login.html';
        return;
    }
    
    initEventListeners();
    performSearch();
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
    
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch();
            }
        });
    }
}

async function performSearch() {
    const urlParams = new URLSearchParams(window.location.search);
    const searchQuery = urlParams.get('q');
    
    if (!searchQuery) {
        renderNoResults('Please enter a search term');
        return;
    }
    
    document.getElementById('searchInput').value = searchQuery;
    document.getElementById('searchTitle').textContent = `Search Results for "${escapeHtml(searchQuery)}"`;
    
    try {
        const response = await fetch(`../api/forum_endpoints.php?action=get_forums&search=${encodeURIComponent(searchQuery)}`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.status === 200) {
            const forums = data.data.forums;
            if (forums.length === 0) {
                renderNoResults(`No forums found for "${escapeHtml(searchQuery)}"`);
            } else {
                renderSearchResults(forums);
            }
        } else {
            showError('Failed to search forums');
        }
    } catch (error) {
        console.error('Error searching forums:', error);
        showError('Failed to search forums');
    }
}

function renderNoResults(message) {
    const container = document.getElementById('searchResults');
    container.innerHTML = `
        <div class="empty-search-state">
            <i class="fas fa-search"></i>
            <h2>No Results</h2>
            <p>${message}</p>
        </div>
    `;
}

function renderSearchResults(forums) {
    const container = document.getElementById('searchResults');
    
    container.innerHTML = `
        <div class="reddit-grid">
            ${forums.map(forum => `
                <div class="reddit-forum-card" onclick="window.location.href='forum-detail.html?id=${forum.id}'">
                    <div class="forum-card-title">
                        ${forum.is_pinned ? '<i class="fas fa-thumbtack" style="color: #ff4500;"></i> ' : ''}
                        ${escapeHtml(forum.title)}
                    </div>
                    <div class="forum-card-description">
                        ${escapeHtml(forum.description || 'No description')}
                    </div>
                    <div class="forum-card-stats">
                        <div class="forum-card-stat">
                            <i class="fas fa-users"></i>
                            <span>${forum.member_count || 0} members</span>
                        </div>
                        <div class="forum-card-stat">
                            <i class="fas fa-comments"></i>
                            <span>${forum.post_count || 0} posts</span>
                        </div>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showError(message) {
    alert(message);
}

