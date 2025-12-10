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
    searchResults: []
};

document.addEventListener('DOMContentLoaded', () => {
    // Check if user is logged in
    const userLoggedIn = sessionStorage.getItem('userLoggedIn');
    if (userLoggedIn !== 'true') {
        window.location.href = '/login';
        return;
    }
    
    initNavigation();
    loadNavigationUserInfo();
    initEventListeners();
    loadConversations();
    connectWebSocket();
    
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
    
    if (conversationId && shareUrl) {
        // Wait for conversations to load
        await new Promise(resolve => setTimeout(resolve, 500));
        
        // Select the conversation
        await selectConversation(parseInt(conversationId));
        
        // Pre-fill the message input with the shared post link
        const messageInput = document.getElementById('messageInput');
        if (messageInput) {
            messageInput.value = `Check out this post: ${decodeURIComponent(shareUrl)}`;
            messageInput.style.height = 'auto';
            messageInput.style.height = messageInput.scrollHeight + 'px';
            messageInput.focus();
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
    
    document.getElementById('btnAttach').addEventListener('click', () => {
        document.getElementById('fileInput').click();
    });
    document.getElementById('fileInput').addEventListener('change', handleFileUpload);
    
    document.getElementById('btnCreateGroup').addEventListener('click', openCreateGroupModal);
    document.getElementById('closeGroupModal').addEventListener('click', closeCreateGroupModal);
    document.getElementById('cancelGroupModal').addEventListener('click', closeCreateGroupModal);
    document.getElementById('createGroupForm').addEventListener('submit', createGroupChat);
    
    // Search members functionality
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
                    `<img src="${conv.other_avatar}" alt="${conv.other_username}">` :
                    conv.type === 'group' ? 
                        '<i class="fas fa-users"></i>' :
                        (conv.other_username ? conv.other_username.charAt(0).toUpperCase() : '?')
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
    
    // Clear message cache when switching conversations
    messageElementsCache.clear();
    lastRenderedMessageIds.clear();
    
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

async function loadMessages(conversationId, page = 1) {
    try {
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
        
        if (data.status === 200) {
            const newMessages = data.data.messages || [];
            
            if (page === 1) {
                // Reverse messages since API returns DESC (newest first), but we want oldest first for display
                messagingState.messages = newMessages.reverse();
            } else {
                // For pagination, prepend older messages
                messagingState.messages = [...newMessages.reverse(), ...messagingState.messages];
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
            
            renderMessages();
            
            // Store participants for typing indicator
            if (data.data.conversation && data.data.conversation.members) {
                messagingState.participants = Array.isArray(data.data.conversation.members) 
                    ? data.data.conversation.members 
                    : [];
            }
            
            if (data.data.conversation && data.data.conversation.type === 'group') {
                loadGroupInfo(data.data.conversation);
            }
        } else {
            showError(data.message || 'Gagal memuatkan mesej');
        }
    } catch (error) {
        console.error('Error loading messages:', error);
        showError('Gagal memuatkan mesej. Sila cuba lagi.');
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
    // Loading will be replaced by renderMessages() or empty state
}

function renderMessages() {
    const container = document.getElementById('chatMessages');
    
    if (!container) return;
    
    if (messagingState.messages.length === 0) {
        container.innerHTML = '<div style="text-align: center; padding: 40px; color: #999; margin-top: auto;">Tiada mesej lagi. Mulakan perbualan!</div>';
        messageElementsCache.clear();
        lastRenderedMessageIds.clear();
        return;
    }
    
    // Store scroll position before rendering
    const wasAtBottom = isScrolledToBottom(container);
    const previousScrollHeight = container.scrollHeight;
    
    const currentUserId = getCurrentUserId();
    const currentMessageIds = new Set(messagingState.messages.map(m => m.id));
    
    // Check if we can do incremental update (only new messages added)
    const hasRemovedMessages = Array.from(lastRenderedMessageIds).some(id => !currentMessageIds.has(id));
    
    // If messages were removed or order changed, do full re-render
    if (hasRemovedMessages || messagingState.messages.length !== lastRenderedMessageIds.size) {
        messageElementsCache.clear();
        lastRenderedMessageIds.clear();
    }
    
    // Use DocumentFragment for better performance
    const fragment = document.createDocumentFragment();
    const tempDiv = document.createElement('div');
    
    // Pre-calculate dates to avoid repeated parsing
    const messageDates = messagingState.messages.map(msg => ({
        msg,
        date: new Date(msg.created_at),
        timestamp: new Date(msg.created_at).getTime()
    }));
    
    // Render messages efficiently
    messageDates.forEach(({ msg, timestamp }, index) => {
        // Check cache first
        let messageElement = messageElementsCache.get(msg.id);
        
        if (!messageElement) {
            const isOwn = msg.sender_id === currentUserId;
            const prevMsgData = index > 0 ? messageDates[index - 1] : null;
            const isGrouped = prevMsgData && 
                             prevMsgData.msg.sender_id === msg.sender_id &&
                             (timestamp - prevMsgData.timestamp) < 300000; // 5 minutes
            
            // Build message HTML
            const messageHtml = `
                <div class="message-group" data-message-id="${msg.id}" onmouseenter="showMessageOptions(${msg.id})" onmouseleave="hideMessageOptions(${msg.id})">
                    <div class="message ${isOwn ? 'own' : ''}">
                        ${!isGrouped ? `
                            <div class="message-avatar">
                                ${msg.avatar_url ? 
                                    `<img src="${escapeHtml(msg.avatar_url)}" alt="${escapeHtml(msg.username || '')}" loading="lazy">` :
                                    (msg.username ? escapeHtml(msg.username.charAt(0).toUpperCase()) : '?')
                                }
                            </div>
                        ` : '<div class="message-avatar" style="visibility: hidden; width: 34px;"></div>'}
                        <div class="message-content">
                            ${!isGrouped && !isOwn ? `<div style="font-size: 12px; color: #666; margin-bottom: 5px;">${escapeHtml(msg.full_name || msg.username || 'Unknown')}</div>` : ''}
                            <div class="message-bubble">
                                ${msg.message_type === 'text' ? 
                                    `<div>${formatMessageContent(msg.content)}</div>` :
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
                                ${isOwn ? `
                                    <div class="message-options" id="messageOptions_${msg.id}" style="display: none;">
                                        <button class="btn-message-option" onclick="event.stopPropagation(); confirmDeleteMessage(${msg.id})" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                ` : ''}
                            </div>
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
                attachment_name: attachmentName
            })
        });
        
        const data = await response.json();
        
        if (data.status === 200) {
            input.value = '';
            input.style.height = 'auto';
            
            // Don't reload immediately - wait for WebSocket to deliver the message
            // This prevents race conditions where loadMessages might not include the new message
            // The WebSocket handler will add the message when it arrives
            await loadConversations();
            
            // If WebSocket doesn't deliver within 1 second, reload messages as fallback
            setTimeout(async () => {
                // Check if the new message is already in the array
                const messageExists = messagingState.messages.some(m => m.id === data.data.message_id);
                if (!messageExists) {
                    // Message not received via WebSocket, reload from server
                    await loadMessages(messagingState.currentConversationId);
                }
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
                    `<img src="${conv.other_avatar}" alt="${conv.other_username}">` :
                    conv.type === 'group' ? 
                        '<i class="fas fa-users"></i>' :
                        (conv.other_username ? conv.other_username.charAt(0).toUpperCase() : '?')
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
            const isOwn = msg.sender_id === currentUserId;
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

function openCreateGroupModal() {
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
                    `<img src="${conversation.other_avatar}" alt="${conversation.other_username}">` :
                    conversation.type === 'group' ?
                        '<i class="fas fa-users"></i>' :
                        (conversation.other_username ? conversation.other_username.charAt(0).toUpperCase() : '?')
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
            <button class="btn-icon" onclick="event.stopPropagation(); toggleConversationOptions(${conversation.id})">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <div id="conversationOptions_${conversation.id}" class="conversation-options-menu" style="display: none;">
                <button class="conversation-option-item" onclick="archiveConversation(${conversation.id})">
                    <i class="fas fa-archive"></i> Archive
                </button>
                ${isGroupCreator ? `
                    <button class="conversation-option-item" onclick="confirmClearAllMessages(${conversation.id})">
                        <i class="fas fa-broom"></i> Clear All Messages
                    </button>
                    <button class="conversation-option-item" onclick="confirmRenameGroup(${conversation.id}, '${escapeHtml(conversation.name)}')">
                        <i class="fas fa-edit"></i> Rename Group
                    </button>
                ` : ''}
                ${conversation.type === 'group' ? `
                    <button class="conversation-option-item" onclick="openInfoSidebar()">
                        <i class="fas fa-info-circle"></i> Group Info
                    </button>
                ` : ''}
                <button class="conversation-option-item delete-option" onclick="confirmDeleteConversation(${conversation.id})">
                    <i class="fas fa-trash"></i> ${conversation.type === 'group' ? 'Delete Group' : 'Delete Conversation'}
                </button>
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

// WebSocket connection management
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
    
    // Determine WebSocket URL (adjust port/host as needed)
    const wsProtocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
    const wsHost = window.location.hostname;
    const wsPort = '8080'; // WebSocket server port
    const wsUrl = `${wsProtocol}//${wsHost}:${wsPort}`;
    
    try {
        messagingState.ws = new WebSocket(wsUrl);
        
        messagingState.ws.onopen = () => {
            console.log('WebSocket connected');
            messagingState.wsReconnectAttempts = 0;
            
            // Authenticate with user ID
            messagingState.ws.send(JSON.stringify({
                type: 'auth',
                user_id: userId
            }));
            
            // Update online status
            updateOnlineStatus(true);
            
            // Join current conversation if any
            if (messagingState.currentConversationId) {
                joinConversation(messagingState.currentConversationId);
            }
        };
        
        messagingState.ws.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                handleWebSocketMessage(data);
            } catch (error) {
                console.error('Error parsing WebSocket message:', error);
            }
        };
        
        messagingState.ws.onerror = (error) => {
            console.error('WebSocket error:', error);
        };
        
        messagingState.ws.onclose = () => {
            console.log('WebSocket disconnected');
            messagingState.ws = null;
            
            // Update offline status
            updateOnlineStatus(false);
            
            // Attempt to reconnect
            if (messagingState.wsReconnectAttempts < messagingState.maxReconnectAttempts) {
                messagingState.wsReconnectAttempts++;
                const delay = messagingState.reconnectDelay * messagingState.wsReconnectAttempts;
                console.log(`Reconnecting in ${delay}ms (attempt ${messagingState.wsReconnectAttempts})...`);
                setTimeout(() => {
                    connectWebSocket();
                }, delay);
            } else {
                console.error('Max reconnection attempts reached. Falling back to polling.');
                // Fallback to polling if WebSocket fails
                startPollingFallback();
            }
        };
        
    } catch (error) {
        console.error('Error creating WebSocket connection:', error);
        // Fallback to polling
        startPollingFallback();
    }
    
    // Update on visibility change
    document.addEventListener('visibilitychange', () => {
        if (messagingState.ws && messagingState.ws.readyState === WebSocket.OPEN) {
            updateOnlineStatus(!document.hidden);
        }
    });
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
                    renderMessages();
                    // Auto-scroll to bottom when new message arrives
                    setTimeout(() => scrollToBottom(true), 100);
                } else {
                    console.log('Message already exists:', newMessage.id);
                }
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

// Join a conversation via WebSocket
function joinConversation(conversationId) {
    if (messagingState.ws && messagingState.ws.readyState === WebSocket.OPEN) {
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
    console.warn('Using polling fallback for real-time updates');
    setInterval(() => {
        if (messagingState.currentConversationId) {
            loadMessages(messagingState.currentConversationId, 1);
        }
        loadConversations();
    }, 2000);
}

async function updateOnlineStatus(isOnline) {
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
        
        // Also update via WebSocket if connected
        if (messagingState.ws && messagingState.ws.readyState === WebSocket.OPEN) {
            messagingState.ws.send(JSON.stringify({
                type: 'online_status',
                is_online: isOnline
            }));
        }
    } catch (error) {
        console.error('Error updating status:', error);
    }
}

// Initialize inactive timeout tracking
function initInactiveTimeout() {
    const INACTIVE_TIMEOUT = 5 * 60 * 1000; // 5 minutes in milliseconds
    
    // Reset activity time on user interaction
    const activityEvents = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
    activityEvents.forEach(event => {
        document.addEventListener(event, () => {
            messagingState.lastActivityTime = Date.now();
            // Reset inactive timeout
            if (messagingState.inactiveTimeout) {
                clearTimeout(messagingState.inactiveTimeout);
            }
            // Set user as online if they were offline
            updateOnlineStatus(true);
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
            updateOnlineStatus(true);
        }
    });
    
    // Set initial online status
    updateOnlineStatus(true);
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

// Message options functions
function showMessageOptions(messageId) {
    const options = document.getElementById(`messageOptions_${messageId}`);
    if (options) {
        options.style.display = 'inline-flex';
    }
}

function hideMessageOptions(messageId) {
    const options = document.getElementById(`messageOptions_${messageId}`);
    if (options) {
        options.style.display = 'none';
    }
}

async function confirmDeleteMessage(messageId) {
    if (!confirm('Are you sure you want to delete this message? This action cannot be undone.')) {
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
    if (!e.target.closest('.conversation-options-container')) {
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
window.showMessageOptions = showMessageOptions;
window.hideMessageOptions = hideMessageOptions;
window.toggleConversationOptions = toggleConversationOptions;

// Export for onclick handlers
window.selectConversation = selectConversation;
window.openInfoSidebar = openInfoSidebar;
window.scrollToMessage = scrollToMessage;
window.unarchiveConversation = unarchiveConversation;
window.toggleMemberCheckbox = toggleMemberCheckbox;
window.updateSelectedMembers = updateSelectedMembers;
window.loadAvailableMembers = loadAvailableMembers;

