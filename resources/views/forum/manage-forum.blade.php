<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Manage Forum - Material Forum</title>
    <link rel="stylesheet" href="{{ asset('Forum/CSS/forum.css') }}">
    <link rel="stylesheet" href="{{ asset('Forum/CSS/manage-forum.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js for navigation dropdowns -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Scripts for notifications and WebSocket -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @endif
</head>
<body>
    @php
        $currentUser = session('user_id') ? \App\Models\User::find(session('user_id')) : \Illuminate\Support\Facades\Auth::user();
    @endphp
    @include('layouts.navigation', ['currentUser' => $currentUser])

    <div class="reddit-container">

        <div class="manage-forum-header">
            <a href="#" class="back-link" id="backLink">
                <i class="fas fa-arrow-left"></i>
                Back to Forum
            </a>
            <h1>Manage Forum</h1>
        </div>

        <div class="manage-container">
            <div class="success-message" id="successMessage"></div>
            <div class="error-message" id="errorMessage"></div>

            <div class="manage-grid">
                <div>
                    <div class="manage-card">
                        <div class="manage-card-header">
                            <i class="fas fa-users"></i> Forum Members
                        </div>
                        <div class="members-list" id="membersList">
                            <!-- Members will be loaded here -->
                        </div>
                    </div>
                    
                    <div class="manage-card">
                        <div class="manage-card-header">
                            <i class="fas fa-flag"></i> Post Reports
                            <div class="report-status-badges" id="reportStatusBadges" style="display: inline-flex; gap: 8px; margin-left: 12px; font-size: 12px;">
                                <!-- Status badges will be loaded here -->
                            </div>
                        </div>
                        <div class="reports-filter" style="margin-bottom: 16px; display: flex; gap: 8px;">
                            <button class="report-filter-btn active" data-status="all" onclick="filterReports('all')">All</button>
                            <button class="report-filter-btn" data-status="pending" onclick="filterReports('pending')">Pending</button>
                            <button class="report-filter-btn" data-status="reviewed" onclick="filterReports('reviewed')">Reviewed</button>
                            <button class="report-filter-btn" data-status="resolved" onclick="filterReports('resolved')">Resolved</button>
                            <button class="report-filter-btn" data-status="dismissed" onclick="filterReports('dismissed')">Dismissed</button>
                        </div>
                        <div class="reports-list" id="reportsList">
                            <div class="loading">Loading reports...</div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="manage-card">
                        <div class="manage-card-header">
                            <i class="fas fa-cog"></i> Forum Settings
                        </div>
                        
                        <form id="settingsForm">
                            <div class="form-group">
                                <label for="forumTitle">Forum Title</label>
                                <input type="text" id="forumTitle" required>
                            </div>

                            <div class="form-group">
                                <label for="forumDescription">Description</label>
                                <textarea id="forumDescription" rows="6"></textarea>
                            </div>

                            <div class="action-buttons">
                                <button type="submit" class="btn-primary">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="manage-card">
                        <div class="manage-card-header">
                            <i class="fas fa-exclamation-triangle"></i> Danger Zone
                        </div>
                        <button class="btn-danger" onclick="showDeleteConfirm()">
                            <i class="fas fa-trash-alt"></i> Delete Forum
                        </button>
                        <small style="display: block; margin-top: 8px; color: #878a8c; font-size: 12px;">
                            This action cannot be undone
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('Forum/JS/manage-forum.js') }}"></script>
    
    <!-- Notification scripts for real-time updates -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.umd.js"></script>
    <script>
        // Wait for Echo to be available
        (function() {
            function checkEcho() {
                if (typeof window.Echo !== 'undefined') {
                    window.dispatchEvent(new Event('echoReady'));
                    return true;
                } else if (typeof Echo !== 'undefined') {
                    window.Echo = Echo;
                    window.dispatchEvent(new Event('echoReady'));
                    return true;
                }
                return false;
            }
            
            if (!checkEcho()) {
                let attempts = 0;
                const maxAttempts = 30;
                const checkInterval = setInterval(() => {
                    attempts++;
                    if (checkEcho() || attempts >= maxAttempts) {
                        clearInterval(checkInterval);
                    }
                }, 100);
            }
        })();
        
        // Notification functionality
        document.addEventListener('DOMContentLoaded', () => {
            const notificationBtn = document.getElementById('notificationBtn');
            const notificationMenu = document.getElementById('notificationMenu');
            
            if (notificationBtn && notificationMenu) {
                notificationBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    notificationMenu.classList.toggle('hidden');
                    if (!notificationMenu.classList.contains('hidden')) {
                        loadNotifications();
                    }
                });
                
                document.addEventListener('click', (e) => {
                    if (!notificationMenu.contains(e.target) && !notificationBtn.contains(e.target)) {
                        notificationMenu.classList.add('hidden');
                    }
                });
            }
            
            loadNotifications();
            
            if (window.Echo || typeof Echo !== 'undefined') {
                initializeNotificationWebSocket();
            } else {
                window.addEventListener('echoReady', () => {
                    initializeNotificationWebSocket();
                }, { once: true });
            }
        });
        
        async function loadNotifications() {
            try {
                const response = await fetch('/api/notifications?unread_only=true&limit=10', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'include'
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.status === 200) {
                        const unreadCount = data.data.unread_count || 0;
                        const badge = document.getElementById('notificationBadge');
                        if (badge) {
                            if (unreadCount > 0) {
                                badge.textContent = unreadCount > 99 ? '99+' : unreadCount.toString();
                                badge.classList.remove('hidden');
                            } else {
                                badge.classList.add('hidden');
                            }
                        }
                    }
                }
            } catch (error) {
                console.error('Error loading notifications:', error);
            }
        }
        
        function initializeNotificationWebSocket() {
            @auth
            const userId = {{ auth()->id() }};
            if (!userId) return;
            
            if (typeof window.Echo !== 'undefined' && (window.Echo.connector || window.Echo.private)) {
                window.Echo.private(`user.${userId}`)
                    .listen('.notification.created', (data) => {
                        loadNotifications();
                    });
            }
            @endauth
        }
    </script>
</body>
</html>


