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
    currentTab: 'active', // 'active' or 'archived'
    searchResults: []
};

document.addEventListener('DOMContentLoaded', () => {
    // Check if user is logged in
    const userLoggedIn = sessionStorage.getItem('userLoggedIn');
    if (userLoggedIn !== 'true') {
        window.location.href = '../login.html';
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
    try {
        const response = await fetch('../api/auth/me.php', { credentials: 'include' });
        if (!response.ok) return;
        const data = await response.json();
        if (data.status === 200 && data.data?.user) {
            const user = data.data.user;
            const displayName = user.full_name || user.username || 'User';

            const userNameElement = document.getElementById('userName');
            if (userNameElement) userNameElement.textContent = displayName;

            const mobileUserName = document.getElementById('mobileUserName');
            if (mobileUserName) mobileUserName.textContent = displayName;

            const mobileUserEmail = document.getElementById('mobileUserEmail');
            if (mobileUserEmail) mobileUserEmail.textContent = user.email || '';
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
            window.location.href = '../login.html';
            return;
        }
        
        // Load based on current tab
        if (messagingState.currentTab === 'archived') {
            await loadArchivedConversations();
            return;
        }
        
        const sortElement = document.getElementById('sortConversations');
        const sort = sortElement ? sortElement.value : 'recent';
        const response = await fetch(`../api/messaging_endpoints.php?action=get_conversations&sort=${sort}`, {
            method: 'GET',
            credentials: 'include'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.status === 200 || data.status === 401) {
            // 200 = success, 401 = unauthorized (will redirect)
            if (data.status === 401) {
                window.location.href = '../login.html';
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
    
    container.innerHTML = messagingState.conversations.map(conv => `
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
            ${messagingState.currentTab === 'archived' ? `
                <div class="conversation-actions" onclick="event.stopPropagation(); unarchiveConversation(${conv.id})">
                    <button class="btn-unarchive" title="Unarchive">
                        <i class="fas fa-inbox"></i>
                    </button>
                </div>
            ` : ''}
        </div>
    `).join('');
}

async function unarchiveConversation(conversationId) {
    try {
        const response = await fetch('../api/messaging_endpoints.php?action=archive_conversation', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
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
    // Leave previous conversation
    if (messagingState.currentConversationId) {
        leaveConversation(messagingState.currentConversationId);
    }
    
    messagingState.currentConversationId = conversationId;
    await loadMessages(conversationId);
    loadConversationDetails(conversationId);
    renderConversations();
    document.getElementById('chatInputContainer').style.display = 'block';
    document.getElementById('chatActionsBar').style.display = 'flex';
    
    // Join new conversation via WebSocket
    joinConversation(conversationId);
    
    // Clear typing indicators
    messagingState.typingUsers.clear();
    updateTypingIndicatorDisplay();
    
    // Close search if open
    closeSearchMessages();
    
    // Scroll to bottom after messages load
    setTimeout(() => {
        scrollToBottom();
    }, 100);
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
        const response = await fetch(`../api/messaging_endpoints.php?action=get_messages&conversation_id=${conversationId}&page=${page}`, {
            method: 'GET',
            credentials: 'include'
        });
        const data = await response.json();
        
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

function renderMessages() {
    const container = document.getElementById('chatMessages');
    
    if (!container) return;
    
    if (messagingState.messages.length === 0) {
        container.innerHTML = '<div style="text-align: center; padding: 40px; color: #999; margin-top: auto;">Tiada mesej lagi. Mulakan perbualan!</div>';
        return;
    }
    
    // Store scroll position before rendering
    const wasAtBottom = isScrolledToBottom(container);
    const previousScrollHeight = container.scrollHeight;
    
    const currentUserId = getCurrentUserId();
    
    console.log('Rendering messages:', messagingState.messages.length, 'messages');
    
    // Messages are already sorted by created_at in ascending order
    // Render ALL messages - don't skip any
    container.innerHTML = messagingState.messages.map((msg, index) => {
        const isOwn = msg.sender_id === currentUserId;
        const prevMsg = index > 0 ? messagingState.messages[index - 1] : null;
        const isGrouped = prevMsg && 
                         prevMsg.sender_id === msg.sender_id &&
                         (new Date(msg.created_at) - new Date(prevMsg.created_at)) < 300000; // 5 minutes
        
        return `
            <div class="message-group" data-message-id="${msg.id}">
                <div class="message ${isOwn ? 'own' : ''}">
                    ${!isGrouped ? `
                        <div class="message-avatar">
                            ${msg.avatar_url ? 
                                `<img src="${msg.avatar_url}" alt="${msg.username || ''}">` :
                                (msg.username ? msg.username.charAt(0).toUpperCase() : '?')
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
                                    <a href="${msg.attachment_url}" target="_blank" class="attachment-file">
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
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    // Restore scroll position or scroll to bottom
    if (wasAtBottom) {
        // User was at bottom, scroll to new bottom
        setTimeout(() => {
            scrollToBottom();
        }, 0);
    } else {
        // User was scrolling up, maintain relative position
        const newScrollHeight = container.scrollHeight;
        const scrollDifference = newScrollHeight - previousScrollHeight;
        if (scrollDifference > 0) {
            container.scrollTop += scrollDifference;
        }
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
        
        const response = await fetch('../api/messaging_endpoints.php?action=send_message', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
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
        const response = await fetch('../api/upload_endpoint.php', {
            method: 'POST',
            credentials: 'include',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.status === 200) {
            const sendResponse = await fetch('../api/messaging_endpoints.php?action=send_message', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
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
        const response = await fetch(`../api/messaging_endpoints.php?action=search_messages&conversation_id=${conversationId}&keyword=${encodeURIComponent(keyword)}`, {
            method: 'GET',
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
        const response = await fetch('../api/messaging_endpoints.php?action=archive_conversation', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
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
        const response = await fetch('../api/messaging_endpoints.php?action=get_archived_conversations', {
            method: 'GET',
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
        const response = await fetch('../api/messaging_endpoints.php?action=create_group_chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
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
    loadAvailableMembers();
}

function closeCreateGroupModal() {
    document.getElementById('createGroupModal').classList.remove('active');
    document.getElementById('createGroupForm').reset();
    document.getElementById('selectedMembers').innerHTML = '';
}

async function loadAvailableMembers() {
    const container = document.getElementById('membersSelector');
    container.innerHTML = '<div class="loading"></div>';
    
    setTimeout(() => {
        container.innerHTML = `
            <div class="member-checkbox">
                <input type="checkbox" id="member1" value="2">
                <label for="member1">Student 1</label>
            </div>
            <div class="member-checkbox">
                <input type="checkbox" id="member2" value="3">
                <label for="member2">Student 2</label>
            </div>
        `;
    }, 500);
}

function loadConversationDetails(conversationId) {
    const conversation = messagingState.conversations.find(c => c.id === conversationId);
    if (!conversation) return;
    
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
            ${conversation.type === 'group' ? 
                '<button class="btn-icon" onclick="openInfoSidebar()"><i class="fas fa-info-circle"></i></button>' : 
                ''
            }
        </div>
    `;
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
    
    content.innerHTML = `
        <div>
            <h4>Group Members</h4>
            <div class="member-list">
                ${members.length > 0 ? members.map(member => `
                    <div class="member-item">
                        <div class="conversation-avatar">
                            ${member.avatar_url ? 
                                `<img src="${member.avatar_url}" alt="${member.username || ''}">` :
                                (member.username ? member.username.charAt(0).toUpperCase() : '?')
                            }
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600;">${escapeHtml(member.full_name || member.username || 'Unknown')}</div>
                            ${member.is_online ? '<span style="font-size: 12px; color: #4CAF50;">Online</span>' : ''}
                        </div>
                    </div>
                `).join('') : '<p>No members found</p>'}
            </div>
        </div>
    `;
}

function openInfoSidebar() {
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
    updateTypingIndicatorDisplay();
}

// Hide typing indicator for a user
function hideTypingIndicator(userId) {
    messagingState.typingUsers.delete(userId);
    updateTypingIndicatorDisplay();
}

// Update typing indicator display
function updateTypingIndicatorDisplay() {
    const indicator = document.getElementById('typingIndicator');
    const typingUser = document.getElementById('typingUser');
    
    if (!indicator) return;
    
    if (messagingState.typingUsers.size > 0) {
        // Get user info for typing users (simplified - you may want to fetch actual names)
        const userCount = messagingState.typingUsers.size;
        if (typingUser) {
            typingUser.textContent = userCount === 1 ? 'Someone' : `${userCount} people`;
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
        await fetch('../api/messaging_endpoints.php?action=update_online_status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ is_online: isOnline })
        });
    } catch (error) {
        console.error('Error updating status:', error);
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

// Export for onclick handlers
window.selectConversation = selectConversation;
window.openInfoSidebar = openInfoSidebar;
window.scrollToMessage = scrollToMessage;
window.unarchiveConversation = unarchiveConversation;

