const messagingState = {
    currentConversationId: null,
    conversations: [],
    messages: [],
    typingTimeout: null,
    isTyping: false
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
    startRealTimeUpdates();
    
    const messageInput = document.getElementById('messageInput');
    if (messageInput) {
        messageInput.addEventListener('input', autoResizeTextarea);
    }
});

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
        </div>
    `).join('');
}

async function selectConversation(conversationId) {
    messagingState.currentConversationId = conversationId;
    await loadMessages(conversationId);
    loadConversationDetails(conversationId);
    renderConversations();
    document.getElementById('chatInputContainer').style.display = 'block';
    
    setTimeout(() => {
        const messagesContainer = document.getElementById('chatMessages');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }, 100);
}

async function loadMessages(conversationId, page = 1) {
    try {
        const response = await fetch(`../api/messaging_endpoints.php?action=get_messages&conversation_id=${conversationId}&page=${page}`, {
            method: 'GET',
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.status === 200) {
            if (page === 1) {
                messagingState.messages = data.data.messages || [];
            } else {
                messagingState.messages = [...(data.data.messages || []), ...messagingState.messages];
            }
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
    
    if (messagingState.messages.length === 0) {
        container.innerHTML = '<div style="text-align: center; padding: 40px; color: #999;">Tiada mesej lagi. Mulakan perbualan!</div>';
        return;
    }
    
    const currentUserId = getCurrentUserId();
    
    container.innerHTML = messagingState.messages.map((msg, index) => {
        const isOwn = msg.sender_id === currentUserId;
        const isGrouped = index > 0 && 
                         messagingState.messages[index - 1].sender_id === msg.sender_id &&
                         (new Date(msg.created_at) - new Date(messagingState.messages[index - 1].created_at)) < 300000; // 5 minutes
        
        return `
            ${!isGrouped ? `
                <div class="message-group">
                    <div class="message ${isOwn ? 'own' : ''}">
                        <div class="message-avatar">
                            ${msg.avatar_url ? 
                                `<img src="${msg.avatar_url}" alt="${msg.username}">` :
                                msg.username.charAt(0).toUpperCase()
                            }
                        </div>
                        <div class="message-content">
                            ${isOwn ? '' : `<div style="font-size: 12px; color: #666; margin-bottom: 5px;">${msg.full_name || msg.username}</div>`}
                            <div class="message-bubble">
                                ${msg.message_type === 'text' ? 
                                    `<div>${escapeHtml(msg.content)}</div>` :
                                    `<div class="message-attachment">
                                        <a href="${msg.attachment_url}" target="_blank" class="attachment-file">
                                            <i class="fas ${getFileIcon(msg.attachment_name)}"></i>
                                            <span>${escapeHtml(msg.attachment_name)}</span>
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
            ` : ''}
        `;
    }).join('');
}

async function sendMessage() {
    const input = document.getElementById('messageInput');
    const content = input.value.trim();
    
    if (!content || !messagingState.currentConversationId) return;
    
    const sendBtn = document.getElementById('btnSend');
    sendBtn.disabled = true;
    
    try {
        const response = await fetch('../api/messaging_endpoints.php?action=send_message', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({
                conversation_id: messagingState.currentConversationId,
                content: content,
                message_type: 'text'
            })
        });
        
        const data = await response.json();
        
        if (data.status === 200) {
            input.value = '';
            input.style.height = 'auto';
            
            await loadMessages(messagingState.currentConversationId);
            
            await loadConversations();
        } else {
            showError(data.message || 'Gagal menghantar mesej');
        }
    } catch (error) {
        console.error('Error sending message:', error);
        showError('Gagal menghantar mesej. Sila cuba lagi.');
    } finally {
        sendBtn.disabled = false;
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
                await loadMessages(messagingState.currentConversationId);
                await loadConversations();
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
    console.log('Searching:', keyword);
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
    content.innerHTML = `
        <div>
            <h4>Group Members</h4>
            <div class="member-list">
                ${conversation.members ? JSON.parse(conversation.members).map(member => `
                    <div class="member-item">
                        <div class="conversation-avatar">
                            ${member.avatar_url ? 
                                `<img src="${member.avatar_url}" alt="${member.username}">` :
                                member.username.charAt(0).toUpperCase()
                            }
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600;">${member.full_name || member.username}</div>
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

// Typing indicator
function handleTyping() {
    if (!messagingState.isTyping) {
        messagingState.isTyping = true;
        // Send typing indicator to server
        // This would typically use WebSocket
    }
    
    clearTimeout(messagingState.typingTimeout);
    messagingState.typingTimeout = setTimeout(() => {
        messagingState.isTyping = false;
    }, 1000);
}

// Real-time updates
function startRealTimeUpdates() {
    // Poll for new messages every 2 seconds
    setInterval(() => {
        if (messagingState.currentConversationId) {
            loadMessages(messagingState.currentConversationId, 1);
        }
        loadConversations();
    }, 2000);
    
    // Update online status
    updateOnlineStatus(true);
    
    // Update on visibility change
    document.addEventListener('visibilitychange', () => {
        updateOnlineStatus(!document.hidden);
    });
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

