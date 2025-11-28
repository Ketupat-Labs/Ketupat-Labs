// Dashboard JavaScript

document.addEventListener('DOMContentLoaded', () => {
    // Check if dashboardData is available
    if (!window.dashboardData) {
        console.error('Dashboard data not found');
        return;
    }
    
    initEventListeners();
    loadDashboardData();
});

function initEventListeners() {
    // User menu toggle
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userMenu = document.getElementById('userMenu');
    
    if (userMenuBtn && userMenu) {
        userMenuBtn.addEventListener('click', () => {
            userMenu.classList.toggle('hidden');
            // Close notification menu when opening user menu
            const notificationMenu = document.getElementById('notificationMenu');
            if (notificationMenu) {
                notificationMenu.classList.add('hidden');
            }
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!userMenuBtn.contains(e.target) && !userMenu.contains(e.target)) {
                userMenu.classList.add('hidden');
            }
        });
    }
    
    // Notification menu toggle
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationMenu = document.getElementById('notificationMenu');
    
    if (notificationBtn && notificationMenu) {
        notificationBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            notificationMenu.classList.toggle('hidden');
            // Close user menu when opening notification menu
            if (userMenu) {
                userMenu.classList.add('hidden');
            }
        });
        
        // Close notification menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!notificationBtn.contains(e.target) && !notificationMenu.contains(e.target)) {
                notificationMenu.classList.add('hidden');
            }
        });
    }
    
    // Mobile menu toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }
    
    // Quick access card click handlers
    document.querySelectorAll('.quick-access-card').forEach(card => {
        card.addEventListener('click', (e) => {
            e.preventDefault();
            const action = card.getAttribute('data-action');
            handleQuickAccess(action);
        });
    });
    
    // Quick action button click handlers
    document.querySelectorAll('.quick-action-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const action = btn.getAttribute('data-action');
            handleQuickAccess(action);
        });
    });
}

function handleQuickAccess(action) {
    const role = window.dashboardData?.userRole || 'student';
    
    // Map actions to URLs (these can be updated when routes are created)
    const actionMap = {
        'view-lessons': '#',
        'take-quiz': '#',
        'submit-assignment': '#',
        'manage-lessons': '#',
        'review-submissions': '#',
        'assign-lessons': '#',
        'create-lesson': '#',
        'browse-lessons': '#'
    };
    
    const url = actionMap[action] || '#';
    
    if (url !== '#') {
        window.location.href = url;
    } else {
        console.log('Action not yet implemented:', action);
        // You can add notifications here when routes are ready
    }
}

async function loadDashboardData() {
    try {
        await Promise.all([
            loadStats(),
            loadRecentLessons(),
            loadCounts()
        ]);
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
}

async function loadStats() {
    try {
        const response = await fetch('../api/dashboard_endpoints.php?action=get_stats');
        const result = await response.json();
        
        if (result.status === 200 && result.data.stats) {
            renderStats(result.data.stats);
        } else {
            console.error('Error loading stats:', result.message);
            renderStats([]);
        }
    } catch (error) {
        console.error('Error fetching stats:', error);
        renderStats([]);
    }
}

function renderStats(stats) {
    const statsSection = document.getElementById('statsSection');
    if (!statsSection) return;
    
    if (stats.length === 0) {
        statsSection.innerHTML = '<div class="col-span-full text-center py-8 text-gray-500">No statistics available</div>';
        return;
    }
    
    statsSection.innerHTML = stats.map(stat => {
        const iconSvg = getStatIcon(stat.icon, stat.color);
        
        return `
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium" style="color: #969696;">${stat.label}</p>
                        <p class="text-3xl font-bold mt-2" style="color: ${stat.color};">${stat.value}</p>
                    </div>
                    <div class="p-3 rounded-lg" style="background-color: ${hexToRgba(stat.color, 0.1)};">
                        ${iconSvg}
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function getStatIcon(type, color) {
    const iconClass = `w-8 h-8`;
    const iconStyle = `color: ${color};`;
    
    switch (type) {
        case 'lessons':
            return `
                <svg class="${iconClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="${iconStyle}">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            `;
        case 'quiz':
            return `
                <svg class="${iconClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="${iconStyle}">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
            `;
        case 'submissions':
            return `
                <svg class="${iconClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="${iconStyle}">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
            `;
        case 'points':
            return `
                <svg class="${iconClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="${iconStyle}">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                </svg>
            `;
        default:
            return '';
    }
}

function hexToRgba(hex, alpha) {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

async function loadRecentLessons() {
    try {
        const response = await fetch('../api/dashboard_endpoints.php?action=get_recent_lessons');
        const result = await response.json();
        
        if (result.status === 200 && result.data.lessons) {
            renderRecentLessons(result.data.lessons);
        } else {
            console.error('Error loading recent lessons:', result.message);
            renderRecentLessons([]);
        }
    } catch (error) {
        console.error('Error fetching recent lessons:', error);
        renderRecentLessons([]);
    }
}

function renderRecentLessons(lessons) {
    const recentSection = document.getElementById('recentLessonsSection');
    if (!recentSection) return;
    
    if (lessons.length === 0) {
        recentSection.innerHTML = `
            <div class="text-center py-8" style="color: #969696;">
                <p>No lessons available yet</p>
            </div>
        `;
        return;
    }
    
    recentSection.innerHTML = lessons.map(lesson => {
        const role = window.dashboardData?.userRole || 'student';
        const statusText = role === 'teacher' && lesson.is_published !== undefined
            ? (lesson.is_published ? 'Published' : 'Draft')
            : (lesson.duration ? `${lesson.duration} mins` : 'N/A');
        
        return `
            <a href="#" class="flex items-center p-4 bg-gray-50 rounded-lg transition-colors group lesson-item" style="background-color: #f9fafb;" 
               onmouseover="this.style.backgroundColor='rgba(36, 84, 255, 0.05)'" 
               onmouseout="this.style.backgroundColor='#f9fafb'">
                <div class="flex-shrink-0 p-2 rounded-lg mr-4" style="background-color: rgba(36, 84, 255, 0.1);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #2454FF;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold transition-colors" style="color: #3E3E3E;">${escapeHtml(lesson.title)}</p>
                    <p class="text-xs mt-1" style="color: #969696;">${escapeHtml(lesson.topic || 'N/A')} • ${escapeHtml(statusText)}</p>
                </div>
                <svg class="w-5 h-5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #969696;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        `;
    }).join('');
}

async function loadCounts() {
    try {
        const response = await fetch('../api/dashboard_endpoints.php?action=get_stats');
        const result = await response.json();
        
        if (result.status === 200 && result.data.counts) {
            const counts = result.data.counts;
            
            // Update count displays
            if (counts.availableLessons !== undefined) {
                const el = document.getElementById('availableLessonsCount');
                if (el) el.textContent = `${counts.availableLessons} lessons available`;
            }
            
            if (counts.myLessons !== undefined) {
                const el = document.getElementById('myLessonsCount');
                if (el) el.textContent = `${counts.myLessons} lessons created`;
            }
            
            if (counts.quizAttempts !== undefined) {
                const el = document.getElementById('quizAttemptsCount');
                if (el) el.textContent = `${counts.quizAttempts} attempts`;
            }
            
            if (counts.pendingSubmissions !== undefined) {
                const el = document.getElementById('pendingSubmissionsCount');
                if (el) el.textContent = `${counts.pendingSubmissions} pending`;
            }
        }
    } catch (error) {
        console.error('Error loading counts:', error);
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

