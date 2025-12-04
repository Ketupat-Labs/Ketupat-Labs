let forumState = {
    forumId: null
};

document.addEventListener('DOMContentLoaded', () => {
    if (sessionStorage.getItem('userLoggedIn') !== 'true') {
        window.location.href = '../login.html';
        return;
    }
    
    initEventListeners();
    loadForumData();
});

function initEventListeners() {
    const btnLogout = document.getElementById('btnLogout');
    if (btnLogout) {
        btnLogout.addEventListener('click', async () => {
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

    const backLink = document.getElementById('backLink');
    if (backLink) {
        backLink.href = `forum-detail.html?id=${getForumId()}`;
    }

    const settingsForm = document.getElementById('settingsForm');
    if (settingsForm) {
        settingsForm.addEventListener('submit', saveSettings);
    }
}

function getForumId() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id');
}

async function loadForumData() {
    const forumId = getForumId();
    
    if (!forumId) {
        showError('No forum ID provided');
        return;
    }
    
    forumState.forumId = forumId;
    
    try {
        // Load forum details and members
        const [forumResponse, membersResponse] = await Promise.all([
            fetch(`../api/forum_endpoints.php?action=get_forum_details&forum_id=${forumId}`, {
                credentials: 'include'
            }),
            fetch(`../api/forum_endpoints.php?action=get_forum_members&forum_id=${forumId}`, {
                credentials: 'include'
            })
        ]);
        
        const forumData = await forumResponse.json();
        const membersData = await membersResponse.json();
        
        if (forumData.status === 200) {
            renderForumSettings(forumData.data.forum);
        }
        
        if (membersData.status === 200) {
            renderMembers(membersData.data.members);
        }
    } catch (error) {
        console.error('Error loading forum data:', error);
        showError('Failed to load forum data');
    }
}

function renderForumSettings(forum) {
    document.getElementById('forumTitle').value = forum.title;
    document.getElementById('forumDescription').value = forum.description || '';
}

function renderMembers(members) {
    const container = document.getElementById('membersList');
    
    if (!members || members.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <p>No members found</p>
            </div>
        `;
        return;
    }
    
    const currentUserId = parseInt(sessionStorage.getItem('userId')) || 0;
    
    container.innerHTML = members.map(member => `
        <div class="member-item">
            <div class="member-info">
                <div class="member-avatar">
                    ${getAvatarInitials(member.full_name || member.username)}
                </div>
                <div class="member-details">
                    <h4>${escapeHtml(member.full_name || member.username)}</h4>
                    <p>${escapeHtml(member.username)}</p>
                </div>
            </div>
            <div class="member-role ${member.role}">
                ${member.role.toUpperCase()}
            </div>
            <div class="member-actions">
                ${member.user_id !== currentUserId ? `
                    ${member.role !== 'admin' ? `
                        <button class="btn-action-sm primary" onclick="promoteToAdmin(${member.user_id})">
                            <i class="fas fa-crown"></i> Make Admin
                        </button>
                    ` : ''}
                    <button class="btn-action-sm danger" onclick="removeMember(${member.user_id})">
                        <i class="fas fa-user-times"></i> Remove
                    </button>
                ` : '<span style="font-size: 12px; color: #878a8c;">You</span>'}
            </div>
        </div>
    `).join('');
}

function getAvatarInitials(name) {
    if (!name) return '?';
    const words = name.split(' ');
    if (words.length >= 2) {
        return (words[0][0] + words[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
}

async function promoteToAdmin(userId) {
    if (!confirm('Promote this user to admin? They will have full control over the forum.')) {
        return;
    }
    
    try {
        const response = await fetch('../api/forum_endpoints.php?action=promote_member', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({
                forum_id: forumState.forumId,
                user_id: userId
            })
        });
        
        const data = await response.json();
        
        if (data.status === 200) {
            showSuccess('User promoted to admin');
            loadForumData();
        } else {
            showError(data.message || 'Failed to promote user');
        }
    } catch (error) {
        console.error('Error promoting user:', error);
        showError('Failed to promote user');
    }
}

async function removeMember(userId) {
    if (!confirm('Remove this member from the forum?')) {
        return;
    }
    
    try {
        const response = await fetch('../api/forum_endpoints.php?action=remove_member', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({
                forum_id: forumState.forumId,
                user_id: userId
            })
        });
        
        const data = await response.json();
        
        if (data.status === 200) {
            showSuccess('Member removed');
            loadForumData();
        } else {
            showError(data.message || 'Failed to remove member');
        }
    } catch (error) {
        console.error('Error removing member:', error);
        showError('Failed to remove member');
    }
}

async function saveSettings(e) {
    e.preventDefault();
    
    const title = document.getElementById('forumTitle').value.trim();
    const description = document.getElementById('forumDescription').value.trim();
    
    try {
        const response = await fetch('../api/forum_endpoints.php?action=update_forum', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({
                forum_id: forumState.forumId,
                title,
                description
            })
        });
        
        const data = await response.json();
        
        if (data.status === 200) {
            showSuccess('Forum settings updated successfully');
        } else {
            showError(data.message || 'Failed to update forum');
        }
    } catch (error) {
        console.error('Error updating forum:', error);
        showError('Failed to update forum');
    }
}

function showDeleteConfirm() {
    if (!confirm('Are you sure you want to delete this forum? This action cannot be undone. All posts and members will be removed.')) {
        return;
    }
    
    const confirmed = prompt('Type "DELETE" to confirm:');
    
    if (confirmed !== 'DELETE') {
        alert('Deletion cancelled');
        return;
    }
    
    deleteForum();
}

async function deleteForum() {
    try {
        const response = await fetch('../api/forum_endpoints.php?action=delete_forum', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({
                forum_id: forumState.forumId
            })
        });
        
        const data = await response.json();
        
        if (data.status === 200) {
            alert('Forum deleted successfully');
            window.location.href = 'forum.html';
        } else {
            showError(data.message || 'Failed to delete forum');
        }
    } catch (error) {
        console.error('Error deleting forum:', error);
        showError('Failed to delete forum');
    }
}

function showSuccess(message) {
    const successDiv = document.getElementById('successMessage');
    successDiv.textContent = message;
    successDiv.classList.add('show');
    
    setTimeout(() => {
        successDiv.classList.remove('show');
    }, 3000);
}

function showError(message) {
    const errorDiv = document.getElementById('errorMessage');
    errorDiv.textContent = message;
    errorDiv.classList.add('show');
    
    setTimeout(() => {
        errorDiv.classList.remove('show');
    }, 5000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Make functions globally accessible
window.showDeleteConfirm = showDeleteConfirm;
window.promoteToAdmin = promoteToAdmin;
window.removeMember = removeMember;

