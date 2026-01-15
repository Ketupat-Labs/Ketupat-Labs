const messagingState = {
    currentConversationId: null,
    conversations: [],
    messages: [],
    typingTimeout: null,
    isTyping: false,
    ws: null,
    wsReconnectAttempts: 0,
    maxReconnectAttempts: 5,
    reconnectDelay: 3000,
    typingUsers: new Set(), // Track users currently typing
    typingUserNames: new Map(), // Map user_id to user name for typing indicator
    participants: [], // Store conversation participants for typing indicator
    inactiveTimeout: null, // Track inactive timeout
    lastActivityTime: Date.now(), // Track last user activity
    currentTab: 'active', // 'active' or 'archived'
    searchResults: [],
    lastOnlineStatus: null, // Track last sent online status
    statusUpdateThrottle: null, // Throttle for status updates
    isSelectionMode: false, // Message selection mode
    selectedMessages: new Set(), // Set of selected message IDs
    replyingTo: null, // Message being replied to
    pinnedMessages: new Set(), // Set of pinned message IDs
    pollingInterval: null, // Track polling interval to prevent duplicates
    isWebSocketConnected: false, // Track WebSocket connection status
    lastMessageLoadTime: null, // Track when messages were last loaded to prevent rapid reloads
};

document.addEventListener('DOMContentLoaded', () => {
    // Check if user is logged in
    const userLoggedIn = sessionStorage.getItem('userLoggedIn');
    if (userLoggedIn !== 'true') {
        window.location.href = '/login';
        return;
    }

    // Remove any existing emoji picker containers
    const existingPicker = document.getElementById('emojiPickerContainer');
    if (existingPicker) {
        existingPicker.remove();
    }

    // Clear old message caches on page load
    clearOldMessageCaches();

    initNavigation();
    loadNavigationUserInfo();
    initEventListeners();
    loadConversations();

    // Listen for echoReady event before connecting WebSocket
    window.addEventListener('echoReady', () => {
        console.log('echoReady event received in messaging.js, connecting WebSocket...');
        connectWebSocket();
    });

    // Also try to connect immediately (in case Echo is already ready)
    // And set a timeout as fallback
    setTimeout(() => {
        if (!messagingState.isWebSocketConnected) {
            console.log('Echo not connected yet, attempting connection...');
            connectWebSocket();
        }
    }, 500);

    loadPinnedMessages();

    const messageInput = document.getElementById('messageInput');
    if (messageInput) {
        messageInput.addEventListener('input', autoResizeTextarea);
    }

    // Handle shared post link
    handleSharedPost();

    // Initialize inactive timeout tracking
    initInactiveTimeout();

    // Track user activity
    trackUserActivity();
});

async function handleSharedPost() {
    const urlParams = new URLSearchParams(window.location.search);
    const conversationId = urlParams.get('conversation');
    const shareUrl = urlParams.get('share');

    if (conversationId) {
        // Wait for conversations to load
        await new Promise(resolve => setTimeout(resolve, 500));

        // Select the conversation
        await selectConversation(parseInt(conversationId));

        if (shareUrl) {
            // Pre-fill the message input with the shared post link
            const messageInput = document.getElementById('messageInput');
            if (messageInput) {
                messageInput.value = `Check out this post: ${decodeURIComponent(shareUrl)}`;
                messageInput.style.height = 'auto';
                messageInput.style.height = messageInput.scrollHeight + 'px';
                messageInput.focus();
            }
        }

        // Clean up URL parameters
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
}

function initEventListeners() {
    document.getElementById('btnSend').addEventListener('click', sendMessage);
    document.getElementById('messageInput').addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    document.getElementById('messageInput').addEventListener('input', handleTyping);

    // Prevent context menu on white area (chat messages container)
    const chatMessagesContainer = document.getElementById('chatMessages');
    if (chatMessagesContainer) {
        chatMessagesContainer.addEventListener('contextmenu', (e) => {
            // Only prevent default if clicking on the container itself, not on a message bubble
            // Message bubbles will handle their own contextmenu events
            if (e.target === chatMessagesContainer || e.target.closest('.message-bubble') === null) {
                e.preventDefault();
            }
        });
    }

    document.getElementById('btnAttach').addEventListener('click', () => {
        document.getElementById('fileInput').click();
    });
    document.getElementById('fileInput').addEventListener('change', handleFileUpload);

    document.getElementById('btnCreateConversation').addEventListener('click', openCreateConversationModal);
    document.getElementById('closeConversationModal').addEventListener('click', closeCreateConversationModal);
    document.getElementById('cancelConversationModal').addEventListener('click', closeCreateConversationModal);

    document.getElementById('closeDMModal').addEventListener('click', closeCreateDMModal);
    document.getElementById('cancelDMModal').addEventListener('click', closeCreateDMModal);

    document.getElementById('closeGroupModal').addEventListener('click', closeCreateGroupModal);
    document.getElementById('cancelGroupModal').addEventListener('click', closeCreateGroupModal);
    document.getElementById('createGroupForm').addEventListener('submit', createGroupChat);

    // Search members functionality (for group chat)
    const searchMembersInput = document.getElementById('searchMembers');
    if (searchMembersInput) {
        let searchTimeout = null;
        searchMembersInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const searchTerm = e.target.value.trim();
            searchTimeout = setTimeout(() => {
                loadAvailableMembers(searchTerm);
            }, 300); // Debounce search
        });
    }

    // Search DM users functionality
    const dmSearchUsersInput = document.getElementById('dmSearchUsers');
    if (dmSearchUsersInput) {
        let dmSearchTimeout = null;
        dmSearchUsersInput.addEventListener('input', (e) => {
            clearTimeout(dmSearchTimeout);
            const searchTerm = e.target.value.trim();
            dmSearchTimeout = setTimeout(() => {
                loadDMUsers(searchTerm);
            }, 300); // Debounce search
        });
    }

    document.getElementById('searchConversations').addEventListener('input', searchConversations);
    document.getElementById('sortConversations').addEventListener('change', loadConversations);

    document.getElementById('btnCloseInfo').addEventListener('click', closeInfoSidebar);

    // Search messages
    document.getElementById('btnSearchMessages').addEventListener('click', openSearchMessages);
    document.getElementById('btnCloseSearch').addEventListener('click', closeSearchMessages);
    document.getElementById('searchMessagesInput').addEventListener('input', handleSearchMessagesInput);
    document.getElementById('searchMessagesInput').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            performMessageSearch();
        }
    });

    // Archive conversation
    document.getElementById('btnArchiveConversation').addEventListener('click', archiveCurrentConversation);

    // Conversation tabs
    document.getElementById('tabActive').addEventListener('click', () => switchTab('active'));
    document.getElementById('tabArchived').addEventListener('click', () => switchTab('archived'));
}

function initNavigation() {
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userMenu = document.getElementById('userMenu');
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationMenu = document.getElementById('notificationMenu');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');

    if (userMenuBtn && userMenu) {
        userMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userMenu.classList.toggle('hidden');
            if (notificationMenu) notificationMenu.classList.add('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!userMenuBtn.contains(e.target) && !userMenu.contains(e.target)) {
                userMenu.classList.add('hidden');
            }
        });
    }

    if (notificationBtn && notificationMenu) {
        notificationBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            notificationMenu.classList.toggle('hidden');
            if (userMenu) userMenu.classList.add('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!notificationBtn.contains(e.target) && !notificationMenu.contains(e.target)) {
                notificationMenu.classList.add('hidden');
            }
        });
    }

    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }
}

async function loadNavigationUserInfo() {
    // User info is now loaded server-side via Blade, so this function is not needed
    // But we keep it for backward compatibility in case it's called elsewhere
    try {
        const response = await fetch('/api/auth/me', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'include'
        });
        if (!response.ok) return;
        const data = await response.json();
        if (data.status === 200 && data.data?.user) {
            const user = data.data.user;
            // Navigation is now server-rendered, so we don't need to update it
        }
    } catch (error) {
        console.error('Failed to load user info:', error);
    }
}

function autoResizeTextarea() {
    const textarea = document.getElementById('messageInput');
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
}

async function loadConversations() {
    try {
        // Check if user is logged in
        const userLoggedIn = sessionStorage.getItem('userLoggedIn');
        if (userLoggedIn !== 'true') {
            window.location.href = '/login';
            return;
        }

        // Load based on current tab
        if (messagingState.currentTab === 'archived') {
            await loadArchivedConversations();
            return;
        }

        const sortElement = document.getElementById('sortConversations');
        const sort = sortElement ? sortElement.value : 'recent';
        const response = await fetch(`/api/messaging/conversations?sort=${sort}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'include'
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.status === 200 || data.status === 401) {
            // 200 = success, 401 = unauthorized (will redirect)
            if (data.status === 401) {
                window.location.href = '/login';
                return;
            }
            messagingState.conversations = data.data && data.data.conversations ? data.data.conversations : [];
            renderConversations();
        } else {
            // For any other status, just show empty state (don't show error)
            console.warn('API returned status:', data.status, data.message || '');
            messagingState.conversations = [];
            renderConversations();
        }
    } catch (error) {
        console.error('Error loading conversations:', error);
        // Silently handle errors - just show empty state
        // This prevents annoying alerts when there are no conversations yet
        messagingState.conversations = [];
        if (document.getElementById('conversationsList')) {
            renderConversations();
        }
    }
}

function renderConversations() {
    const container = document.getElementById('conversationsList');

    if (!messagingState.conversations || messagingState.conversations.length === 0) {
        container.innerHTML = '<div style="text-align: center; padding: 40px; color: #999; font-size: 14px;">Tiada perbualan lagi. Mulakan perbualan baharu dengan pengguna lain.</div>';
        return;
    }

    container.innerHTML = messagingState.conversations.map(conv => {
        // Handle friend-only entries (no conversation ID yet)
        let conversationId;
        if (conv.id !== null) {
            conversationId = conv.id;
        } else if (conv.is_friend_only && conv.other_user_id) {
            conversationId = `'friend_${conv.other_user_id}'`;
        } else {
            conversationId = 'null';
        }

        const isActive = conv.id === messagingState.currentConversationId;

        return `
        <div class="conversation-item ${isActive ? 'active' : ''}" 
             onclick="selectConversation(${conversationId})">
            <div class="conversation-avatar ${conv.other_avatar ? '' : ''}">
                ${conv.other_avatar ?
                `<img src="${escapeHtml(conv.other_avatar)}" alt="${escapeHtml(conv.other_username || '')}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                     <div style="display: none; width: 100%; height: 100%; background: linear-gradient(135deg, #2454FF 0%, #4B7BFF 100%); border-radius: 50%; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.9rem;">
                        ${conv.other_username ? escapeHtml(conv.other_username.charAt(0).toUpperCase()) : '?'}
                     </div>` :
                conv.type === 'group' ?
                    '<i class="fas fa-users"></i>' :
                    `<div style="width: 100%; height: 100%; background: linear-gradient(135deg, #2454FF 0%, #4B7BFF 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.9rem;">
                            ${conv.other_username ? escapeHtml(conv.other_username.charAt(0).toUpperCase()) : '?'}
                         </div>`
            }
            </div>
            <div class="conversation-details">
                <div class="conversation-header">
                    <span class="conversation-name">
                        ${conv.type === 'group' ? conv.name : conv.other_full_name || conv.other_username}
                    </span>
                    <span class="conversation-time">${conv.last_message_time ? formatTime(conv.last_message_time) : ''}</span>
                </div>
                <div class="conversation-preview">${conv.last_message || (conv.is_friend_only ? 'Friend' : 'Tiada mesej lagi')}</div>
            </div>
            <div class="conversation-meta">
                ${conv.is_online && conv.type === 'direct' ? '<span class="online-indicator"></span>' : ''}
                ${conv.unread_count > 0 ? `<span class="unread-badge">${conv.unread_count}</span>` : ''}
            </div>
            ${messagingState.currentTab === 'archived' && conv.id ? `
                <div class="conversation-actions" onclick="event.stopPropagation(); unarchiveConversation(${conv.id})">
                    <button class="btn-unarchive" title="Unarchive">
                        <i class="fas fa-inbox"></i>
                    </button>
                </div>
            ` : ''}
        </div>
    `;
    }).join('');
}

async function unarchiveConversation(conversationId) {
    try {
        const response = await fetch('/api/messaging/archive', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({
                conversation_id: conversationId,
                archive: false
            })
        });

        const data = await response.json();

        if (data.status === 200) {
            await loadArchivedConversations();
        } else {
            showError(data.message || 'Failed to unarchive conversation');
        }
    } catch (error) {
        console.error('Error unarchiving conversation:', error);
        showError('Failed to unarchive conversation');
    }
}

async function selectConversation(conversationId) {
    // Handle friend-only entries (string format: 'friend_123')
    let actualConversationId = conversationId;

    if (typeof conversationId === 'string' && conversationId.startsWith('friend_')) {
        const friendUserId = parseInt(conversationId.replace('friend_', ''));
        const conversation = messagingState.conversations.find(c =>
            c.is_friend_only && c.other_user_id === friendUserId
        );

        if (conversation && conversation.other_user_id) {
            // This is a friend without a conversation, create one first
            try {
                const response = await fetch('/api/messaging/conversation/direct', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        user_id: conversation.other_user_id
                    })
                });

                const data = await response.json();

                if (data.status === 200 && data.data && data.data.conversation_id) {
                    // Reload conversations to get the new conversation
                    await loadConversations();
                    // Select the newly created conversation
                    actualConversationId = data.data.conversation_id;
                } else {
                    alert(data.message || 'Failed to create conversation');
                    return;
                }
            } catch (error) {
                console.error('Error creating conversation:', error);
                alert('Failed to create conversation');
                return;
            }
        } else {
            return; // Friend not found
        }
    }

    // Leave previous conversation
    if (messagingState.currentConversationId) {
        leaveConversation(messagingState.currentConversationId);
    }

    messagingState.currentConversationId = actualConversationId;

    // Clear messages and cache immediately when switching conversations
    messagingState.messages = [];
    messageElementsCache.clear();
    lastRenderedMessageIds.clear();

    // Clear the messages container immediately
    const chatMessagesContainer = document.getElementById('chatMessages');
    if (chatMessagesContainer) {
        chatMessagesContainer.innerHTML = '';
    }

    // Get conversation data from cache for instant UI update
    const cachedConversation = messagingState.conversations.find(c => c.id === actualConversationId);

    // INSTANT UI UPDATE: Show conversation header immediately from cached data
    if (cachedConversation) {
        loadConversationDetailsFromCache(cachedConversation);
    }

    // Show loading state for messages
    showMessagesLoading();

    // Show input container immediately
    document.getElementById('chatInputContainer').style.display = 'block';
    document.getElementById('chatActionsBar').style.display = 'flex';

    // Update conversation list highlight
    renderConversations();

    // Join new conversation via WebSocket immediately
    joinConversation(actualConversationId);

    // Clear typing indicators
    messagingState.typingUsers.clear();
    messagingState.typingUserNames.clear();
    updateTypingIndicatorDisplay();

    // Close search if open
    closeSearchMessages();

    // Load messages in background (non-blocking)
    loadMessages(actualConversationId).then(() => {
        // After messages load, update conversation details with fresh data
        loadConversationDetails(actualConversationId);
        scrollToBottom();
    }).catch(error => {
        console.error('Error loading messages:', error);
        hideMessagesLoading();
        showError('Failed to load messages');
    });
}

function scrollToBottom(smooth = false) {
    const messagesContainer = document.getElementById('chatMessages');
    if (messagesContainer) {
        if (smooth) {
            messagesContainer.scrollTo({
                top: messagesContainer.scrollHeight,
                behavior: 'smooth'
            });
        } else {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }
}

// LocalStorage cache functions for messages
const MESSAGE_CACHE_PREFIX = 'msg_cache_';
const CACHE_EXPIRY_HOURS = 24; // Cache expires after 24 hours

function getMessageCacheKey(conversationId) {
    return `${MESSAGE_CACHE_PREFIX}${conversationId}`;
}

function getCachedMessages(conversationId) {
    try {
        const cacheKey = getMessageCacheKey(conversationId);
        const cached = localStorage.getItem(cacheKey);
        if (!cached) return null;

        const cacheData = JSON.parse(cached);
        const now = Date.now();
        const cacheAge = now - cacheData.timestamp;
        const expiryMs = CACHE_EXPIRY_HOURS * 60 * 60 * 1000;

        // Check if cache is expired
        if (cacheAge > expiryMs) {
            localStorage.removeItem(cacheKey);
            return null;
        }

        return cacheData.messages || null;
    } catch (error) {
        console.error('Error reading message cache:', error);
        return null;
    }
}

function saveMessagesToCache(conversationId, messages) {
    try {
        const cacheKey = getMessageCacheKey(conversationId);
        const cacheData = {
            timestamp: Date.now(),
            messages: messages,
            conversationId: conversationId
        };
        localStorage.setItem(cacheKey, JSON.stringify(cacheData));
    } catch (error) {
        console.error('Error saving message cache:', error);
        // If storage is full, try to clear old caches
        if (error.name === 'QuotaExceededError') {
            clearOldMessageCaches();
            try {
                localStorage.setItem(cacheKey, JSON.stringify(cacheData));
            } catch (e) {
                console.error('Still unable to save cache after cleanup:', e);
            }
        }
    }
}

function clearOldMessageCaches() {
    try {
        const now = Date.now();
        const expiryMs = CACHE_EXPIRY_HOURS * 60 * 60 * 1000;
        const keysToRemove = [];

        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key && key.startsWith(MESSAGE_CACHE_PREFIX)) {
                try {
                    const cached = JSON.parse(localStorage.getItem(key));
                    const cacheAge = now - cached.timestamp;
                    if (cacheAge > expiryMs) {
                        keysToRemove.push(key);
                    }
                } catch (e) {
                    // Invalid cache entry, remove it
                    keysToRemove.push(key);
                }
            }
        }

        keysToRemove.forEach(key => localStorage.removeItem(key));
    } catch (error) {
        console.error('Error clearing old caches:', error);
    }
}

function updateMessageInCache(conversationId, message) {
    try {
        const cacheKey = getMessageCacheKey(conversationId);
        const cached = localStorage.getItem(cacheKey);
        if (!cached) return;

        const cacheData = JSON.parse(cached);
        const messages = cacheData.messages || [];

        // Check if message already exists
        const existingIndex = messages.findIndex(m => m.id === message.id);
        if (existingIndex >= 0) {
            // Update existing message
            messages[existingIndex] = message;
        } else {
            // Add new message (append to end since messages are sorted by time)
            messages.push(message);
            // Re-sort by created_at
            messages.sort((a, b) => {
                return new Date(a.created_at) - new Date(b.created_at);
            });
        }

        cacheData.messages = messages;
        cacheData.timestamp = Date.now();
        localStorage.setItem(cacheKey, JSON.stringify(cacheData));
    } catch (error) {
        console.error('Error updating message in cache:', error);
    }
}

function removeMessageFromCache(conversationId, messageId) {
    try {
        const cacheKey = getMessageCacheKey(conversationId);
        const cached = localStorage.getItem(cacheKey);
        if (!cached) return;

        const cacheData = JSON.parse(cached);
        const messages = (cacheData.messages || []).filter(m => m.id !== messageId);

        cacheData.messages = messages;
        cacheData.timestamp = Date.now();
        localStorage.setItem(cacheKey, JSON.stringify(cacheData));
    } catch (error) {
        console.error('Error removing message from cache:', error);
    }
}

function clearConversationCache(conversationId) {
    try {
        const cacheKey = getMessageCacheKey(conversationId);
        localStorage.removeItem(cacheKey);
    } catch (error) {
        console.error('Error clearing conversation cache:', error);
    }
}

async function loadMessages(conversationId, page = 1, forceReload = false) {
    try {
        // Prevent rapid reloads (throttle to max once per 2 seconds for same conversation)
        const now = Date.now();
        if (!forceReload && page === 1 && messagingState.lastMessageLoadTime &&
            messagingState.lastMessageLoadTime.conversationId === conversationId &&
            (now - messagingState.lastMessageLoadTime.timestamp) < 2000) {
            console.log('Skipping rapid reload for conversation', conversationId);
            return;
        }

        // For page 1, try to load from cache first
        if (page === 1 && !forceReload) {
            const cachedMessages = getCachedMessages(conversationId);
            if (cachedMessages && cachedMessages.length > 0) {
                // Load from cache immediately
                messagingState.messages = cachedMessages;

                // Double-check conversation ID before rendering
                if (messagingState.currentConversationId === conversationId) {
                    renderMessages();
                    hideMessagesLoading();
                    scrollToBottom();

                    // Still fetch from server in background to get latest messages
                    // This ensures we have the most up-to-date data
                    // But only if WebSocket is not connected (to avoid duplicate updates)
                    if (!messagingState.isWebSocketConnected) {
                        // Continue to fetch from server below
                    } else {
                        // WebSocket is connected, skip server fetch to avoid flickering
                        messagingState.lastMessageLoadTime = {
                            conversationId: conversationId,
                            timestamp: now
                        };
                        return;
                    }
                } else {
                    return; // Conversation changed, don't render cached messages
                }
            }
        }

        // Check if this is a group conversation to request members
        const conversation = messagingState.conversations.find(c => c.id === conversationId);
        const isGroup = conversation && conversation.type === 'group';
        const loadMembers = isGroup && page === 1; // Only load members on first page

        const url = `/api/messaging/conversation/${conversationId}/messages?page=${page}${loadMembers ? '&load_members=true' : ''}`;

        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'include'
        });
        const data = await response.json();

        // Hide loading state
        hideMessagesLoading();

        // CRITICAL: Check if conversation ID still matches current conversation
        // This prevents race conditions where old API calls overwrite new conversation messages
        if (messagingState.currentConversationId !== conversationId) {
            console.log(`Ignoring messages for conversation ${conversationId} - current conversation is ${messagingState.currentConversationId}`);
            return;
        }

        if (data.status === 200) {
            const newMessages = data.data.messages || [];

            if (page === 1) {
                // Reverse messages since API returns DESC (newest first), but we want oldest first for display
                messagingState.messages = newMessages.reverse();
                // Save to cache
                saveMessagesToCache(conversationId, messagingState.messages);
            } else {
                // For pagination, prepend older messages (but only if still on same conversation)
                if (messagingState.currentConversationId === conversationId) {
                    messagingState.messages = [...newMessages.reverse(), ...messagingState.messages];
                    // Update cache with merged messages
                    saveMessagesToCache(conversationId, messagingState.messages);
                } else {
                    return; // Don't update if conversation changed
                }
            }

            // Double-check conversation ID before rendering
            if (messagingState.currentConversationId !== conversationId) {
                return;
            }

            // Remove duplicates based on message ID
            const uniqueMessages = [];
            const seenIds = new Set();
            for (const msg of messagingState.messages) {
                if (!seenIds.has(msg.id)) {
                    seenIds.add(msg.id);
                    uniqueMessages.push(msg);
                }
            }
            messagingState.messages = uniqueMessages;

            // Sort by created_at to ensure correct order
            messagingState.messages.sort((a, b) => {
                return new Date(a.created_at) - new Date(b.created_at);
            });

            // Update cache with deduplicated and sorted messages
            if (page === 1) {
                saveMessagesToCache(conversationId, messagingState.messages);
            }

            // Final check before rendering
            if (messagingState.currentConversationId === conversationId) {
                // Always render if there are no messages (to show empty state)
                // Or if messages changed or it's pagination
                const currentMessageIds = new Set(messagingState.messages.map(m => m.id));
                const previousMessageIds = new Set(Array.from(lastRenderedMessageIds));
                const messagesChanged = currentMessageIds.size !== previousMessageIds.size ||
                    Array.from(currentMessageIds).some(id => !previousMessageIds.has(id));

                // Always render if no messages (to show empty state) or if messages changed
                if (messagingState.messages.length === 0 || messagesChanged || page !== 1) {
                    renderMessages();
                }

                // Store participants for typing indicator
                if (data.data.conversation && data.data.conversation.members) {
                    messagingState.participants = Array.isArray(data.data.conversation.members)
                        ? data.data.conversation.members
                        : [];
                }

                if (data.data.conversation && data.data.conversation.type === 'group') {
                    loadGroupInfo(data.data.conversation);
                }

                // Update last load time
                messagingState.lastMessageLoadTime = {
                    conversationId: conversationId,
                    timestamp: Date.now()
                };
            }
        } else {
            // API returned non-200 status
            hideMessagesLoading();
            // If no messages in state, show empty state
            if (!messagingState.messages || messagingState.messages.length === 0) {
                renderMessages(); // This will show empty state
            }
            showError(data.message || 'Gagal memuatkan mesej');
        }
    } catch (error) {
        console.error('Error loading messages:', error);
        hideMessagesLoading();
        // Only show error if still on the same conversation
        if (messagingState.currentConversationId === conversationId) {
            // If no messages, show empty state instead of error
            if (!messagingState.messages || messagingState.messages.length === 0) {
                renderMessages(); // This will show empty state
            } else {
                showError('Gagal memuatkan mesej. Sila cuba lagi.');
            }
        }
    }
}

// Cache for rendered message elements to avoid full re-renders
let messageElementsCache = new Map();
let lastRenderedMessageIds = new Set();

// Show loading state for messages
function showMessagesLoading() {
    const container = document.getElementById('chatMessages');
    if (!container) return;

    container.innerHTML = `
        <div style="display: flex; justify-content: center; align-items: center; padding: 40px; min-height: 200px;">
            <div style="text-align: center;">
                <div class="loading-spinner" style="border: 3px solid #f3f3f3; border-top: 3px solid #3498db; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 10px;"></div>
                <div style="color: #999; font-size: 14px;">Loading messages...</div>
            </div>
        </div>
        <style>
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>
    `;
}

// Hide loading state
function hideMessagesLoading() {
    const container = document.getElementById('chatMessages');
    if (!container) return;

    // If container shows loading spinner, ensure it gets cleared
    // renderMessages() will be called to show messages or empty state
    // This is a safety check - renderMessages() should always be called after hideMessagesLoading()
}

function renderMessages() {
    const container = document.getElementById('chatMessages');

    if (!container) return;

    // Filter messages to only show messages for current conversation
    // This is a safety check in case messages array contains messages from different conversations
    const currentConversationId = messagingState.currentConversationId;
    const filteredMessages = messagingState.messages.filter(msg => {
        // If message has conversation_id, filter by it
        if (msg.conversation_id) {
            return msg.conversation_id === currentConversationId;
        }
        // Otherwise, assume it's for current conversation (for backward compatibility)
        return true;
    });

    if (filteredMessages.length === 0) {
        container.innerHTML = '<div style="text-align: center; padding: 40px; color: #999; margin-top: auto;">Tiada mesej lagi. Mulakan perbualan!</div>';
        messageElementsCache.clear();
        lastRenderedMessageIds.clear();
        return;
    }

    // Use filtered messages for rendering
    const messagesToRender = filteredMessages;

    // Store scroll position before rendering
    const wasAtBottom = isScrolledToBottom(container);
    const previousScrollHeight = container.scrollHeight;

    const currentUserId = getCurrentUserId();
    const currentMessageIds = new Set(messagesToRender.map(m => m.id));

    // Check if we can do incremental update (only new messages added)
    const hasRemovedMessages = Array.from(lastRenderedMessageIds).some(id => !currentMessageIds.has(id));

    // If messages were removed or order changed, do full re-render
    if (hasRemovedMessages || messagesToRender.length !== lastRenderedMessageIds.size) {
        messageElementsCache.clear();
        lastRenderedMessageIds.clear();
    }

    // Use DocumentFragment for better performance
    const fragment = document.createDocumentFragment();
    const tempDiv = document.createElement('div');

    // Pre-calculate dates to avoid repeated parsing
    const messageDates = messagesToRender.map(msg => ({
        msg,
        date: new Date(msg.created_at),
        timestamp: new Date(msg.created_at).getTime()
    }));

    // Render messages efficiently
    messageDates.forEach(({ msg, timestamp }, index) => {
        // Check cache first
        let messageElement = messageElementsCache.get(msg.id);

        if (!messageElement) {
            // Compare as integers to handle string/number type mismatches
            const isOwn = parseInt(msg.sender_id) === parseInt(currentUserId);
            const prevMsgData = index > 0 ? messageDates[index - 1] : null;
            const isGrouped = prevMsgData &&
                prevMsgData.msg.sender_id === msg.sender_id &&
                (timestamp - prevMsgData.timestamp) < 300000; // 5 minutes

            // Check if message is selected
            const isSelected = messagingState.isSelectionMode && messagingState.selectedMessages.has(msg.id);

            // Build message HTML
            const messageHtml = `
                <div class="message-group ${isSelected ? 'message-selected' : ''} ${messagingState.isSelectionMode ? 'selection-mode' : ''} ${isOwn ? 'message-own-group' : ''}" 
                     data-message-id="${msg.id}" 
                     ${messagingState.isSelectionMode ? `onclick="toggleMessageSelection(${msg.id})" style="cursor: pointer;"` : ''}>
                    <div class="message-checkbox">
                        <input type="checkbox" data-message-id="${msg.id}" ${isSelected ? 'checked' : ''} 
                               ${messagingState.isSelectionMode ? `onclick="event.stopPropagation(); toggleMessageSelection(${msg.id});"` : ''}>
                    </div>
                    <div class="message ${isOwn ? 'own' : ''}">
                        ${!isGrouped ? `
                            <div class="message-avatar" ${!isOwn && !messagingState.isSelectionMode ? `onclick="event.stopPropagation(); openDirectMessage(${msg.sender_id})" style="cursor: pointer;" title="Click to message ${escapeHtml(msg.full_name || msg.username || 'user')}"` : ''}>
                                ${msg.avatar_url ?
                        `<img src="${escapeHtml(msg.avatar_url)}" alt="${escapeHtml(msg.username || '')}" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                     <div style="display: none; width: 100%; height: 100%; background: linear-gradient(135deg, #2454FF 0%, #4B7BFF 100%); border-radius: 50%; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.9rem;">
                                        ${msg.username ? escapeHtml(msg.username.charAt(0).toUpperCase()) : '?'}
                                     </div>` :
                        `<div style="width: 100%; height: 100%; background: linear-gradient(135deg, #2454FF 0%, #4B7BFF 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.9rem;">
                                        ${msg.username ? escapeHtml(msg.username.charAt(0).toUpperCase()) : '?'}
                                     </div>`
                    }
                            </div>
                        ` : '<div class="message-avatar" style="visibility: hidden; width: 34px;"></div>'}
                        <div class="message-content">
                            ${!isGrouped && !isOwn ? `<div style="font-size: 12px; color: #666; margin-bottom: 5px;">${escapeHtml(msg.full_name || msg.username || 'Unknown')}</div>` : ''}
                            <div class="message-bubble ${msg.message_type === 'shared_post' ? 'has-shared-post' : ''}" ${messagingState.isSelectionMode ? '' : `oncontextmenu="event.preventDefault(); event.stopPropagation(); showMessageContextMenu(event, ${msg.id}, ${isOwn ? 'true' : 'false'})"`}>
                                ${msg.message_type === 'text' ?
                    `<div>${formatMessageContent(msg.content)}</div>` :
                    msg.message_type === 'shared_post' && msg.attachment_url ?
                        `<div id="shared-post-${msg.id}" class="shared-post-container" style="min-width: 220px; max-width: 350px; width: 100%;">Loading post preview...</div>` :
                        msg.message_type === 'image' && msg.attachment_url ?
                            `<div class="message-image-container" style="max-width: 400px; max-height: 400px; border-radius: 8px; overflow: hidden; cursor: pointer;" onclick="window.open('${escapeHtml(msg.attachment_url)}', '_blank');">
                                        <img src="${escapeHtml(msg.attachment_url)}" alt="${escapeHtml(msg.attachment_name || 'Image')}" style="width: 100%; height: auto; display: block;" onerror="this.parentElement.innerHTML='<div style=\\'padding: 20px; text-align: center; color: #999;\\'><i class=\\'fas fa-image\\'></i><br>Image not available</div>';">
                                        ${msg.content ? `<div style="padding: 8px; background: rgba(0,0,0,0.05); font-size: 0.9rem; color: #333;">${formatMessageContent(msg.content)}</div>` : ''}
                                    </div>` :
                            msg.message_type === 'link' && msg.attachment_url ?
                                renderLinkPreview(msg) :
                                `<div class="message-attachment">
                                        <a href="${escapeHtml(msg.attachment_url)}" target="_blank" class="attachment-file">
                                            <i class="fas ${getFileIcon(msg.attachment_name)}"></i>
                                            <span>${escapeHtml(msg.attachment_name || '')}</span>
                                        </a>
                                    </div>`
                }
                            </div>
                            <div class="message-meta">
                                ${msg.is_edited ? '<span class="message-edited">edited</span>' : ''}
                                <span>${formatTime(msg.created_at)}</span>
                            </div>
                            ${renderMessageReactions(msg)}
                        </div>
                    </div>
                </div>
            `;

            tempDiv.innerHTML = messageHtml;
            messageElement = tempDiv.firstElementChild;
            messageElementsCache.set(msg.id, messageElement.cloneNode(true));
        } else {
            // Use cached element
            messageElement = messageElement.cloneNode(true);
        }

        fragment.appendChild(messageElement);
    });

    // Clear container and append fragment (single DOM operation)
    container.innerHTML = '';
    container.appendChild(fragment);

    // Load shared post previews asynchronously after DOM is updated
    setTimeout(() => {
        messagesToRender.forEach(msg => {
            if (msg.message_type === 'shared_post' && msg.attachment_url) {
                const previewContainer = document.getElementById(`shared-post-${msg.id}`);
                if (previewContainer) {
                    // Determine if this is own message by checking parent element
                    const messageElement = previewContainer.closest('[data-message-id]');
                    const isOwn = messageElement ? messageElement.closest('.message')?.classList.contains('own') || false : false;
                    renderSharedPostPreview(msg, isOwn).then(html => {
                        // Double-check container still exists (might have been removed)
                        const container = document.getElementById(`shared-post-${msg.id}`);
                        if (container && container.parentElement) {
                            // Find parent message bubble and add class to remove bubble styling
                            const messageBubble = container.closest('.message-bubble');
                            if (messageBubble) {
                                messageBubble.classList.add('has-shared-post');
                            }
                            // Replace container with preview HTML using proper DOM manipulation
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = html.trim();
                            const previewElement = tempDiv.firstElementChild;
                            if (previewElement) {
                                container.replaceWith(previewElement);
                                // Update cache to prevent re-rendering
                                messageElementsCache.delete(msg.id);
                            } else {
                                console.error('Failed to parse preview HTML for message', msg.id);
                                container.innerHTML = html;
                            }
                        }
                    }).catch(error => {
                        console.error('Error rendering shared post preview:', error);
                        const container = document.getElementById(`shared-post-${msg.id}`);
                        if (container) {
                            container.innerHTML = `<div style="padding: 12px; color: #999; font-size: 0.9em;">Failed to load post preview. <a href="${escapeHtml(msg.attachment_name || '#')}" target="_blank" style="color: #2454FF;">View post</a></div>`;
                        }
                    });
                }
            }
        });
    }, 100); // Small delay to ensure DOM is ready

    // Update cache tracking
    lastRenderedMessageIds = new Set(currentMessageIds);

    // Restore scroll position or scroll to bottom
    if (wasAtBottom) {
        // User was at bottom, scroll to new bottom
        requestAnimationFrame(() => {
            scrollToBottom();
        });
    } else {
        // User was scrolling up, maintain relative position
        requestAnimationFrame(() => {
            const newScrollHeight = container.scrollHeight;
            const scrollDifference = newScrollHeight - previousScrollHeight;
            if (scrollDifference > 0) {
                container.scrollTop += scrollDifference;
            }
        });
    }
}

function isScrolledToBottom(container, threshold = 100) {
    if (!container) return true;
    return container.scrollHeight - container.scrollTop - container.clientHeight < threshold;
}

// Extract URL from text
function extractUrl(text) {
    const urlPattern = /(https?:\/\/[^\s]+)/gi;
    const matches = text.match(urlPattern);
    return matches ? matches[0] : null;
}

// Fetch link preview
async function fetchLinkPreview(url) {
    try {
        const response = await fetch(`../api/link_preview_endpoint.php?url=${encodeURIComponent(url)}`);
        const data = await response.json();
        if (data.status === 200) {
            return data.data;
        }
    } catch (error) {
        console.error('Error fetching link preview:', error);
    }
    return null;
}

async function sendMessage() {
    const input = document.getElementById('messageInput');
    const content = input.value.trim();

    if (!content || !messagingState.currentConversationId) return;

    const sendBtn = document.getElementById('btnSend');
    sendBtn.disabled = true;

    try {
        // Check if message contains a URL
        const url = extractUrl(content);
        let linkPreview = null;
        let messageType = 'text';
        let attachmentUrl = null;
        let attachmentName = null;

        if (url) {
            // Fetch link preview
            const originalBtnContent = sendBtn.innerHTML;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            linkPreview = await fetchLinkPreview(url);

            if (linkPreview && linkPreview.title) {
                messageType = 'link';
                attachmentUrl = JSON.stringify(linkPreview);
                attachmentName = url;
            }
            sendBtn.innerHTML = originalBtnContent;
        }

        // Include reply context if replying
        let replyContext = null;
        if (messagingState.replyingTo) {
            replyContext = {
                message_id: messagingState.replyingTo.id,
                sender_name: messagingState.replyingTo.full_name || messagingState.replyingTo.username,
                content_preview: messagingState.replyingTo.content.substring(0, 100)
            };
        }

        const response = await fetch('/api/messaging/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({
                conversation_id: messagingState.currentConversationId,
                content: content,
                message_type: messageType,
                attachment_url: attachmentUrl,
                attachment_name: attachmentName,
                reply_to: replyContext ? replyContext.message_id : null
            })
        });

        const data = await response.json();

        if (data.status === 200) {
            input.value = '';
            input.style.height = 'auto';

            // Clear reply preview
            cancelReply();

            // Optimistically add message to UI immediately
            const currentUserId = getCurrentUserId();

            // Get user info from existing messages or fetch it
            let userInfo = null;
            if (messagingState.messages.length > 0) {
                // Try to find user info from existing messages sent by current user
                const ownMessage = messagingState.messages.find(m => parseInt(m.sender_id) === parseInt(currentUserId));
                if (ownMessage) {
                    userInfo = {
                        username: ownMessage.username,
                        full_name: ownMessage.full_name,
                        avatar_url: ownMessage.avatar_url
                    };
                }
            }

            const optimisticMessage = {
                id: data.data.message_id,
                conversation_id: messagingState.currentConversationId,
                sender_id: parseInt(currentUserId),
                content: content,
                message_type: messageType,
                attachment_url: attachmentUrl,
                attachment_name: attachmentName,
                created_at: new Date().toISOString(),
                is_edited: false,
                reactions: {},
                username: userInfo?.username || 'You',
                full_name: userInfo?.full_name || 'You',
                avatar_url: userInfo?.avatar_url || null
            };

            // Add message to array if it doesn't exist
            const messageExists = messagingState.messages.some(m => m.id === optimisticMessage.id);
            if (!messageExists) {
                messagingState.messages.push(optimisticMessage);
                // Sort by created_at to maintain order
                messagingState.messages.sort((a, b) => {
                    return new Date(a.created_at) - new Date(b.created_at);
                });
                renderMessages();
                scrollToBottom(true);
            }

            // Update conversations list
            await loadConversations();

            // If WebSocket doesn't deliver within 1 second, reload messages to get server data
            setTimeout(async () => {
                // Check if the new message is already in the array with full data
                const messageExists = messagingState.messages.some(m => m.id === data.data.message_id);
                if (!messageExists) {
                    // Message not received via WebSocket, reload from server
                    await loadMessages(messagingState.currentConversationId);
                } else {
                    // Message exists, reload to ensure we have the latest server data
                    await loadMessages(messagingState.currentConversationId);
                }

                // Update pin indicators after loading
                setTimeout(() => {
                    messagingState.pinnedMessages.forEach(messageId => {
                        updatePinIndicator(messageId);
                    });
                }, 100);

                scrollToBottom(true);
            }, 1000);
        } else {
            showError(data.message || 'Gagal menghantar mesej');
        }
    } catch (error) {
        console.error('Error sending message:', error);
        showError('Gagal menghantar mesej. Sila cuba lagi.');
    } finally {
        sendBtn.disabled = false;
        if (sendBtn.innerHTML.indexOf('fa-paper-plane') === -1) {
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
        }
    }
}

async function handleFileUpload(event) {
    const files = event.target.files;
    if (!files || files.length === 0) return;

    if (!messagingState.currentConversationId) {
        showError('Sila pilih perbualan terlebih dahulu');
        return;
    }

    for (const file of files) {
        await uploadFile(file);
    }

    event.target.value = '';
}

async function uploadFile(file) {
    const formData = new FormData();
    formData.append('file', file);

    try {
        const response = await fetch('/api/upload', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'include',
            body: formData
        });

        const data = await response.json();

        if (data.status === 200) {
            const sendResponse = await fetch('/api/messaging/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    conversation_id: messagingState.currentConversationId,
                    content: file.name,
                    message_type: 'document',
                    attachment_url: data.data.url,
                    attachment_name: file.name,
                    attachment_size: file.size
                })
            });

            const sendData = await sendResponse.json();
            if (sendData.status === 200) {
                // Notify via WebSocket that new message was sent
                if (messagingState.ws && messagingState.ws.readyState === WebSocket.OPEN) {
                    messagingState.ws.send(JSON.stringify({
                        type: 'new_message',
                        conversation_id: messagingState.currentConversationId,
                        message: {
                            id: sendData.data.message_id,
                            content: file.name
                        }
                    }));
                }

                await loadMessages(messagingState.currentConversationId);
                await loadConversations();

                // Update pin indicators after loading
                setTimeout(() => {
                    messagingState.pinnedMessages.forEach(messageId => {
                        updatePinIndicator(messageId);
                    });
                }, 100);

                // Scroll to bottom after sending file
                setTimeout(() => scrollToBottom(true), 150);
            } else {
                showError(sendData.message || 'Gagal menghantar fail');
            }
        } else {
            showError(data.message || 'Gagal memuat naik fail');
        }
    } catch (error) {
        console.error('Error uploading file:', error);
        showError('Gagal memuat naik fail. Sila cuba lagi.');
    }
}

function searchConversations() {
    const keyword = document.getElementById('searchConversations').value.toLowerCase();
    const conversations = messagingState.conversations;

    if (!keyword) {
        renderConversations();
        return;
    }

    const filtered = conversations.filter(conv => {
        const name = conv.type === 'group' ? conv.name : (conv.other_full_name || conv.other_username || '');
        const lastMessage = conv.last_message || '';
        return name.toLowerCase().includes(keyword) || lastMessage.toLowerCase().includes(keyword);
    });

    renderFilteredConversations(filtered);
}

function renderFilteredConversations(conversations) {
    const container = document.getElementById('conversationsList');

    if (conversations.length === 0) {
        container.innerHTML = '<div style="text-align: center; padding: 40px; color: #999; font-size: 14px;">No conversations found</div>';
        return;
    }

    container.innerHTML = conversations.map(conv => `
        <div class="conversation-item ${conv.id === messagingState.currentConversationId ? 'active' : ''}" 
             onclick="selectConversation(${conv.id})">
            <div class="conversation-avatar ${conv.other_avatar ? '' : ''}">
                ${conv.other_avatar ?
            `<img src="${escapeHtml(conv.other_avatar)}" alt="${escapeHtml(conv.other_username || '')}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                     <div style="display: none; width: 100%; height: 100%; background: linear-gradient(135deg, #2454FF 0%, #4B7BFF 100%); border-radius: 50%; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.9rem;">
                        ${conv.other_username ? escapeHtml(conv.other_username.charAt(0).toUpperCase()) : '?'}
                     </div>` :
            conv.type === 'group' ?
                '<i class="fas fa-users"></i>' :
                `<div style="width: 100%; height: 100%; background: linear-gradient(135deg, #2454FF 0%, #4B7BFF 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.9rem;">
                            ${conv.other_username ? escapeHtml(conv.other_username.charAt(0).toUpperCase()) : '?'}
                         </div>`
        }
            </div>
            <div class="conversation-details">
                <div class="conversation-header">
                    <span class="conversation-name">
                        ${conv.type === 'group' ? conv.name : conv.other_full_name || conv.other_username}
                    </span>
                    <span class="conversation-time">${conv.last_message_time ? formatTime(conv.last_message_time) : ''}</span>
                </div>
                <div class="conversation-preview">${conv.last_message || 'Tiada mesej lagi'}</div>
            </div>
            <div class="conversation-meta">
                ${conv.is_online && conv.type === 'direct' ? '<span class="online-indicator"></span>' : ''}
                ${conv.unread_count > 0 ? `<span class="unread-badge">${conv.unread_count}</span>` : ''}
            </div>
        </div>
    `).join('');
}

// Search messages within conversation
function openSearchMessages() {
    document.getElementById('searchMessagesContainer').style.display = 'block';
    document.getElementById('searchMessagesInput').focus();
}

function closeSearchMessages() {
    document.getElementById('searchMessagesContainer').style.display = 'none';
    document.getElementById('searchMessagesInput').value = '';
    document.getElementById('searchResults').innerHTML = '';
    messagingState.searchResults = [];
}

let searchTimeout = null;
function handleSearchMessagesInput() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        performMessageSearch();
    }, 500); // Debounce search
}

async function performMessageSearch() {
    const keyword = document.getElementById('searchMessagesInput').value.trim();
    const conversationId = messagingState.currentConversationId;

    if (!keyword || !conversationId) {
        document.getElementById('searchResults').innerHTML = '';
        return;
    }

    try {
        const response = await fetch(`/api/messaging/search?conversation_id=${conversationId}&keyword=${encodeURIComponent(keyword)}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'include'
        });

        const data = await response.json();

        if (data.status === 200) {
            messagingState.searchResults = data.data.messages || [];
            renderSearchResults(messagingState.searchResults, keyword);
        } else {
            document.getElementById('searchResults').innerHTML = '<div class="search-no-results">No messages found</div>';
        }
    } catch (error) {
        console.error('Error searching messages:', error);
        document.getElementById('searchResults').innerHTML = '<div class="search-no-results">Error searching messages</div>';
    }
}

function renderSearchResults(messages, keyword) {
    const container = document.getElementById('searchResults');
    const currentUserId = getCurrentUserId();

    if (messages.length === 0) {
        container.innerHTML = '<div class="search-no-results">No messages found matching "' + escapeHtml(keyword) + '"</div>';
        return;
    }

    container.innerHTML = `
        <div class="search-results-header">
            <span>Found ${messages.length} message(s)</span>
        </div>
        ${messages.map(msg => {
        // Compare as integers to handle string/number type mismatches
        const isOwn = parseInt(msg.sender_id) === parseInt(currentUserId);
        const highlightedContent = highlightKeyword(msg.content, keyword);

        return `
                <div class="search-result-item" onclick="scrollToMessage(${msg.id})">
                    <div class="search-result-sender">
                        ${isOwn ? 'You' : escapeHtml(msg.full_name || msg.username)}
                        <span class="search-result-time">${formatTime(msg.created_at)}</span>
                    </div>
                    <div class="search-result-content">${highlightedContent}</div>
                </div>
            `;
    }).join('')}
    `;
}

function highlightKeyword(text, keyword) {
    if (!text || !keyword) return escapeHtml(text);
    const regex = new RegExp(`(${escapeRegex(keyword)})`, 'gi');
    return escapeHtml(text).replace(regex, '<mark>$1</mark>');
}

function escapeRegex(str) {
    return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function scrollToMessage(messageId) {
    // Close search
    closeSearchMessages();

    // Find and scroll to message
    const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
    if (messageElement) {
        messageElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        messageElement.style.backgroundColor = '#fff3cd';
        setTimeout(() => {
            messageElement.style.backgroundColor = '';
        }, 2000);
    } else {
        // Message not loaded, reload messages and try again
        loadMessages(messagingState.currentConversationId).then(() => {
            setTimeout(() => scrollToMessage(messageId), 500);
        });
    }
}

// Archive conversation
async function archiveCurrentConversation() {
    const conversationId = messagingState.currentConversationId;
    if (!conversationId) return;

    if (!confirm('Archive this conversation? You can unarchive it later from the Archived tab.')) {
        return;
    }

    try {
        const response = await fetch('/api/messaging/archive', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({
                conversation_id: conversationId,
                archive: true
            })
        });

        const data = await response.json();

        if (data.status === 200) {
            // Clear current conversation
            messagingState.currentConversationId = null;
            document.getElementById('chatInputContainer').style.display = 'none';
            document.getElementById('chatActionsBar').style.display = 'none';
            document.getElementById('chatHeader').innerHTML = `
                <div class="chat-header-placeholder">
                    <i class="fas fa-comment-dots"></i>
                    <p>Select a conversation to start chatting</p>
                </div>
            `;

            // Reload conversations
            await loadConversations();
        } else {
            showError(data.message || 'Failed to archive conversation');
        }
    } catch (error) {
        console.error('Error archiving conversation:', error);
        showError('Failed to archive conversation');
    }
}

// Switch between active and archived tabs
function switchTab(tab) {
    messagingState.currentTab = tab;

    // Update tab buttons
    document.getElementById('tabActive').classList.toggle('active', tab === 'active');
    document.getElementById('tabArchived').classList.toggle('active', tab === 'archived');

    // Load appropriate conversations
    if (tab === 'archived') {
        loadArchivedConversations();
    } else {
        loadConversations();
    }
}

async function loadArchivedConversations() {
    try {
        const response = await fetch('/api/messaging/archived', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'include'
        });

        const data = await response.json();

        if (data.status === 200) {
            messagingState.conversations = data.data.conversations || [];
            renderConversations();
        } else {
            messagingState.conversations = [];
            renderConversations();
        }
    } catch (error) {
        console.error('Error loading archived conversations:', error);
        messagingState.conversations = [];
        renderConversations();
    }
}

async function createGroupChat(event) {
    event.preventDefault();

    const groupName = document.getElementById('groupName').value;
    const checkboxes = document.querySelectorAll('.member-checkbox input:checked');
    const memberIds = Array.from(checkboxes).map(cb => parseInt(cb.value));

    if (memberIds.length === 0) {
        showError('Sila pilih sekurang-kurangnya seorang ahli');
        return;
    }

    try {
        const response = await fetch('/api/messaging/group', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({
                name: groupName,
                member_ids: memberIds
            })
        });

        const data = await response.json();

        if (data.status === 200) {
            closeCreateGroupModal();
            await loadConversations();
            await selectConversation(data.data.conversation_id);
        } else {
            showError(data.message || 'Gagal mencipta kumpulan');
        }
    } catch (error) {
        console.error('Error creating group:', error);
        showError('Gagal mencipta kumpulan. Sila cuba lagi.');
    }
}

// Conversation type selection modal
function openCreateConversationModal() {
    document.getElementById('createConversationModal').classList.add('active');
}

function closeCreateConversationModal() {
    document.getElementById('createConversationModal').classList.remove('active');
}

// DM creation modal
function openCreateDMModal() {
    try {
        console.log('[DM Modal] Opening DM modal...');
        closeCreateConversationModal();

        const dmModal = document.getElementById('createDMModal');
        if (!dmModal) {
            console.error('[DM Modal] createDMModal element not found');
            alert('Error: Could not open DM modal. Please refresh the page.');
            return;
        }

        dmModal.classList.add('active');

        const searchInput = document.getElementById('dmSearchUsers');
        if (searchInput) {
            searchInput.value = '';
        } else {
            console.warn('[DM Modal] dmSearchUsers input not found');
        }

        console.log('[DM Modal] Loading users...');
        loadDMUsers();
    } catch (error) {
        console.error('[DM Modal] Error opening DM modal:', error);
        alert('Error opening message dialog. Please try again or refresh the page.');
    }
}

function closeCreateDMModal() {
    document.getElementById('createDMModal').classList.remove('active');
    document.getElementById('dmSearchUsers').value = '';
    const dmUsersList = document.getElementById('dmUsersList');
    if (dmUsersList) {
        dmUsersList.innerHTML = '';
    }
}

// Load users for DM creation (following users + search)
async function loadDMUsers(search = '') {
    const container = document.getElementById('dmUsersList');
    if (!container) {
        console.error('[DM Users] dmUsersList container not found');
        return;
    }

    console.log('[DM Users] Loading users, search term:', search || '(none)');
    container.innerHTML = '<div class="loading" style="text-align: center; padding: 20px;">Loading users...</div>';

    try {
        // Get following users
        console.log('[DM Users] Fetching following users...');
        const followingResponse = await fetch('/api/profile/me/following', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'include'
        });

        console.log('[DM Users] Following response status:', followingResponse.status);

        let followingUsers = [];
        if (followingResponse.ok) {
            const followingData = await followingResponse.json();
            console.log('[DM Users] Following data:', followingData);
            if (followingData.status === 200 && followingData.data && followingData.data.users) {
                followingUsers = followingData.data.users.map(u => ({ ...u, is_following: true }));
                console.log('[DM Users] Found', followingUsers.length, 'following users');
            } else {
                console.warn('[DM Users] Unexpected following data structure:', followingData);
            }
        } else {
            console.warn('[DM Users] Following request failed:', followingResponse.status, followingResponse.statusText);
        }

        // If search is provided, also search all users
        let searchUsers = [];
        if (search) {
            console.log('[DM Users] Searching for users with term:', search);
            const searchResponse = await fetch(`/api/messaging/available-users?search=${encodeURIComponent(search)}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'include'
            });

            console.log('[DM Users] Search response status:', searchResponse.status);

            if (searchResponse.ok) {
                const searchData = await searchResponse.json();
                console.log('[DM Users] Search data:', searchData);
                if (searchData.status === 200 && searchData.data && searchData.data.users) {
                    searchUsers = searchData.data.users.map(u => ({ ...u, is_following: false }));
                    console.log('[DM Users] Found', searchUsers.length, 'search results');
                } else {
                    console.warn('[DM Users] Unexpected search data structure:', searchData);
                }
            } else {
                console.warn('[DM Users] Search request failed:', searchResponse.status, searchResponse.statusText);
            }
        }

        // Combine and deduplicate
        const usersMap = new Map();
        [...followingUsers, ...searchUsers].forEach(user => {
            if (!usersMap.has(user.id)) {
                usersMap.set(user.id, user);
            } else {
                // Prefer following status if exists
                if (user.is_following) {
                    usersMap.set(user.id, user);
                }
            }
        });

        const users = Array.from(usersMap.values());
        console.log('[DM Users] Total unique users:', users.length);

        if (users.length === 0) {
            console.log('[DM Users] No users found');
            container.innerHTML = '<div style="text-align: center; padding: 20px; color: #999;">No users found. Try searching for a user by name or username.</div>';
            return;
        }

        // Group by following/other
        const following = users.filter(u => u.is_following);
        const others = users.filter(u => !u.is_following);

        let html = '';

        if (following.length > 0) {
            html += '<div class="members-section-header" style="font-weight: 600; color: #6b7280; font-size: 0.875rem; padding: 0.75rem 1rem; text-transform: uppercase; letter-spacing: 0.05em;">Following</div>';
            html += following.map(user => createDMUserItem(user)).join('');
        }

        if (others.length > 0) {
            if (following.length > 0) {
                html += '<div class="members-section-header" style="font-weight: 600; color: #6b7280; font-size: 0.875rem; padding: 0.75rem 1rem; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.5rem;">Other Users</div>';
            }
            html += others.map(user => createDMUserItem(user)).join('');
        }

        container.innerHTML = html;
        console.log('[DM Users] Successfully rendered', users.length, 'users');
    } catch (error) {
        console.error('[DM Users] Error loading DM users:', error);
        container.innerHTML = '<div style="text-align: center; padding: 20px; color: #ef4444;">Error loading users. Please try again or refresh the page.<br><small style="color: #999; margin-top: 8px; display: block;">Error: ' + error.message + '</small></div>';
    }
}

// Create DM user item HTML
function createDMUserItem(user) {
    const avatarUrl = user.avatar_url ? (user.avatar_url.startsWith('/') ? user.avatar_url : '/' + user.avatar_url) : null;
    const initials = (user.full_name || user.username || 'U').charAt(0).toUpperCase();
    const fullName = escapeHtml(user.full_name || user.username);
    const username = escapeHtml(user.username);

    return `
        <div class="dm-user-item" onclick="createDirectConversation(${user.id})" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; cursor: pointer; border-radius: 0.5rem; transition: background 0.2s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
            <div class="member-avatar" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #1877f2 0%, #42a5f5 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; flex-shrink: 0; overflow: hidden;">
                ${avatarUrl ? `<img src="${escapeHtml(avatarUrl)}" alt="${fullName}" style="width: 100%; height: 100%; object-fit: cover;">` : initials}
            </div>
            <div style="flex: 1; min-width: 0;">
                <div style="font-weight: 500; color: #1f2937; font-size: 0.95rem;">${fullName}</div>
                <div style="font-size: 0.85rem; color: #6b7280;">@${username}</div>
            </div>
            ${user.is_online ? '<div style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; flex-shrink: 0;"></div>' : ''}
        </div>
    `;
}

// Create direct conversation
async function createDirectConversation(userId) {
    try {
        const response = await fetch('/api/messaging/conversation/direct', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({ user_id: userId })
        });

        const data = await response.json();

        if (data.status === 200) {
            closeCreateDMModal();
            await loadConversations();
            if (data.data && data.data.conversation_id) {
                await selectConversation(data.data.conversation_id);
            }
        } else {
            alert(data.message || 'Failed to create conversation');
        }
    } catch (error) {
        console.error('Error creating direct conversation:', error);
        alert('Failed to create conversation');
    }
}

// Group chat modal (existing)
function openCreateGroupModal() {
    closeCreateConversationModal();
    document.getElementById('createGroupModal').classList.add('active');
    document.getElementById('searchMembers').value = '';
    loadAvailableMembers();
}

function closeCreateGroupModal() {
    document.getElementById('createGroupModal').classList.remove('active');
    document.getElementById('createGroupForm').reset();
    document.getElementById('selectedMembers').innerHTML = '';
}

async function loadAvailableMembers(search = '') {
    const container = document.getElementById('membersSelector');
    container.innerHTML = '<div class="loading" style="text-align: center; padding: 20px;">Loading users...</div>';

    try {
        const url = `/api/messaging/available-users${search ? '?search=' + encodeURIComponent(search) : ''}`;
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'include'
        });

        const data = await response.json();

        if (data.status === 200 && data.data && data.data.users) {
            const users = data.data.users;

            if (users.length === 0) {
                container.innerHTML = '<div style="text-align: center; padding: 20px; color: #999;">No users found</div>';
                return;
            }

            // Group users by recent/other
            const recentUsers = users.filter(u => u.is_recent);
            const otherUsers = users.filter(u => !u.is_recent);

            let html = '';

            if (recentUsers.length > 0) {
                html += '<div class="members-section-header">Recent Chats</div>';
                html += recentUsers.map(user => `
                    <div class="member-checkbox" onclick="toggleMemberCheckbox(${user.id})">
                        <input type="checkbox" id="member_${user.id}" value="${user.id}" onchange="updateSelectedMembers()">
                        <div class="member-avatar">
                            ${user.avatar_url ?
                        `<img src="${escapeHtml(user.avatar_url)}" alt="${escapeHtml(user.full_name || user.username)}">` :
                        (user.username ? user.username.charAt(0).toUpperCase() : '?')
                    }
                        </div>
                        <div class="member-info">
                            <div class="member-name">${escapeHtml(user.full_name || user.username)}</div>
                            ${user.is_online ? '<div class="member-status">Online</div>' : ''}
                        </div>
                    </div>
                `).join('');
            }

            if (otherUsers.length > 0) {
                if (recentUsers.length > 0) {
                    html += '<div class="members-section-header">All Users</div>';
                }
                html += otherUsers.map(user => `
                    <div class="member-checkbox" onclick="toggleMemberCheckbox(${user.id})">
                        <input type="checkbox" id="member_${user.id}" value="${user.id}" onchange="updateSelectedMembers()">
                        <div class="member-avatar">
                            ${user.avatar_url ?
                        `<img src="${escapeHtml(user.avatar_url)}" alt="${escapeHtml(user.full_name || user.username)}">` :
                        (user.username ? user.username.charAt(0).toUpperCase() : '?')
                    }
                        </div>
                        <div class="member-info">
                            <div class="member-name">${escapeHtml(user.full_name || user.username)}</div>
                            ${user.is_online ? '<div class="member-status">Online</div>' : ''}
                        </div>
                    </div>
                `).join('');
            }

            container.innerHTML = html;
        } else {
            container.innerHTML = '<div style="text-align: center; padding: 20px; color: #999;">Failed to load users</div>';
        }
    } catch (error) {
        console.error('Error loading available members:', error);
        container.innerHTML = '<div style="text-align: center; padding: 20px; color: #999;">Error loading users</div>';
    }
}

function toggleMemberCheckbox(userId) {
    const checkbox = document.getElementById(`member_${userId}`);
    if (checkbox) {
        checkbox.checked = !checkbox.checked;
        updateSelectedMembers();
    }
}

function updateSelectedMembers() {
    const checkboxes = document.querySelectorAll('.member-checkbox input:checked');
    const selectedContainer = document.getElementById('selectedMembers');

    if (checkboxes.length === 0) {
        selectedContainer.innerHTML = '';
        return;
    }

    const selected = Array.from(checkboxes).map(cb => {
        const userId = parseInt(cb.value);
        const label = cb.closest('.member-checkbox').querySelector('div[style*="font-weight: 600"]');
        return {
            id: userId,
            name: label ? label.textContent : 'User'
        };
    });

    selectedContainer.innerHTML = `
        <div style="margin-top: 0.75rem; padding: 0.75rem; background: var(--panel-muted); border-radius: 0.5rem; border: 1px solid var(--border);">
            <strong style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Selected (${selected.length})</strong>
            <div style="margin-top: 0.5rem; display: flex; flex-wrap: wrap; gap: 0.5rem;">
                ${selected.map(s => `
                    <span class="member-tag" style="display: inline-flex; align-items: center; gap: 0.4rem; background: rgba(36, 84, 255, 0.08); color: var(--primary); padding: 0.35rem 0.85rem; border-radius: 999px; font-size: 0.85rem;">
                        ${escapeHtml(s.name)}
                    </span>
                `).join('')}
            </div>
        </div>
    `;
}

// Load conversation details from cached data (instant)
function loadConversationDetailsFromCache(conversation) {
    const currentUserId = getCurrentUserId();
    const isGroupCreator = conversation.type === 'group' && conversation.created_by === currentUserId;

    const header = document.getElementById('chatHeader');
    header.innerHTML = `
        <div class="chat-info">
            <div class="chat-info-avatar">
                ${conversation.other_avatar ?
            `<img src="${escapeHtml(conversation.other_avatar)}" alt="${escapeHtml(conversation.other_username || '')}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                     <div style="display: none; width: 100%; height: 100%; background: linear-gradient(135deg, #2454FF 0%, #4B7BFF 100%); border-radius: 50%; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 1.2rem;">
                        ${conversation.other_username ? escapeHtml(conversation.other_username.charAt(0).toUpperCase()) : '?'}
                     </div>` :
            conversation.type === 'group' ?
                '<i class="fas fa-users"></i>' :
                `<div style="width: 100%; height: 100%; background: linear-gradient(135deg, #2454FF 0%, #4B7BFF 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 1.2rem;">
                            ${conversation.other_username ? escapeHtml(conversation.other_username.charAt(0).toUpperCase()) : '?'}
                         </div>`
        }
            </div>
            <div class="chat-info-details">
                <h3>${conversation.type === 'group' ? conversation.name : conversation.other_full_name || conversation.other_username}</h3>
                <div class="chat-info-status">
                    ${conversation.type === 'direct' && conversation.is_online ?
            '<span class="online-indicator"></span><span>Online</span>' :
            conversation.type === 'group' ?
                `<span>${conversation.member_count || 0} members</span>` :
                '<span>Offline</span>'
        }
                </div>
            </div>
        </div>
        <div class="chat-header-actions">
            <div class="conversation-options-container">
                <button class="btn-icon" onclick="event.stopPropagation(); toggleConversationOptions(${conversation.id})">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div id="conversationOptions_${conversation.id}" class="conversation-options-menu" style="display: none;">
                    <button class="conversation-option-item" onclick="event.stopPropagation(); archiveConversation(${conversation.id})">
                        <i class="fas fa-archive"></i> Archive
                    </button>
                    ${isGroupCreator ? `
                        <button class="conversation-option-item" onclick="event.stopPropagation(); confirmClearAllMessages(${conversation.id})">
                            <i class="fas fa-broom"></i> Clear All Messages
                        </button>
                        <button class="conversation-option-item" onclick="event.stopPropagation(); confirmRenameGroup(${conversation.id}, '${escapeHtml(conversation.name)}')">
                            <i class="fas fa-edit"></i> Rename Group
                        </button>
                    ` : ''}
                    ${conversation.type === 'group' ? `
                        <button class="conversation-option-item" onclick="event.stopPropagation(); openInfoSidebar()">
                            <i class="fas fa-info-circle"></i> Group Info
                        </button>
                    ` : ''}
                    <button class="conversation-option-item delete-option" onclick="event.stopPropagation(); confirmDeleteConversation(${conversation.id})">
                        <i class="fas fa-trash"></i> ${conversation.type === 'group' ? 'Delete Group' : 'Delete Conversation'}
                    </button>
                </div>
            </div>
        </div>
    `;
}

function loadConversationDetails(conversationId) {
    const conversation = messagingState.conversations.find(c => c.id === conversationId);
    if (!conversation) {
        // If not in cache, will be updated after messages load
        return;
    }

    // Use cached version for instant display
    loadConversationDetailsFromCache(conversation);
}

// Load group info
function loadGroupInfo(conversation) {
    const content = document.getElementById('infoContent');

    // Handle members - could be array or string
    let members = conversation.members;
    if (typeof members === 'string') {
        try {
            members = JSON.parse(members);
        } catch (e) {
            console.error('Error parsing members:', e);
            members = [];
        }
    }

    // Ensure members is an array
    if (!Array.isArray(members)) {
        members = [];
    }

    const currentUserId = getCurrentUserId();
    const isGroupCreator = conversation.created_by === currentUserId;

    content.innerHTML = `
        <div>
            ${isGroupCreator ? `
                <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #e0e0e0;">
                    <h4 style="margin-bottom: 10px;">Group Settings</h4>
                    <button class="btn-group-action" onclick="renameGroup(${conversation.id})" style="width: 100%; margin-bottom: 8px;">
                        <i class="fas fa-edit"></i> Rename Group
                    </button>
                    <button class="btn-group-action delete-action" onclick="confirmDeleteConversation(${conversation.id})" style="width: 100%;">
                        <i class="fas fa-trash"></i> Delete Group
                    </button>
                </div>
            ` : ''}
            <h4>Group Members (${members.length})</h4>
            <div class="member-list">
                ${members.length > 0 ? members.map(member => `
                    <div class="member-item" style="display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #f0f0f0;">
                        <div class="conversation-avatar">
                            ${member.avatar_url ?
            `<img src="${member.avatar_url}" alt="${member.username || ''}">` :
            (member.username ? member.username.charAt(0).toUpperCase() : '?')
        }
                        </div>
                        <div style="flex: 1; margin-left: 10px;">
                            <div style="font-weight: 600;">${escapeHtml(member.full_name || member.username || 'Unknown')}</div>
                            ${member.is_online ? '<span style="font-size: 12px; color: #4CAF50;">Online</span>' : ''}
                        </div>
                        ${isGroupCreator && member.id !== currentUserId ? `
                            <button class="btn-remove-member" onclick="removeGroupMember(${conversation.id}, ${member.id})" title="Remove member">
                                <i class="fas fa-user-minus"></i>
                            </button>
                        ` : ''}
                    </div>
                `).join('') : '<p>No members found</p>'}
            </div>
        </div>
    `;
}

async function openInfoSidebar() {
    // If this is a group chat and members aren't loaded, fetch them
    const conversation = messagingState.conversations.find(c => c.id === messagingState.currentConversationId);
    if (conversation && conversation.type === 'group' && (!conversation.members || conversation.members.length === 0)) {
        try {
            const response = await fetch(`/api/messaging/conversation/${messagingState.currentConversationId}/messages?page=1&load_members=true`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'include'
            });

            const data = await response.json();
            if (data.status === 200 && data.data && data.data.conversation) {
                conversation.members = data.data.conversation.members || [];
                loadGroupInfo(data.data.conversation);
            }
        } catch (error) {
            console.error('Error loading group members:', error);
        }
    }

    document.getElementById('infoSidebar').style.display = 'block';
}

function closeInfoSidebar() {
    document.getElementById('infoSidebar').style.display = 'none';
}

// WebSocket connection management - Using Laravel Echo (Reverb)
function connectWebSocket() {
    // Get user ID from sessionStorage or fetch from API
    let userId = getCurrentUserId();

    if (!userId) {
        // Try to get from sessionStorage directly
        userId = sessionStorage.getItem('userId');
        if (userId) {
            userId = parseInt(userId);
        }
    }

    if (!userId) {
        console.error('Cannot connect WebSocket: No user ID. Please log in again.');
        // Fallback to polling
        startPollingFallback();
        return;
    }

    // Check if Echo is available - handle both constructor and instance
    const echoAvailable = typeof window.Echo !== 'undefined' && (
        // Echo is an instance (already initialized)
        (window.Echo.connector || window.Echo.private || window.Echo.options) ||
        // Echo is a constructor function (will be initialized by messaging.blade.php)
        typeof window.Echo === 'function'
    );

    if (echoAvailable) {
        // If Echo is a constructor, wait for it to be initialized
        if (typeof window.Echo === 'function' && !window.Echo.connector && !window.Echo.private) {
            console.log('Echo constructor found, waiting for initialization...');
            // Wait for echoReady event or check periodically
            let attempts = 0;
            const maxAttempts = 50;
            const checkInterval = setInterval(() => {
                attempts++;
                if ((window.Echo.connector || window.Echo.private || window.Echo.options) || attempts >= maxAttempts) {
                    clearInterval(checkInterval);
                    if (window.Echo.connector || window.Echo.private || window.Echo.options) {
                        console.log('Echo instance ready, connecting...');
                        initializeEchoConnection();
                    } else {
                        console.warn('Echo did not initialize after waiting, using polling fallback');
                        messagingState.isWebSocketConnected = false;
                        startPollingFallback();
                    }
                }
            }, 100);
            return;
        }

        // Echo is already an instance, connect immediately
        console.log('Using Laravel Echo for messaging WebSocket');
        initializeEchoConnection();
    } else {
        // Echo not available, use polling fallback
        console.log('Laravel Echo not available, using polling fallback');
        messagingState.isWebSocketConnected = false;
        startPollingFallback();
    }
}

function initializeEchoConnection() {
    try {
        // Join current conversation if any (will be set up when conversation is selected)
        if (messagingState.currentConversationId) {
            joinConversation(messagingState.currentConversationId);
        }

        // Update online status
        updateOnlineStatus(true);

        // Mark as connected
        messagingState.echoConnected = true;
        messagingState.isWebSocketConnected = true;
        stopPollingFallback(); // Stop polling since WebSocket is connected
        console.log('Laravel Echo connected for messaging');

    } catch (error) {
        console.error('Error setting up Laravel Echo:', error);
        messagingState.isWebSocketConnected = false;
        startPollingFallback();
    }
}
updateOnlineStatus(true);

// Mark as connected
messagingState.echoConnected = true;
messagingState.isWebSocketConnected = true;
stopPollingFallback(); // Stop polling since WebSocket is connected
console.log('Laravel Echo connected for messaging');
            
        } catch (error) {
    console.error('Error setting up Laravel Echo:', error);
    messagingState.isWebSocketConnected = false;
    startPollingFallback();
}
    } else {
    // Echo not available, use polling fallback
    console.log('Laravel Echo not available, using polling fallback');
    messagingState.isWebSocketConnected = false;
    startPollingFallback();
}
}

// Handle WebSocket messages
function handleWebSocketMessage(data) {
    switch (data.type) {
        case 'auth_success':
            console.log('WebSocket authenticated');
            break;

        case 'new_message':
            // New message received - add directly to messages array
            if (data.conversation_id === messagingState.currentConversationId && data.message) {
                const newMessage = data.message;

                // Check if message already exists (avoid duplicates)
                const messageExists = messagingState.messages.some(m => m.id === newMessage.id);
                if (!messageExists) {
                    console.log('Adding new message:', newMessage);
                    // Add new message to the array
                    messagingState.messages.push(newMessage);
                    // Sort by created_at to maintain order
                    messagingState.messages.sort((a, b) => {
                        return new Date(a.created_at) - new Date(b.created_at);
                    });
                    // Update cache with new message
                    updateMessageInCache(data.conversation_id, newMessage);
                    renderMessages();
                    // Auto-scroll to bottom when new message arrives
                    setTimeout(() => scrollToBottom(true), 100);
                } else {
                    console.log('Message already exists:', newMessage.id);
                }
            } else if (data.message) {
                // Message for a different conversation - still update cache
                updateMessageInCache(data.conversation_id, data.message);
            }
            // Always reload conversations to update last message preview
            loadConversations();
            break;

        case 'user_typing':
            // Show typing indicator
            if (data.conversation_id === messagingState.currentConversationId) {
                showTypingIndicator(data.user_id);
            }
            break;

        case 'user_stopped_typing':
            // Hide typing indicator
            if (data.conversation_id === messagingState.currentConversationId) {
                hideTypingIndicator(data.user_id);
            }
            break;

        case 'online_status_update':
            // Update online status in conversations
            updateConversationOnlineStatus(data.user_id, data.is_online);
            break;
            // Update online status in conversations
            updateConversationOnlineStatus(data.user_id, data.is_online);
            break;

        case 'error':
            console.error('WebSocket error:', data.message);
            break;
    }
}

// Join a conversation via WebSocket/Echo
function joinConversation(conversationId) {
    if (!conversationId) return;

    // Use Laravel Echo if available
    if (typeof window.Echo !== 'undefined' && (window.Echo.connector || window.Echo.private)) {
        try {
            // Leave previous conversation channel if exists
            if (messagingState.echoChannels && messagingState.currentConversationId && messagingState.currentConversationId !== conversationId) {
                const oldChannel = messagingState.echoChannels[messagingState.currentConversationId];
                if (oldChannel) {
                    window.Echo.leave(`conversation.${messagingState.currentConversationId}`);
                    delete messagingState.echoChannels[messagingState.currentConversationId];
                }
            }

            // Join new conversation channel
            // Note: Initial auth may show 403 errors in console, but Echo will retry and succeed
            const channel = window.Echo.private(`conversation.${conversationId}`);

            // Handle auth errors gracefully - Echo will retry automatically
            channel.error((error) => {
                if (error.status !== 403) {
                    console.error('Channel subscription error:', error);
                }
                // 403 errors are expected on initial attempts and will be retried
            });

            channel.listen('.new_message', (data) => {
                console.log('New message via Echo:', data);
                // Mark WebSocket as connected when we receive messages
                messagingState.isWebSocketConnected = true;
                stopPollingFallback();
                handleWebSocketMessage({
                    type: 'new_message',
                    conversation_id: data.conversation_id || data.conversationId || conversationId,
                    message: data.message
                });
            });

            channel.listen('.user_typing', (data) => {
                if (data.conversation_id === conversationId) {
                    handleWebSocketMessage({
                        type: 'user_typing',
                        conversation_id: conversationId,
                        user_id: data.user_id
                    });
                }
            });

            channel.listen('.user_stopped_typing', (data) => {
                if (data.conversation_id === conversationId) {
                    handleWebSocketMessage({
                        type: 'user_stopped_typing',
                        conversation_id: conversationId,
                        user_id: data.user_id
                    });
                }
            });

            channel.listen('.online_status_update', (data) => {
                handleWebSocketMessage({
                    type: 'online_status_update',
                    user_id: data.user_id,
                    is_online: data.is_online
                });
            });

            // Store channel reference
            messagingState.echoChannels = messagingState.echoChannels || {};
            messagingState.echoChannels[conversationId] = channel;

            console.log('Joined conversation channel via Echo:', conversationId);
        } catch (error) {
            console.error('Error joining conversation via Echo:', error);
        }
    } else if (messagingState.ws && messagingState.ws.readyState === WebSocket.OPEN) {
        // Fallback to old WebSocket if Echo not available
        messagingState.ws.send(JSON.stringify({
            type: 'join_conversation',
            conversation_id: conversationId
        }));
    }
}

// Leave a conversation via WebSocket
function leaveConversation(conversationId) {
    if (messagingState.ws && messagingState.ws.readyState === WebSocket.OPEN) {
        messagingState.ws.send(JSON.stringify({
            type: 'leave_conversation',
            conversation_id: conversationId
        }));
    }
}

// Typing indicator
let typingTimeoutId = null;
function handleTyping() {
    if (!messagingState.currentConversationId) return;

    // Send typing indicator via WebSocket
    if (messagingState.ws && messagingState.ws.readyState === WebSocket.OPEN) {
        if (!messagingState.isTyping) {
            messagingState.isTyping = true;
            messagingState.ws.send(JSON.stringify({
                type: 'typing',
                conversation_id: messagingState.currentConversationId
            }));
        }

        // Clear existing timeout
        if (typingTimeoutId) {
            clearTimeout(typingTimeoutId);
        }

        // Stop typing after 3 seconds of inactivity
        typingTimeoutId = setTimeout(() => {
            messagingState.isTyping = false;
            if (messagingState.ws && messagingState.ws.readyState === WebSocket.OPEN) {
                messagingState.ws.send(JSON.stringify({
                    type: 'stop_typing',
                    conversation_id: messagingState.currentConversationId
                }));
            }
        }, 3000);
    }
}

// Show typing indicator for a user
function showTypingIndicator(userId) {
    const currentUserId = getCurrentUserId();
    if (userId === currentUserId) return; // Don't show own typing

    messagingState.typingUsers.add(userId);

    // Get user name from participants or conversations
    if (!messagingState.typingUserNames.has(userId)) {
        const userName = getUserNameForTyping(userId);
        if (userName) {
            messagingState.typingUserNames.set(userId, userName);
        }
    }

    updateTypingIndicatorDisplay();
}

// Hide typing indicator for a user
function hideTypingIndicator(userId) {
    messagingState.typingUsers.delete(userId);
    messagingState.typingUserNames.delete(userId);
    updateTypingIndicatorDisplay();
}

// Get user name for typing indicator
function getUserNameForTyping(userId) {
    // First try to get from participants
    if (messagingState.participants && messagingState.participants.length > 0) {
        const participant = messagingState.participants.find(p => parseInt(p.id) === parseInt(userId));
        if (participant) {
            return participant.full_name || participant.username || 'Someone';
        }
    }

    // Try to get from current conversation
    const conversation = messagingState.conversations.find(c => c.id === messagingState.currentConversationId);
    if (conversation) {
        if (conversation.type === 'group') {
            // For groups, check if we have members in conversation data
            if (conversation.members && Array.isArray(conversation.members)) {
                const member = conversation.members.find(m => parseInt(m.id) === parseInt(userId));
                if (member) {
                    return member.full_name || member.username || 'Someone';
                }
            }
            return 'Someone';
        } else {
            // For direct messages, check if it's the other user
            if (parseInt(conversation.other_user_id) === parseInt(userId) || parseInt(conversation.id) === parseInt(userId)) {
                return conversation.other_full_name || conversation.other_username || 'Someone';
            }
        }
    }

    // Try to get from messages
    const message = messagingState.messages.find(m => parseInt(m.sender_id) === parseInt(userId));
    if (message) {
        return message.full_name || message.username || 'Someone';
    }

    return 'Someone';
}

// Update typing indicator display
function updateTypingIndicatorDisplay() {
    const indicator = document.getElementById('typingIndicator');
    const typingUser = document.getElementById('typingUser');

    if (!indicator) return;

    if (messagingState.typingUsers.size > 0) {
        const typingUserIds = Array.from(messagingState.typingUsers);
        const typingNames = typingUserIds
            .map(id => messagingState.typingUserNames.get(id) || getUserNameForTyping(id))
            .filter(name => name && name !== 'Someone');

        if (typingUser) {
            if (typingNames.length === 0) {
                typingUser.textContent = 'Someone';
            } else if (typingNames.length === 1) {
                typingUser.textContent = typingNames[0];
            } else if (typingNames.length === 2) {
                typingUser.textContent = `${typingNames[0]} and ${typingNames[1]}`;
            } else {
                typingUser.textContent = `${typingNames[0]} and ${typingNames.length - 1} others`;
            }
        }
        indicator.style.display = 'block';
    } else {
        indicator.style.display = 'none';
    }
}

// Update online status in conversation list
function updateConversationOnlineStatus(userId, isOnline) {
    // Update the conversation in the list
    const conversation = messagingState.conversations.find(c =>
        c.type === 'direct' && (c.other_user_id === userId || c.id === userId)
    );

    if (conversation) {
        conversation.is_online = isOnline;
        renderConversations();
    }
}

// Fallback to polling if WebSocket fails
function startPollingFallback() {
    // Clear any existing polling interval to prevent duplicates
    if (messagingState.pollingInterval) {
        clearInterval(messagingState.pollingInterval);
        messagingState.pollingInterval = null;
    }

    // Don't start polling if WebSocket is already connected
    if (messagingState.isWebSocketConnected) {
        return;
    }

    console.warn('Using polling fallback for real-time updates');
    // Increase interval to 15 seconds to avoid rate limiting and reduce flickering
    messagingState.pollingInterval = setInterval(() => {
        // Only poll if WebSocket is still not connected
        if (!messagingState.isWebSocketConnected && messagingState.currentConversationId) {
            loadMessages(messagingState.currentConversationId, 1);
        }
        loadConversations();
    }, 15000); // 15 seconds to reduce flickering
}

// Stop polling fallback (called when WebSocket connects)
function stopPollingFallback() {
    if (messagingState.pollingInterval) {
        clearInterval(messagingState.pollingInterval);
        messagingState.pollingInterval = null;
        console.log('Stopped polling fallback - WebSocket connected');
    }
}

async function updateOnlineStatus(isOnline) {
    // Don't update if status hasn't changed
    if (messagingState.lastOnlineStatus === isOnline) {
        return;
    }

    // Clear any pending throttle
    if (messagingState.statusUpdateThrottle) {
        clearTimeout(messagingState.statusUpdateThrottle);
    }

    // Throttle status updates to max once per 5 seconds
    messagingState.statusUpdateThrottle = setTimeout(async () => {
        try {
            await fetch('/api/messaging/status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({ is_online: isOnline })
            });

            // Update last sent status
            messagingState.lastOnlineStatus = isOnline;

            // Also update via WebSocket if connected
            if (messagingState.ws && messagingState.ws.readyState === WebSocket.OPEN) {
                messagingState.ws.send(JSON.stringify({
                    type: 'online_status',
                    is_online: isOnline
                }));
            }
        } catch (error) {
            // Silently handle errors to avoid console spam
            // Only log if it's not a rate limit error
            if (!error.message || !error.message.includes('429')) {
                console.error('Error updating status:', error);
            }
        }
    }, 5000); // Wait 5 seconds before sending
}

// Initialize inactive timeout tracking
function initInactiveTimeout() {
    const INACTIVE_TIMEOUT = 5 * 60 * 1000; // 5 minutes in milliseconds

    // Reset activity time on user interaction (only track meaningful events, not every mouse move)
    const activityEvents = ['mousedown', 'keypress', 'touchstart', 'click'];
    activityEvents.forEach(event => {
        document.addEventListener(event, () => {
            messagingState.lastActivityTime = Date.now();
            // Reset inactive timeout
            if (messagingState.inactiveTimeout) {
                clearTimeout(messagingState.inactiveTimeout);
            }
            // Only update status if user was offline
            if (messagingState.lastOnlineStatus === false) {
                updateOnlineStatus(true);
            }
        }, { passive: true });
    });

    // Check for inactivity periodically
    setInterval(() => {
        const timeSinceActivity = Date.now() - messagingState.lastActivityTime;
        if (timeSinceActivity >= INACTIVE_TIMEOUT) {
            // User has been inactive for 5 minutes, set to offline
            updateOnlineStatus(false);
        }
    }, 60000); // Check every minute
}

// Track user activity
function trackUserActivity() {
    // Update activity time on page visibility change
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            // Page is hidden, set to offline after a delay
            if (messagingState.inactiveTimeout) {
                clearTimeout(messagingState.inactiveTimeout);
            }
            messagingState.inactiveTimeout = setTimeout(() => {
                updateOnlineStatus(false);
            }, 30000); // 30 seconds after page becomes hidden
        } else {
            // Page is visible, set to online
            if (messagingState.inactiveTimeout) {
                clearTimeout(messagingState.inactiveTimeout);
            }
            messagingState.lastActivityTime = Date.now();
            // Only update if currently offline
            if (messagingState.lastOnlineStatus === false) {
                updateOnlineStatus(true);
            }
        }
    });

    // Set initial online status (only once on page load)
    if (messagingState.lastOnlineStatus === null) {
        updateOnlineStatus(true);
    }
}

// Helper functions
function formatTime(dateString) {
    if (!dateString) {
        return '';
    }

    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) {
            return '';
        }

        const now = new Date();
        const diff = now - date;
        const seconds = Math.floor(diff / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);

        if (days > 7) {
            return date.toLocaleDateString('ms-MY');
        } else if (days > 0) {
            return `${days}h lalu`;
        } else if (hours > 0) {
            return `${hours}j lalu`;
        } else if (minutes > 0) {
            return `${minutes}m lalu`;
        } else {
            return 'Baru sahaja';
        }
    } catch (e) {
        return '';
    }
}

function getFileIcon(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    const icons = {
        'pdf': 'fa-file-pdf',
        'doc': 'fa-file-word',
        'docx': 'fa-file-word',
        'xls': 'fa-file-excel',
        'xlsx': 'fa-file-excel',
        'ppt': 'fa-file-powerpoint',
        'pptx': 'fa-file-powerpoint',
        'jpg': 'fa-file-image',
        'jpeg': 'fa-file-image',
        'png': 'fa-file-image',
        'gif': 'fa-file-image',
        'zip': 'fa-file-archive',
        'rar': 'fa-file-archive'
    };
    return icons[ext] || 'fa-file';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Format message content with URL detection and linking
function formatMessageContent(text) {
    if (!text) return '';

    // Escape HTML first
    let escaped = escapeHtml(text);

    // Convert URLs to clickable links
    const urlPattern = /(https?:\/\/[^\s]+)/gi;
    escaped = escaped.replace(urlPattern, '<a href="$1" target="_blank" rel="noopener noreferrer" style="color: #2454FF; text-decoration: underline;">$1</a>');

    return escaped;
}

// Cache for shared post data
const sharedPostCache = new Map();

// Fetch and render shared post preview
async function renderSharedPostPreview(msg, isOwn = false) {
    const postId = parseInt(msg.attachment_url);
    if (!postId) {
        // Fallback to link if post_id is invalid
        return `<div class="shared-post-preview"><a href="${escapeHtml(msg.attachment_name || '#')}" target="_blank">${escapeHtml(msg.content || 'Shared Post')}</a></div>`;
    }

    // Check cache first
    if (sharedPostCache.has(postId)) {
        return renderSharedPostCard(sharedPostCache.get(postId), msg, isOwn);
    }

    // Fetch post data
    try {
        const response = await fetch(`/api/forum/post?post_id=${postId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'include'
        });

        const data = await response.json();
        if (data.status === 200 && data.data.posts && data.data.posts.length > 0) {
            const post = data.data.posts[0];
            sharedPostCache.set(postId, post);
            return renderSharedPostCard(post, msg, isOwn);
        } else {
            // Post not found or access denied, show fallback
            return `<div class="shared-post-preview"><a href="${escapeHtml(msg.attachment_name || '/forum/post/' + postId)}" target="_blank">${escapeHtml(msg.content || 'Shared Post')}</a></div>`;
        }
    } catch (error) {
        console.error('Error fetching shared post:', error);
        // Fallback to link
        return `<div class="shared-post-preview"><a href="${escapeHtml(msg.attachment_name || '/forum/post/' + postId)}" target="_blank">${escapeHtml(msg.content || 'Shared Post')}</a></div>`;
    }
}

// Render shared post card (similar to forum main page)
function renderSharedPostCard(post, msg, isOwn = false) {
    const postUrl = `/forum/post/${post.id}`;
    const forumUrl = `/forum/${post.forum_id}`;
    const authorUrl = post.author_id ? `/profile/${post.author_id}` : '#';

    // Process content preview (first 120 chars for smaller container)
    let contentPreview = '';
    if (post.content) {
        const content = post.content.substring(0, 120);
        contentPreview = escapeHtml(content) + (post.content.length > 120 ? '...' : '');
    }

    // Get first image attachment for preview
    let imagePreview = '';
    if (post.attachments && Array.isArray(post.attachments) && post.attachments.length > 0) {
        const imageAtt = post.attachments.find(att => {
            const ext = (att.name || '').split('.').pop().toLowerCase();
            return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
        });
        if (imageAtt) {
            imagePreview = `<div class="shared-post-image" style="width: 100%; overflow: hidden; border-radius: 8px 8px 0 0; margin: -10px -10px 8px -10px;">
                <img src="${escapeHtml(imageAtt.url)}" alt="${escapeHtml(post.title)}" style="width: 100%; height: auto; display: block; object-fit: contain; max-height: none;" onerror="this.parentElement.style.display='none';">
            </div>`;
        }
    }

    // Poll options preview
    let pollPreview = '';
    if (post.post_type === 'poll' && post.poll_options && Array.isArray(post.poll_options)) {
        pollPreview = `<div class="shared-post-poll" style="margin-top: 8px; padding-top: 8px; border-top: 1px solid ${isOwn ? 'rgba(255, 255, 255, 0.3)' : '#e0e0e0'} !important;">
            <div style="font-weight: 600; margin-bottom: 6px; color: ${isOwn ? 'rgba(255, 255, 255, 0.9)' : '#666'} !important; font-size: 0.8em;">Poll Options:</div>
            ${post.poll_options.slice(0, 2).map((option, idx) => `
                <div style="padding: 4px; margin: 3px 0; background: ${isOwn ? 'rgba(255, 255, 255, 0.15)' : '#f5f5f5'} !important; border-radius: 4px; font-size: 0.75em;">
                    <span style="font-weight: 600; color: ${isOwn ? 'rgba(255, 255, 255, 0.95)' : '#ff4500'} !important;">${idx + 1}.</span>
                    <span style="color: ${isOwn ? 'rgba(255, 255, 255, 0.9)' : '#333'} !important;">${escapeHtml(option.text.length > 40 ? option.text.substring(0, 40) + '...' : option.text)}</span>
                    ${option.vote_count > 0 ? `<span style="margin-left: auto; color: ${isOwn ? 'rgba(255, 255, 255, 0.9)' : '#666'} !important; font-size: 0.7em;">${option.vote_count}</span>` : ''}
                </div>
            `).join('')}
            ${post.poll_options.length > 2 ? `<div style="font-size: 0.7em; color: ${isOwn ? 'rgba(255, 255, 255, 0.8)' : '#999'} !important; margin-top: 3px;">+${post.poll_options.length - 2} more</div>` : ''}
            ${post.total_poll_votes > 0 ? `<div style="font-size: 0.7em; color: ${isOwn ? 'rgba(255, 255, 255, 0.9)' : '#666'} !important; margin-top: 6px;">Total: ${post.total_poll_votes}</div>` : ''}
        </div>`;
    }

    return `
        <div class="shared-post-preview ${isOwn ? 'own-message-shared-post' : 'received-message-shared-post'}" 
             onclick="event.stopPropagation(); window.open('${postUrl}', '_blank')"
             onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'" 
             onmouseout="this.style.boxShadow='none'"
             style="border: 1px solid #e0e0e0 !important; border-radius: 8px !important; background: ${isOwn ? 'rgba(255, 255, 255, 0.25)' : '#f8f9fa'} !important; overflow: hidden !important; cursor: pointer !important; transition: box-shadow 0.2s !important; display: block !important; width: 100% !important; max-width: 350px !important; min-width: 220px !important; box-sizing: border-box !important; color: ${isOwn ? 'rgba(255, 255, 255, 0.95)' : '#333'} !important;">
            ${imagePreview}
            <div style="padding: 8px; box-sizing: border-box; color: ${isOwn ? 'rgba(255, 255, 255, 0.95)' : '#333'} !important;">
                <div style="display: flex; align-items: center; gap: 4px; margin-bottom: 2px; font-size: 0.75em; color: ${isOwn ? 'rgba(255, 255, 255, 0.9)' : '#666'} !important; flex-wrap: wrap;">
                    <span style="font-weight: 600; color: ${isOwn ? 'rgba(255, 255, 255, 0.95)' : '#ff4500'} !important; cursor: pointer;" onclick="event.stopPropagation(); window.open('${forumUrl}', '_blank')">${escapeHtml(post.forum_name || 'Forum')}</span>
                    <span style="color: ${isOwn ? 'rgba(255, 255, 255, 0.9)' : '#666'} !important;">•</span>
                    <span onclick="event.stopPropagation(); window.open('${authorUrl}', '_blank')" style="cursor: pointer; color: ${isOwn ? 'rgba(255, 255, 255, 0.9)' : '#666'} !important;">${escapeHtml(post.author_name || post.author_username || 'Unknown')}</span>
                    <span style="color: ${isOwn ? 'rgba(255, 255, 255, 0.9)' : '#666'} !important;">•</span>
                    <span style="color: ${isOwn ? 'rgba(255, 255, 255, 0.9)' : '#666'} !important;">${formatTime(post.created_at)}</span>
                </div>
                <div style="font-weight: 600; font-size: 0.95em; color: ${isOwn ? 'rgba(255, 255, 255, 0.95)' : '#333'} !important; margin-bottom: 2px; cursor: pointer; word-wrap: break-word; line-height: 1.3;" onclick="event.stopPropagation(); window.open('${postUrl}', '_blank')">
                    ${escapeHtml(post.title)}
                </div>
                ${contentPreview ? `<div style="color: ${isOwn ? 'rgba(255, 255, 255, 0.9)' : '#666'} !important; font-size: 0.8em; margin-bottom: 2px; line-height: 1.3; word-wrap: break-word; max-height: 60px; overflow: hidden;">${contentPreview}</div>` : ''}
                ${pollPreview}
                <div style="display: flex; align-items: center; gap: 12px; margin-top: 4px; padding-top: 4px; border-top: 1px solid ${isOwn ? 'rgba(255, 255, 255, 0.3)' : '#f0f0f0'} !important; font-size: 0.75em;">
                    <span onclick="event.stopPropagation(); togglePostReaction(${post.id}, '${postUrl}')" style="cursor: pointer; display: flex; align-items: center; gap: 4px; color: ${isOwn ? 'rgba(255, 255, 255, 0.9)' : '#666'} !important;">
                        <i class="${post.user_reacted ? 'fas' : 'far'} fa-heart" style="color: ${post.user_reacted ? '#ff4500' : (isOwn ? 'rgba(255, 255, 255, 0.9)' : '#666')} !important;"></i>
                        <span style="color: ${isOwn ? 'rgba(255, 255, 255, 0.9)' : '#666'} !important;">${post.reaction_count || 0}</span>
                    </span>
                    <span onclick="event.stopPropagation(); window.open('${postUrl}', '_blank')" style="cursor: pointer; display: flex; align-items: center; gap: 4px; color: ${isOwn ? 'rgba(255, 255, 255, 0.9)' : '#666'} !important;">
                        <i class="far fa-comment" style="color: ${isOwn ? 'rgba(255, 255, 255, 0.9)' : '#666'} !important;"></i>
                        <span style="color: ${isOwn ? 'rgba(255, 255, 255, 0.9)' : '#666'} !important;">${post.reply_count || 0}</span>
                    </span>
                </div>
            </div>
        </div>
    `;
}

// Toggle post reaction from shared post preview
window.togglePostReaction = async function (postId, postUrl) {
    try {
        const response = await fetch('/api/forum/react', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            credentials: 'include',
            body: JSON.stringify({
                target_type: 'post',
                target_id: postId
            })
        });

        const data = await response.json();
        if (data.status === 200) {
            // Update cache
            if (sharedPostCache.has(postId)) {
                const post = sharedPostCache.get(postId);
                post.user_reacted = data.data.is_reacted;
                post.reaction_count = data.data.reaction_count;
                sharedPostCache.set(postId, post);
            }
            // Re-render messages to update the preview
            renderMessages();
        }
    } catch (error) {
        console.error('Error toggling reaction:', error);
    }
};

// Render link preview card
function renderLinkPreview(msg) {
    try {
        const preview = JSON.parse(msg.attachment_url);
        const url = preview.url || msg.attachment_name || '';

        return `
            <div class="link-preview">
                ${preview.image ? `
                    <div class="link-preview-image">
                        <img src="${escapeHtml(preview.image)}" alt="${escapeHtml(preview.title || '')}" onerror="this.style.display='none'">
                    </div>
                ` : ''}
                <div class="link-preview-content">
                    <div class="link-preview-site">
                        ${preview.favicon ? `<img src="${escapeHtml(preview.favicon)}" alt="" class="link-favicon" onerror="this.style.display='none'">` : ''}
                        <span>${escapeHtml(preview.site_name || new URL(url).hostname || '')}</span>
                    </div>
                    <a href="${escapeHtml(url)}" target="_blank" rel="noopener noreferrer" class="link-preview-title">
                        ${escapeHtml(preview.title || url)}
                    </a>
                    ${preview.description ? `
                        <div class="link-preview-description">
                            ${escapeHtml(preview.description)}
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    } catch (e) {
        // Fallback if preview data is invalid
        const url = msg.attachment_name || '';
        return `
            <div class="link-preview">
                <div class="link-preview-content">
                    <a href="${escapeHtml(url)}" target="_blank" rel="noopener noreferrer" class="link-preview-title">
                        ${escapeHtml(url)}
                    </a>
                </div>
            </div>
        `;
    }
}

function showError(message, showAlert = true) {
    // Log error to console
    console.error('Messaging Error:', message);

    // Only show alert if explicitly requested (for critical errors)
    // Don't show alerts for empty data or non-critical issues
    if (showAlert && !message.toLowerCase().includes('empty') && !message.toLowerCase().includes('tiada')) {
        alert(message);
    }
}

function getCurrentUserId() {
    // Get from sessionStorage
    const userId = sessionStorage.getItem('userId');
    return userId ? parseInt(userId) : null;
}

// Message context menu functions
let currentContextMenuMessageId = null;

function showMessageContextMenu(event, messageId, isOwn) {
    event.preventDefault();
    event.stopPropagation();

    // Close any existing context menu
    closeMessageContextMenu();

    currentContextMenuMessageId = messageId;

    // Get message data
    const message = messagingState.messages.find(m => m.id === messageId);
    if (!message) return;

    // Create context menu
    const menu = document.createElement('div');
    menu.id = 'messageContextMenu';
    menu.className = 'message-context-menu';

    // Reaction emojis row
    const reactions = ['👍', '❤️', '😂', '😮', '😥', '🙏'];
    const reactionsHtml = `
        <div class="message-reactions">
            ${reactions.map(emoji => `
                <button class="reaction-btn" onclick="event.stopPropagation(); addReaction(${messageId}, '${emoji}'); closeMessageContextMenu();">
                    ${emoji}
                </button>
            `).join('')}
        </div>
    `;

    // Menu options
    const menuOptions = [
        { icon: 'fa-reply', text: 'Reply', action: `replyToMessage(${messageId})` },
        { icon: 'fa-copy', text: 'Copy', action: `copyMessage(${messageId})` },
        { icon: 'fa-user', text: 'Reply privately', action: `replyPrivately(${messageId})`, condition: !isOwn },
        { icon: 'fa-share', text: 'Forward', action: `forwardMessage(${messageId})` },
        { icon: 'fa-thumbtack', text: 'Pin', action: `pinMessage(${messageId})` },
        { icon: 'fa-trash', text: 'Delete for me', action: `confirmDeleteMessage(${messageId})` },
        { icon: 'fa-check-square', text: 'Select', action: `selectMessage(${messageId})` },
    ];

    const menuItemsHtml = menuOptions
        .filter(option => option.condition !== false)
        .map(option => `
            <div class="context-menu-item" onclick="event.stopPropagation(); ${option.action}; closeMessageContextMenu();">
                <i class="fas ${option.icon}"></i>
                <span>${option.text}</span>
            </div>
        `).join('');

    menu.innerHTML = reactionsHtml + `<div class="context-menu-divider"></div>` + menuItemsHtml;

    // Position menu - add to DOM first to measure
    document.body.appendChild(menu);
    menu.style.visibility = 'hidden'; // Hide while measuring
    menu.style.display = 'block'; // Must be block to measure dimensions

    // Get menu dimensions (need to measure after adding to DOM)
    const rect = menu.getBoundingClientRect();
    const menuWidth = rect.width;
    const menuHeight = rect.height;

    // Get viewport dimensions
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;
    const padding = 10; // Padding from edges

    // Get click position
    const clickX = event.clientX;
    const clickY = event.clientY;

    // Calculate position with smart boundary detection
    let menuX = clickX;
    let menuY = clickY;

    // Horizontal positioning: prefer right of cursor, but flip to left if needed
    if (clickX + menuWidth + padding > viewportWidth) {
        // Would exceed right edge - try positioning to the left of cursor
        menuX = clickX - menuWidth;
        // If that would exceed left edge, align to right edge with padding
        if (menuX < padding) {
            menuX = viewportWidth - menuWidth - padding;
        }
    } else {
        // Fits on the right, but ensure it doesn't go off left edge
        if (menuX < padding) {
            menuX = padding;
        }
    }

    // Vertical positioning: prefer below cursor, but flip above if needed
    if (clickY + menuHeight + padding > viewportHeight) {
        // Would exceed bottom edge - position above cursor
        menuY = clickY - menuHeight;
        // If that would exceed top edge, align to bottom edge with padding
        if (menuY < padding) {
            menuY = viewportHeight - menuHeight - padding;
        }
    } else {
        // Fits below, but ensure it doesn't go off top edge
        if (menuY < padding) {
            menuY = padding;
        }
    }

    // Final boundary checks (safety net)
    menuX = Math.max(padding, Math.min(menuX, viewportWidth - menuWidth - padding));
    menuY = Math.max(padding, Math.min(menuY, viewportHeight - menuHeight - padding));

    // Apply position
    menu.style.left = menuX + 'px';
    menu.style.top = menuY + 'px';
    menu.style.display = 'block'; // Show after positioning
    menu.style.visibility = 'visible';

    // Close menu when clicking outside
    setTimeout(() => {
        document.addEventListener('click', closeMessageContextMenu, { once: true });
        document.addEventListener('contextmenu', closeMessageContextMenu, { once: true });
    }, 0);
}

function closeMessageContextMenu() {
    const menu = document.getElementById('messageContextMenu');
    if (menu) {
        menu.remove();
    }
    currentContextMenuMessageId = null;
}

// Context menu action functions
function replyToMessage(messageId) {
    const message = messagingState.messages.find(m => m.id === messageId);
    if (!message) return;

    // Store reply context
    messagingState.replyingTo = message;

    // Focus message input
    const messageInput = document.getElementById('messageInput');
    if (messageInput) {
        messageInput.focus();
    }

    // Show reply preview
    showReplyPreview(message);
}

function showReplyPreview(message) {
    // Remove existing reply preview if any
    const existingPreview = document.getElementById('replyPreview');
    if (existingPreview) {
        existingPreview.remove();
    }

    // Create reply preview element
    const inputContainer = document.getElementById('chatInputContainer');
    if (!inputContainer) return;

    const replyPreview = document.createElement('div');
    replyPreview.id = 'replyPreview';
    replyPreview.className = 'reply-preview';
    replyPreview.innerHTML = `
        <div class="reply-preview-content">
            <div class="reply-preview-header">
                <i class="fas fa-reply"></i>
                <span>Replying to ${escapeHtml(message.full_name || message.username || 'Unknown')}</span>
                <button class="btn-close-reply" onclick="cancelReply()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="reply-preview-message">
                ${message.message_type === 'text' ?
            `<div class="reply-preview-text">${escapeHtml(message.content.substring(0, 100))}${message.content.length > 100 ? '...' : ''}</div>` :
            message.message_type === 'file' || message.message_type === 'image' ?
                `<div class="reply-preview-attachment">
                        <i class="fas ${getFileIcon(message.attachment_name)}"></i>
                        <span>${escapeHtml(message.attachment_name || 'Attachment')}</span>
                    </div>` :
                `<div class="reply-preview-text">${escapeHtml(message.content || 'Message')}</div>`
        }
            </div>
        </div>
    `;

    // Insert before input wrapper
    const inputWrapper = inputContainer.querySelector('.chat-input-wrapper');
    if (inputWrapper) {
        inputContainer.insertBefore(replyPreview, inputWrapper);
    }
}

function cancelReply() {
    messagingState.replyingTo = null;
    const replyPreview = document.getElementById('replyPreview');
    if (replyPreview) {
        replyPreview.remove();
    }
}

function copyMessage(messageId) {
    const message = messagingState.messages.find(m => m.id === messageId);
    if (!message) return;

    navigator.clipboard.writeText(message.content).then(() => {
        // Show toast notification
        if (typeof showToast === 'function') {
            showToast('Message copied to clipboard');
        } else {
            // Simple toast fallback
            const toast = document.createElement('div');
            toast.style.cssText = 'position: fixed; bottom: 20px; right: 20px; background: #333; color: white; padding: 12px 20px; border-radius: 8px; z-index: 10000; font-size: 14px; max-width: 300px; word-wrap: break-word; white-space: normal; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
            toast.textContent = 'Message copied to clipboard';
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
}

async function openDirectMessage(userId) {
    if (!userId) return;

    try {
        // Get or create direct conversation with the user
        const response = await fetch('/api/messaging/conversation/direct', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'include',
            body: JSON.stringify({
                user_id: userId
            })
        });

        const data = await response.json();

        if (data.status === 200 && data.data && data.data.conversation_id) {
            // Reload conversations to ensure the new conversation appears in the list
            await loadConversations();

            // Switch to the direct conversation
            await selectConversation(data.data.conversation_id);
        } else {
            showError(data.message || 'Failed to open direct message');
        }
    } catch (error) {
        console.error('Error creating direct conversation:', error);
        showError('Failed to open direct message');
    }
}

async function replyPrivately(messageId) {
    const message = messagingState.messages.find(m => m.id === messageId);
    if (!message || !message.sender_id) return;

    // Use the same function as clicking avatar
    await openDirectMessage(message.sender_id);

    // Pre-fill message input with reply context
    const messageInput = document.getElementById('messageInput');
    if (messageInput) {
        messageInput.value = `Replying to your message: "${message.content.substring(0, 50)}${message.content.length > 50 ? '...' : ''}"\n\n`;
        messageInput.focus();
        messageInput.style.height = 'auto';
        messageInput.style.height = messageInput.scrollHeight + 'px';
    }
}

async function forwardMessage(messageId) {
    const message = messagingState.messages.find(m => m.id === messageId);
    if (!message) return;

    // Show forward dialog with conversation list
    showForwardDialog(message);
}

function showForwardDialog(message) {
    // Create modal overlay
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    overlay.id = 'forwardModal';
    overlay.innerHTML = `
        <div class="modal-content forward-modal">
            <div class="modal-header">
                <h3>Forward Message</h3>
                <button class="btn-close-modal" onclick="closeForwardDialog()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="forward-message-preview">
                    <div class="forward-preview-header">Message to forward:</div>
                    <div class="forward-preview-content">
                        ${message.message_type === 'text' ?
            `<div>${escapeHtml(message.content.substring(0, 150))}${message.content.length > 150 ? '...' : ''}</div>` :
            `<div><i class="fas ${getFileIcon(message.attachment_name)}"></i> ${escapeHtml(message.attachment_name || 'Attachment')}</div>`
        }
                    </div>
                </div>
                <div class="forward-conversations-list" id="forwardConversationsList">
                    <div class="loading-spinner">Loading conversations...</div>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    // Load conversations
    loadConversationsForForward(message.id);
}

async function loadConversationsForForward(messageId) {
    const listContainer = document.getElementById('forwardConversationsList');
    if (!listContainer) return;

    try {
        const response = await fetch('/api/messaging/conversations?sort=recent', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'include'
        });

        const data = await response.json();

        if (data.status === 200 && data.data && data.data.conversations) {
            const conversations = data.data.conversations.filter(c => c.id !== messagingState.currentConversationId);

            if (conversations.length === 0) {
                listContainer.innerHTML = '<div class="empty-state">No other conversations available</div>';
                return;
            }

            listContainer.innerHTML = conversations.map(conv => {
                const avatar = conv.other_avatar || conv.avatar_url || '';
                const name = conv.name || conv.other_name || 'Unknown';
                const lastMessage = conv.last_message ? (conv.last_message.content || '').substring(0, 50) : '';

                return `
                    <div class="forward-conversation-item" onclick="forwardToConversation(${messageId}, ${conv.id})">
                        <div class="conversation-avatar">
                            ${avatar ?
                        `<img src="${escapeHtml(avatar)}" alt="${escapeHtml(name)}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">` :
                        ''
                    }
                            <div style="${avatar ? 'display: none;' : ''} width: 100%; height: 100%; background: linear-gradient(135deg, #2454FF 0%, #4B7BFF 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.9rem;">
                                ${name.charAt(0).toUpperCase()}
                            </div>
                        </div>
                        <div class="conversation-info">
                            <div class="conversation-name">${escapeHtml(name)}</div>
                            ${lastMessage ? `<div class="conversation-preview">${escapeHtml(lastMessage)}</div>` : ''}
                        </div>
                        <i class="fas fa-chevron-right"></i>
                    </div>
                `;
            }).join('');
        } else {
            listContainer.innerHTML = '<div class="empty-state">Failed to load conversations</div>';
        }
    } catch (error) {
        console.error('Error loading conversations:', error);
        listContainer.innerHTML = '<div class="empty-state">Error loading conversations</div>';
    }
}

async function forwardToConversation(messageId, conversationId) {
    const message = messagingState.messages.find(m => m.id === messageId);
    if (!message) return;

    try {
        // Forward the message content
        const forwardContent = message.message_type === 'text' ?
            message.content :
            `Forwarded ${message.message_type}: ${message.attachment_name || 'attachment'}`;

        const response = await fetch('/api/messaging/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'include',
            body: JSON.stringify({
                conversation_id: conversationId,
                content: forwardContent,
                message_type: message.message_type,
                attachment_url: message.attachment_url,
                attachment_name: message.attachment_name,
                attachment_size: message.attachment_size
            })
        });

        const data = await response.json();

        if (data.status === 200) {
            if (typeof showToast === 'function') {
                showToast('Message forwarded');
            } else {
                alert('Message forwarded successfully');
            }
            closeForwardDialog();
        } else {
            showError(data.message || 'Failed to forward message');
        }
    } catch (error) {
        console.error('Error forwarding message:', error);
        showError('Failed to forward message');
    }
}

function closeForwardDialog() {
    const modal = document.getElementById('forwardModal');
    if (modal) {
        modal.remove();
    }
}

function pinMessage(messageId) {
    const message = messagingState.messages.find(m => m.id === messageId);
    if (!message) return;

    // Toggle pin status
    if (messagingState.pinnedMessages.has(messageId)) {
        messagingState.pinnedMessages.delete(messageId);
    } else {
        messagingState.pinnedMessages.add(messageId);
    }

    // Update UI to show pin indicator
    updatePinIndicator(messageId);

    // Store in localStorage for persistence
    try {
        const pinned = Array.from(messagingState.pinnedMessages);
        localStorage.setItem('pinnedMessages', JSON.stringify(pinned));
    } catch (e) {
        console.error('Failed to save pinned messages:', e);
    }

    // Show feedback
    const isPinned = messagingState.pinnedMessages.has(messageId);
    if (typeof showToast === 'function') {
        showToast(isPinned ? 'Message pinned' : 'Message unpinned');
    }
}

function updatePinIndicator(messageId) {
    const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
    if (!messageElement) return;

    const isPinned = messagingState.pinnedMessages.has(messageId);
    let pinIndicator = messageElement.querySelector('.pin-indicator');

    if (isPinned) {
        if (!pinIndicator) {
            pinIndicator = document.createElement('div');
            pinIndicator.className = 'pin-indicator';
            pinIndicator.innerHTML = '<i class="fas fa-thumbtack"></i>';
            const messageContent = messageElement.querySelector('.message-content');
            if (messageContent) {
                messageContent.insertBefore(pinIndicator, messageContent.firstChild);
            }
        }
        pinIndicator.style.display = 'flex';
    } else {
        if (pinIndicator) {
            pinIndicator.style.display = 'none';
        }
    }
}

function loadPinnedMessages() {
    try {
        const stored = localStorage.getItem('pinnedMessages');
        if (stored) {
            const pinned = JSON.parse(stored);
            messagingState.pinnedMessages = new Set(pinned);

            // Update UI for all pinned messages
            pinned.forEach(messageId => {
                updatePinIndicator(messageId);
            });
        }
    } catch (e) {
        console.error('Failed to load pinned messages:', e);
    }
}

function selectMessage(messageId) {
    // Enter selection mode
    messagingState.isSelectionMode = true;
    messagingState.selectedMessages.clear();
    if (messageId) {
        messagingState.selectedMessages.add(messageId);
    }

    // Show selection bar
    showSelectionBar();

    // Re-render messages with checkboxes
    renderMessages();

    // Force show all checkboxes after render - use requestAnimationFrame for better timing
    requestAnimationFrame(() => {
        const messageGroups = document.querySelectorAll('.message-group');
        messageGroups.forEach(group => {
            // Ensure selection-mode class is added
            if (messagingState.isSelectionMode) {
                group.classList.add('selection-mode');
                const checkbox = group.querySelector('.message-checkbox');
                if (checkbox) {
                    // Force visibility with inline styles
                    checkbox.style.display = 'flex';
                    checkbox.style.opacity = '1';
                    checkbox.style.visibility = 'visible';
                    // Also ensure the checkbox input is visible
                    const checkboxInput = checkbox.querySelector('input[type="checkbox"]');
                    if (checkboxInput) {
                        checkboxInput.style.display = 'block';
                        checkboxInput.style.visibility = 'visible';
                    }
                }
            }
        });

        // Update selection bar
        updateSelectionBar();
    });
}

function exitSelectionMode() {
    messagingState.isSelectionMode = false;
    messagingState.selectedMessages.clear();
    hideSelectionBar();

    // Hide all checkboxes
    document.querySelectorAll('.message-checkbox').forEach(checkbox => {
        checkbox.style.display = 'none';
    });

    // Remove selected styling
    document.querySelectorAll('.message-group.message-selected').forEach(group => {
        group.classList.remove('message-selected');
    });

    renderMessages();
}

function toggleMessageSelection(messageId) {
    if (!messagingState.isSelectionMode) {
        selectMessage(messageId);
        return;
    }

    if (messagingState.selectedMessages.has(messageId)) {
        messagingState.selectedMessages.delete(messageId);
    } else {
        messagingState.selectedMessages.add(messageId);
    }

    // Update selection bar count
    updateSelectionBar();

    // Update checkbox state and message group styling
    const checkbox = document.querySelector(`input[type="checkbox"][data-message-id="${messageId}"]`);
    const messageGroup = document.querySelector(`.message-group[data-message-id="${messageId}"]`);

    if (checkbox) {
        checkbox.checked = messagingState.selectedMessages.has(messageId);
    }

    if (messageGroup) {
        if (messagingState.selectedMessages.has(messageId)) {
            messageGroup.classList.add('message-selected');
        } else {
            messageGroup.classList.remove('message-selected');
        }
    }

    // If no messages selected, exit selection mode
    if (messagingState.selectedMessages.size === 0) {
        exitSelectionMode();
    }
}

function showSelectionBar() {
    const chatHeader = document.getElementById('chatHeader');
    if (!chatHeader) return;

    const selectedCount = messagingState.selectedMessages.size;
    chatHeader.innerHTML = `
        <div class="selection-bar">
            <button class="btn-selection-cancel" onclick="exitSelectionMode()">
                <i class="fas fa-times"></i>
            </button>
            <div class="selection-count">${selectedCount} selected</div>
            <div class="selection-actions">
                <button class="btn-selection-action" onclick="starSelectedMessages()" title="Star" ${selectedCount === 0 ? 'disabled' : ''}>
                    <i class="fas fa-star"></i>
                </button>
                <button class="btn-selection-action" onclick="copySelectedMessages()" title="Copy" ${selectedCount === 0 ? 'disabled' : ''}>
                    <i class="fas fa-copy"></i>
                </button>
                <button class="btn-selection-action" onclick="forwardSelectedMessages()" title="Forward" ${selectedCount === 0 ? 'disabled' : ''}>
                    <i class="fas fa-share"></i>
                </button>
                <button class="btn-selection-action btn-selection-delete" onclick="deleteSelectedMessages()" title="Delete" ${selectedCount === 0 ? 'disabled' : ''}>
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;

    // Hide input container in selection mode
    const inputContainer = document.getElementById('chatInputContainer');
    if (inputContainer) {
        inputContainer.style.display = 'none';
    }
}

function hideSelectionBar() {
    const chatHeader = document.getElementById('chatHeader');
    if (!chatHeader) return;

    // Restore original header
    const conversation = messagingState.conversations.find(c => c.id === messagingState.currentConversationId);
    if (conversation) {
        loadConversationDetailsFromCache(conversation);
    } else {
        chatHeader.innerHTML = `
            <div class="chat-header-placeholder">
                <i class="fas fa-comment-dots"></i>
                <p>Select a conversation to start chatting</p>
            </div>
        `;
    }

    // Show input container again
    const inputContainer = document.getElementById('chatInputContainer');
    if (inputContainer && messagingState.currentConversationId) {
        inputContainer.style.display = 'block';
    }
}

function updateSelectionBar() {
    const selectionCount = document.querySelector('.selection-count');
    if (selectionCount) {
        selectionCount.textContent = `${messagingState.selectedMessages.size} selected`;
    }

    // Update action buttons state
    const hasSelection = messagingState.selectedMessages.size > 0;
    const actionButtons = document.querySelectorAll('.btn-selection-action');
    actionButtons.forEach(btn => {
        btn.disabled = !hasSelection;
        btn.style.opacity = hasSelection ? '1' : '0.5';
        btn.style.cursor = hasSelection ? 'pointer' : 'not-allowed';
    });
}

function starSelectedMessages() {
    const messageIds = Array.from(messagingState.selectedMessages);
    console.log('Star messages:', messageIds);
    // Implement API call to star messages
    exitSelectionMode();
}

function copySelectedMessages() {
    const messageIds = Array.from(messagingState.selectedMessages);
    const messages = messagingState.messages.filter(m => messageIds.includes(m.id));
    const text = messages.map(m => m.content).join('\n');

    navigator.clipboard.writeText(text).then(() => {
        if (typeof showToast === 'function') {
            showToast('Messages copied to clipboard');
        } else {
            const toast = document.createElement('div');
            toast.style.cssText = 'position: fixed; bottom: 20px; right: 20px; background: #333; color: white; padding: 12px 20px; border-radius: 8px; z-index: 10000; font-size: 14px; max-width: 300px; word-wrap: break-word; white-space: normal; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
            toast.textContent = 'Messages copied to clipboard';
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
    }).catch(err => {
        console.error('Failed to copy:', err);
    });

    exitSelectionMode();
}

function forwardSelectedMessages() {
    const messageIds = Array.from(messagingState.selectedMessages);
    console.log('Forward messages:', messageIds);
    // Implement forward dialog
    exitSelectionMode();
}

async function deleteSelectedMessages() {
    const messageIds = Array.from(messagingState.selectedMessages);
    const count = messageIds.length;

    if (count === 0) return;

    if (!confirm(`Are you sure you want to delete ${count} message(s)? This action cannot be undone.`)) {
        return;
    }

    try {
        // Delete messages one by one (or implement batch delete API)
        const deletePromises = messageIds.map(messageId => confirmDeleteMessage(messageId, false));
        await Promise.all(deletePromises);

        // Reload messages to reflect deletions
        if (messagingState.currentConversationId) {
            await loadMessages(messagingState.currentConversationId, 1);

            // Update pin indicators after loading
            setTimeout(() => {
                messagingState.pinnedMessages.forEach(messageId => {
                    updatePinIndicator(messageId);
                });
            }, 100);
        }

        exitSelectionMode();
    } catch (error) {
        console.error('Error deleting messages:', error);
        alert('Failed to delete some messages');
    }
}


async function addReaction(messageId, emoji) {
    try {
        const response = await fetch(`/api/messaging/message/${messageId}/reaction`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            credentials: 'include',
            body: JSON.stringify({ emoji: emoji })
        });

        const data = await response.json();

        if (data.status === 200) {
            // Update message reactions in state
            const message = messagingState.messages.find(m => m.id === messageId);
            if (message) {
                message.reactions = data.data.reactions;
            }

            // Clear cache for this message to force re-render
            messageElementsCache.delete(messageId);

            // Re-render the message to show updated reactions
            renderMessages();

            // Close context menu and emoji picker
            closeMessageContextMenu();
            const picker = document.getElementById('emojiPickerContainer');
            if (picker) picker.remove();
        } else {
            showError(data.message || 'Failed to add reaction');
        }
    } catch (error) {
        console.error('Error adding reaction:', error);
        showError('Failed to add reaction');
    }
}

function showMoreReactions(messageId) {
    // Close context menu first
    closeMessageContextMenu();

    // Create emoji picker container
    let pickerContainer = document.getElementById('emojiPickerContainer');
    if (pickerContainer) {
        pickerContainer.remove();
    }

    pickerContainer = document.createElement('div');
    pickerContainer.id = 'emojiPickerContainer';
    pickerContainer.style.cssText = 'position: fixed; z-index: 10001; background: white; border: 1px solid #ddd; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); padding: 12px; max-width: 350px; max-height: 400px; overflow-y: auto;';

    // Get message position for placement
    const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
    if (messageElement) {
        const rect = messageElement.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        const pickerWidth = 350; // max-width
        const pickerHeight = 400; // max-height
        const spacing = 10; // spacing from message

        // Calculate if picker would overflow on the right
        const rightSpace = viewportWidth - rect.right;
        const leftSpace = rect.left;
        const wouldOverflowRight = (rect.right + spacing + pickerWidth) > viewportWidth;
        const wouldOverflowLeft = (rect.left - spacing - pickerWidth) < 0;

        // Position horizontally: prefer right, but use left if right would overflow
        if (wouldOverflowRight && leftSpace >= pickerWidth) {
            // Position on the left side of the message
            pickerContainer.style.left = (rect.left - pickerWidth - spacing) + 'px';
        } else if (wouldOverflowRight && rightSpace < pickerWidth && leftSpace < pickerWidth) {
            // Not enough space on either side, position on left edge with margin
            pickerContainer.style.left = '20px';
        } else {
            // Position on the right side (default)
            pickerContainer.style.left = (rect.right + spacing) + 'px';
        }

        // Position vertically: ensure it doesn't overflow bottom
        let topPosition = rect.top;
        if (topPosition + pickerHeight > viewportHeight) {
            // Adjust to fit in viewport
            topPosition = Math.max(10, viewportHeight - pickerHeight - 10);
        }
        // Ensure it doesn't go above viewport
        if (topPosition < 10) {
            topPosition = 10;
        }
        pickerContainer.style.top = topPosition + 'px';
    } else {
        // Fallback position
        pickerContainer.style.right = '20px';
        pickerContainer.style.bottom = '100px';
    }

    // Popular emojis organized by category
    const emojiCategories = {
        'Smileys & People': ['😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣', '😊', '😇', '🙂', '🙃', '😉', '😌', '😍', '🥰', '😘', '😗', '😙', '😚', '😋', '😛', '😝', '😜', '🤪', '🤨', '🧐', '🤓', '😎', '🤩', '🥳', '😏', '😒', '😞', '😔', '😟', '😕', '🙁', '😣', '😖', '😫', '😩', '🥺', '😢', '😭', '😤', '😠', '😡', '🤬', '🤯', '😳', '🥵', '🥶', '😱', '😨', '😰', '😥', '😓'],
        'Gestures': ['👋', '🤚', '🖐', '✋', '🖖', '👌', '🤏', '✌️', '🤞', '🤟', '🤘', '🤙', '👈', '👉', '👆', '🖕', '👇', '☝️', '👍', '👎', '✊', '👊', '🤛', '🤜', '👏', '🙌', '👐', '🤲', '🤝', '🙏'],
        'Hearts & Love': ['❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '❣️', '💕', '💞', '💓', '💗', '💖', '💘', '💝', '💟'],
        'Objects & Symbols': ['⭐', '🌟', '✨', '💫', '🔥', '💯', '✅', '❌', '❗', '❓', '💡', '🎉', '🎊', '🎈', '🎁'],
    };

    let emojiHtml = '<div style="display: flex; flex-direction: column; gap: 12px;">';

    for (const [category, emojis] of Object.entries(emojiCategories)) {
        emojiHtml += `<div><div style="font-size: 11px; color: #666; margin-bottom: 6px; font-weight: 600;">${category}</div>`;
        emojiHtml += '<div style="display: flex; flex-wrap: wrap; gap: 4px;">';
        emojis.forEach(emoji => {
            emojiHtml += `<button onclick="event.stopPropagation(); addReaction(${messageId}, '${emoji}'); document.getElementById('emojiPickerContainer')?.remove();" style="font-size: 24px; background: none; border: none; cursor: pointer; padding: 4px; border-radius: 6px; transition: background 0.2s;" onmouseover="this.style.background='#f0f0f0'" onmouseout="this.style.background='none'">${emoji}</button>`;
        });
        emojiHtml += '</div></div>';
    }

    emojiHtml += '</div>';
    pickerContainer.innerHTML = emojiHtml;

    document.body.appendChild(pickerContainer);

    // Close picker when clicking outside
    setTimeout(() => {
        const closePicker = (e) => {
            if (!pickerContainer.contains(e.target) && e.target.closest('.reaction-btn') !== document.querySelector(`[data-message-id="${messageId}"]`)) {
                pickerContainer.remove();
                document.removeEventListener('click', closePicker);
            }
        };
        document.addEventListener('click', closePicker);
    }, 100);
}

// Render message reactions
function renderMessageReactions(msg) {
    if (!msg.reactions || Object.keys(msg.reactions).length === 0) {
        return '';
    }

    // Map reaction types to emojis
    const emojiMap = {
        'like': '👍',
        'love': '❤️',
        'laugh': '😂',
        'surprised': '😮',
        'sad': '😥',
        'pray': '🙏',
        'angry': '😡',
        'star': '⭐',
    };

    const currentUserId = getCurrentUserId();
    let reactionsHtml = '<div class="message-reactions-display" style="margin-top: 6px; display: flex; flex-wrap: wrap; gap: 4px;">';

    for (const [reactionType, reactionData] of Object.entries(msg.reactions)) {
        const emoji = emojiMap[reactionType] || '👍';
        const count = reactionData.count || 0;
        const userReacted = reactionData.user_reacted || false;

        if (count > 0) {
            reactionsHtml += `
                <button class="message-reaction-badge ${userReacted ? 'user-reacted' : ''}" 
                        onclick="event.stopPropagation(); addReaction(${msg.id}, '${emoji}');"
                        style="background: ${userReacted ? '#e3f2fd' : '#f5f5f5'}; border: 1px solid ${userReacted ? '#2196f3' : '#ddd'}; border-radius: 12px; padding: 2px 8px; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: all 0.2s;"
                        onmouseover="this.style.background='${userReacted ? '#bbdefb' : '#e0e0e0'}'"
                        onmouseout="this.style.background='${userReacted ? '#e3f2fd' : '#f5f5f5'}'"
                        title="${reactionData.users?.map(u => u.full_name || u.username).join(', ') || ''}">
                    <span style="font-size: 14px;">${emoji}</span>
                    <span style="color: #666; font-weight: 500;">${count}</span>
                </button>
            `;
        }
    }

    reactionsHtml += '</div>';
    return reactionsHtml;
}

async function confirmDeleteMessage(messageId, showConfirm = true) {
    if (showConfirm && !confirm('Are you sure you want to delete this message? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch(`/api/messaging/message/${messageId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'include'
        });

        const data = await response.json();

        if (data.status === 200) {
            // Remove message from state
            messagingState.messages = messagingState.messages.filter(m => m.id !== messageId);
            messageElementsCache.delete(messageId);
            // Remove from cache
            removeMessageFromCache(messagingState.currentConversationId, messageId);
            renderMessages();
        } else {
            alert(data.message || 'Failed to delete message');
        }
    } catch (error) {
        console.error('Error deleting message:', error);
        alert('Failed to delete message');
    }
}

async function permanentlyDeleteMessage(messageId) {
    if (!confirm('Are you sure you want to permanently delete this message? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch(`/api/messaging/message/${messageId}/permanent`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'include'
        });

        const data = await response.json();

        if (data.status === 200) {
            // Remove message from state
            messagingState.messages = messagingState.messages.filter(m => m.id !== messageId);
            messageElementsCache.delete(messageId);
            // Remove from cache
            removeMessageFromCache(messagingState.currentConversationId, messageId);
            renderMessages();
        } else {
            alert(data.message || 'Failed to permanently delete message');
        }
    } catch (error) {
        console.error('Error permanently deleting message:', error);
        alert('Failed to permanently delete message');
    }
}

async function confirmDeleteConversation(conversationId) {
    const conversation = messagingState.conversations.find(c => c.id === conversationId);
    const isGroup = conversation && conversation.type === 'group';
    const message = isGroup
        ? 'Are you sure you want to delete this group? All messages and members will be removed. This action cannot be undone.'
        : 'Are you sure you want to delete this conversation? This action cannot be undone.';

    if (!confirm(message)) {
        return;
    }

    try {
        const response = await fetch(`/api/messaging/conversation/${conversationId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'include'
        });

        const data = await response.json();

        if (data.status === 200) {
            // Remove conversation from state
            messagingState.conversations = messagingState.conversations.filter(c => c.id !== conversationId);
            messagingState.currentConversationId = null;
            messagingState.messages = [];
            messageElementsCache.clear();
            lastRenderedMessageIds.clear();

            // Clear cache for this conversation
            clearConversationCache(conversationId);

            // Clear UI
            document.getElementById('chatHeader').innerHTML = `
                <div class="chat-header-placeholder">
                    <i class="fas fa-comment-dots"></i>
                    <p>Select a conversation to start chatting</p>
                </div>
            `;
            document.getElementById('chatMessages').innerHTML = '';
            document.getElementById('chatInputContainer').style.display = 'none';
            document.getElementById('chatActionsBar').style.display = 'none';
            document.getElementById('infoSidebar').style.display = 'none';

            renderConversations();
            alert(data.message || 'Conversation deleted');
        } else {
            alert(data.message || 'Failed to delete conversation');
        }
    } catch (error) {
        console.error('Error deleting conversation:', error);
        alert('Failed to delete conversation');
    }
}

async function confirmClearAllMessages(conversationId) {
    if (!confirm('Are you sure you want to clear all messages in this conversation? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch(`/api/messaging/conversation/${conversationId}/messages`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'include'
        });

        const data = await response.json();

        if (data.status === 200) {
            // Clear messages from state
            messagingState.messages = [];
            messageElementsCache.clear();
            lastRenderedMessageIds.clear();
            renderMessages();
            alert(data.message || 'All messages cleared');
        } else {
            alert(data.message || 'Failed to clear messages');
        }
    } catch (error) {
        console.error('Error clearing messages:', error);
        alert('Failed to clear messages');
    }
}

async function archiveConversation(conversationId) {
    try {
        const response = await fetch('/api/messaging/archive', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'include',
            body: JSON.stringify({
                conversation_id: conversationId,
                archive: true
            })
        });

        const data = await response.json();

        if (data.status === 200) {
            // Remove from active conversations
            messagingState.conversations = messagingState.conversations.filter(c => c.id !== conversationId);
            if (messagingState.currentConversationId === conversationId) {
                messagingState.currentConversationId = null;
                messagingState.messages = [];
                document.getElementById('chatHeader').innerHTML = `
                    <div class="chat-header-placeholder">
                        <i class="fas fa-comment-dots"></i>
                        <p>Select a conversation to start chatting</p>
                    </div>
                `;
                document.getElementById('chatMessages').innerHTML = '';
                document.getElementById('chatInputContainer').style.display = 'none';
                document.getElementById('chatActionsBar').style.display = 'none';
            }
            renderConversations();
        } else {
            alert(data.message || 'Failed to archive conversation');
        }
    } catch (error) {
        console.error('Error archiving conversation:', error);
        alert('Failed to archive conversation');
    }
}

// Group management functions
async function renameGroup(conversationId) {
    const conversation = messagingState.conversations.find(c => c.id === conversationId);
    if (!conversation || conversation.type !== 'group') {
        alert('This is not a group chat');
        return;
    }

    const newName = prompt('Enter new group name:', conversation.name || '');
    if (!newName || newName.trim() === '') {
        return;
    }

    try {
        const response = await fetch(`/api/messaging/group/${conversationId}/rename`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'include',
            body: JSON.stringify({
                name: newName.trim()
            })
        });

        const data = await response.json();

        if (data.status === 200) {
            // Update conversation name in state
            const conv = messagingState.conversations.find(c => c.id === conversationId);
            if (conv) {
                conv.name = data.data.name;
            }
            renderConversations();
            loadConversationDetails(conversationId);
            alert('Group renamed successfully');
        } else {
            alert(data.message || 'Failed to rename group');
        }
    } catch (error) {
        console.error('Error renaming group:', error);
        alert('Failed to rename group');
    }
}

async function removeGroupMember(conversationId, memberId) {
    if (!confirm('Are you sure you want to remove this member from the group?')) {
        return;
    }

    try {
        const response = await fetch('/api/messaging/group/members', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'include',
            body: JSON.stringify({
                conversation_id: conversationId,
                action: 'remove',
                member_id: memberId
            })
        });

        const data = await response.json();

        if (data.status === 200) {
            // Reload conversation details
            loadConversationDetails(conversationId);
            alert('Member removed successfully');
        } else {
            alert(data.message || 'Failed to remove member');
        }
    } catch (error) {
        console.error('Error removing member:', error);
        alert('Failed to remove member');
    }
}

function toggleConversationOptions(conversationId) {
    const menu = document.getElementById(`conversationOptions_${conversationId}`);
    if (menu) {
        // Close all other menus
        document.querySelectorAll('.conversation-options-menu').forEach(m => {
            if (m.id !== `conversationOptions_${conversationId}`) {
                m.style.display = 'none';
            }
        });
        menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
    }
}

// Close conversation options when clicking outside
document.addEventListener('click', (e) => {
    // Don't close if clicking inside the options container (button or menu)
    if (!e.target.closest('.conversation-options-container') &&
        !e.target.closest('.conversation-options-menu') &&
        !e.target.closest('.btn-icon')) {
        document.querySelectorAll('.conversation-options-menu').forEach(menu => {
            menu.style.display = 'none';
        });
    }
});

// Export functions for global access
window.confirmDeleteMessage = confirmDeleteMessage;
window.permanentlyDeleteMessage = permanentlyDeleteMessage;
window.confirmDeleteConversation = confirmDeleteConversation;
window.confirmClearAllMessages = confirmClearAllMessages;
window.archiveConversation = archiveConversation;
window.renameGroup = renameGroup;
window.removeGroupMember = removeGroupMember;
window.showMessageContextMenu = showMessageContextMenu;
window.closeMessageContextMenu = closeMessageContextMenu;
window.replyToMessage = replyToMessage;
window.cancelReply = cancelReply;
window.copyMessage = copyMessage;
window.replyPrivately = replyPrivately;
window.openDirectMessage = openDirectMessage;
window.forwardMessage = forwardMessage;
window.forwardToConversation = forwardToConversation;
window.closeForwardDialog = closeForwardDialog;
window.pinMessage = pinMessage;
window.selectMessage = selectMessage;
window.exitSelectionMode = exitSelectionMode;
window.toggleMessageSelection = toggleMessageSelection;
window.starSelectedMessages = starSelectedMessages;
window.copySelectedMessages = copySelectedMessages;
window.forwardSelectedMessages = forwardSelectedMessages;
window.deleteSelectedMessages = deleteSelectedMessages;
// shareMessage function - placeholder for future implementation
window.shareMessage = function (messageId) {
    console.log('Share message:', messageId);
    // TODO: Implement share message functionality
};
window.addReaction = addReaction;
window.showMoreReactions = showMoreReactions;
window.renderMessageReactions = renderMessageReactions;
window.toggleConversationOptions = toggleConversationOptions;

// Export for onclick handlers
window.selectConversation = selectConversation;
window.openInfoSidebar = openInfoSidebar;
window.scrollToMessage = scrollToMessage;
window.unarchiveConversation = unarchiveConversation;
window.toggleMemberCheckbox = toggleMemberCheckbox;
window.updateSelectedMembers = updateSelectedMembers;
window.loadAvailableMembers = loadAvailableMembers;

