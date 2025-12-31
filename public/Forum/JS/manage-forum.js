let forumState = {
    forumId: null,
    reports: [],
    currentReportFilter: 'all'
};

document.addEventListener('DOMContentLoaded', () => {
    if (sessionStorage.getItem('userLoggedIn') !== 'true') {
        window.location.href = '../login.html';
        return;
    }
    
    initEventListeners();
    loadForumData();
    loadReports();
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
        const forumId = getForumId();
        backLink.href = `/forum/${forumId}`;
    }

    const settingsForm = document.getElementById('settingsForm');
    if (settingsForm) {
        settingsForm.addEventListener('submit', saveSettings);
    }
}

function getForumId() {
    // Try to get from URL path first (e.g., /forum/manage/123)
    const pathParts = window.location.pathname.split('/').filter(part => part);
    const manageIndex = pathParts.indexOf('manage');
    if (manageIndex !== -1 && manageIndex + 1 < pathParts.length) {
        return pathParts[manageIndex + 1];
    }
    
    // Fallback to query parameter
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
        // Load forum details and members in parallel
        const [forumResponse, membersResponse] = await Promise.all([
            fetch(`/api/forum/${forumId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'include',
            }),
            fetch(`/api/forum/${forumId}/members`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'include',
            })
        ]);
        
        if (!forumResponse.ok) {
            throw new Error(`HTTP error! status: ${forumResponse.status}`);
        }
        
        const forumData = await forumResponse.json();
        
        if (forumData.status === 200 && forumData.data && forumData.data.forum) {
            renderForumSettings(forumData.data.forum);
        } else {
            showError(forumData.message || 'Failed to load forum');
        }
        
        // Load members if request was successful
        if (membersResponse.ok) {
            const membersData = await membersResponse.json();
            if (membersData.status === 200 && membersData.data && membersData.data.members) {
                const creatorId = forumData.data && forumData.data.forum ? forumData.data.forum.created_by : null;
                renderMembers(membersData.data.members, creatorId);
            }
        } else if (membersResponse.status === 403) {
            // User doesn't have permission to view members, show empty state
            renderMembers([], null);
        }
    } catch (error) {
        console.error('Error loading forum data:', error);
        showError('Failed to load forum');
    }
}

function renderForumSettings(forum) {
    document.getElementById('forumTitle').value = forum.title;
    document.getElementById('forumDescription').value = forum.description || '';
}

function renderMembers(members, creatorId) {
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
    const isCreator = currentUserId === parseInt(creatorId);
    
    container.innerHTML = members.map(member => {
        const isMemberCreator = member.user_id === parseInt(creatorId);
        const isCurrentUser = member.user_id === currentUserId;
        const canPromote = isCreator && !isMemberCreator && member.role !== 'admin';
        const canRemove = isCreator && !isMemberCreator && !isCurrentUser;
        
        let roleLabel = member.role.toUpperCase();
        if (isMemberCreator) {
            roleLabel = 'CREATOR';
        }
        
        return `
        <div class="member-item">
            <div class="member-info">
                <div class="member-avatar">
                    ${getAvatarInitials(member.full_name || member.username)}
                </div>
                <div class="member-details">
                    <div>
                        <h4>${escapeHtml(member.full_name || member.username)}</h4>
                        <p>${escapeHtml(member.username)}</p>
                    </div>
                    <div class="member-role ${member.role} ${isMemberCreator ? 'creator' : ''}">
                        ${roleLabel}
                    </div>
                </div>
            </div>
            <div class="member-actions">
                ${isCurrentUser ? '<span style="font-size: 12px; color: #878a8c;">You</span>' : ''}
                ${canPromote ? `
                    <button class="btn-action-sm primary" onclick="promoteToAdmin(${member.user_id})">
                        <i class="fas fa-crown"></i> Make Admin
                    </button>
                ` : ''}
                ${canRemove ? `
                    <button class="btn-action-sm danger" onclick="removeMember(${member.user_id})">
                        <i class="fas fa-user-times"></i> Remove
                    </button>
                ` : ''}
            </div>
        </div>
        `;
    }).join('');
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
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const response = await fetch(`/api/forum/${forumState.forumId}/members/promote`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'include',
            body: JSON.stringify({
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
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const response = await fetch(`/api/forum/${forumState.forumId}/members`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'include',
            body: JSON.stringify({
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
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const response = await fetch(`/api/forum/${forumState.forumId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'include',
            body: JSON.stringify({
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
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const response = await fetch(`/api/forum/${forumState.forumId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'include',
        });
        
        const data = await response.json();
        
        if (data.status === 200) {
            alert('Forum deleted successfully');
            window.location.href = '/forums';
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

// Report Management Functions
async function loadReports() {
    const forumId = getForumId();
    if (!forumId) return;
    
    try {
        const response = await fetch(`/api/forum/${forumId}/reports`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'include',
        });
        
        if (!response.ok) {
            if (response.status === 403) {
                const data = await response.json();
                document.getElementById('reportsList').innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-lock"></i>
                        <p>${data.message || 'You do not have permission to view reports'}</p>
                    </div>
                `;
                return;
            }
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.status === 200) {
            forumState.reports = data.data.reports || [];
            renderReportStatusBadges(data.data.status_counts || {});
            renderReports(forumState.reports);
        } else {
            console.error('Failed to load reports:', data);
            document.getElementById('reportsList').innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>${data.message || 'Failed to load reports'}</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading reports:', error);
        document.getElementById('reportsList').innerHTML = `
            <div class="empty-state">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Failed to load reports: ${error.message}</p>
            </div>
        `;
    }
}

function renderReportStatusBadges(statusCounts) {
    const container = document.getElementById('reportStatusBadges');
    if (!container) return;
    
    container.innerHTML = `
        <span class="status-badge pending" title="Pending Reports">${statusCounts.pending || 0} Pending</span>
        <span class="status-badge reviewed" title="Reviewed Reports">${statusCounts.reviewed || 0} Reviewed</span>
        <span class="status-badge resolved" title="Resolved Reports">${statusCounts.resolved || 0} Resolved</span>
        <span class="status-badge dismissed" title="Dismissed Reports">${statusCounts.dismissed || 0} Dismissed</span>
    `;
}

function filterReports(status) {
    forumState.currentReportFilter = status;
    
    // Update filter buttons
    document.querySelectorAll('.report-filter-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.status === status) {
            btn.classList.add('active');
        }
    });
    
    // Filter and render reports
    let filteredReports = forumState.reports;
    if (status !== 'all') {
        filteredReports = forumState.reports.filter(r => r.status === status);
    }
    
    renderReports(filteredReports);
}

function renderReports(reports) {
    const container = document.getElementById('reportsList');
    if (!container) return;
    
    if (!reports || reports.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-flag"></i>
                <p>No reports found</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = reports.map(report => {
        const statusClass = `status-${report.status}`;
        const statusLabel = report.status.charAt(0).toUpperCase() + report.status.slice(1);
        const reasonLabels = {
            spam: 'Spam',
            harassment: 'Harassment or Bullying',
            inappropriate: 'Inappropriate Content',
            misinformation: 'Misinformation',
            other: 'Other'
        };
        
        return `
            <div class="report-item ${statusClass}">
                <div class="report-header">
                    <div class="report-info">
                        <div class="report-post-title">
                            <a href="/forum/post/${report.post_id}" target="_blank" style="color: #2454FF; text-decoration: none; font-weight: 600;">
                                ${escapeHtml(report.post_title)}
                            </a>
                        </div>
                        <div class="report-meta">
                            <span class="report-reporter">
                                <i class="fas fa-user"></i> ${escapeHtml(report.reporter_name)}
                            </span>
                            <span class="report-reason">
                                <i class="fas fa-exclamation-circle"></i> ${reasonLabels[report.reason] || report.reason}
                            </span>
                            <span class="report-date">
                                <i class="fas fa-clock"></i> ${formatDate(report.created_at)}
                            </span>
                        </div>
                    </div>
                    <div class="report-status-badge ${statusClass}">
                        ${statusLabel}
                    </div>
                </div>
                ${report.details ? `
                    <div class="report-details">
                        <strong>Details:</strong> ${escapeHtml(report.details)}
                    </div>
                ` : ''}
                ${report.review_notes ? `
                    <div class="report-review-notes">
                        <strong>Review Notes:</strong> ${escapeHtml(report.review_notes)}
                    </div>
                ` : ''}
                ${report.status === 'pending' ? `
                    <div class="report-actions">
                        <button class="btn-action-sm primary" onclick="updateReportStatus(${report.id}, 'reviewed')">
                            <i class="fas fa-eye"></i> Mark as Reviewed
                        </button>
                        <button class="btn-action-sm success" onclick="updateReportStatus(${report.id}, 'resolved')">
                            <i class="fas fa-check"></i> Resolve
                        </button>
                        <button class="btn-action-sm warning" onclick="updateReportStatus(${report.id}, 'dismissed')">
                            <i class="fas fa-times"></i> Dismiss
                        </button>
                    </div>
                ` : ''}
            </div>
        `;
    }).join('');
}

async function updateReportStatus(reportId, status) {
    const statusLabels = {
        reviewed: 'reviewed',
        resolved: 'resolved',
        dismissed: 'dismissed'
    };
    
    const statusLabel = statusLabels[status] || status;
    const reviewNotes = prompt(`Add review notes (optional):`);
    
    if (reviewNotes === null && status !== 'dismissed') {
        // User cancelled, but allow dismissal without notes
        return;
    }
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const response = await fetch(`/api/forum/report/${reportId}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'include',
            body: JSON.stringify({
                status: status,
                review_notes: reviewNotes || null
            })
        });
        
        const data = await response.json();
        
        if (data.status === 200) {
            showSuccess(`Report marked as ${statusLabel}`);
            loadReports(); // Reload reports
        } else {
            showError(data.message || `Failed to update report status`);
        }
    } catch (error) {
        console.error('Error updating report status:', error);
        showError('Failed to update report status');
    }
}

function formatDate(dateString) {
    if (!dateString) return 'Unknown';
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
    if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
    
    return date.toLocaleDateString();
}

// Make functions globally accessible
window.showDeleteConfirm = showDeleteConfirm;
window.promoteToAdmin = promoteToAdmin;
window.removeMember = removeMember;
window.filterReports = filterReports;
window.updateReportStatus = updateReportStatus;

