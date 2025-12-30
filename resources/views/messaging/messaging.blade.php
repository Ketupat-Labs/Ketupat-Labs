<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Messaging - Material Learning Platform</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/images/LOGOCompuPlay.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/LOGOCompuPlay.png') }}">
    
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('Forum/CSS/messaging.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js for interactive components -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 min-h-screen font-['Inter']">
    <div class="min-h-screen flex flex-col">
        @php
            $currentUser = session('user_id') ? \App\Models\User::find(session('user_id')) : \Illuminate\Support\Facades\Auth::user();
        @endphp
        @include('layouts.navigation', ['currentUser' => $currentUser])

        <main class="flex-1" style="padding: 0; margin: 0;">
            <div class="messaging-shell" style="max-width: 100%; margin: 0;">

                <div class="messaging-container">
                    <aside class="conversations-sidebar">
                        <div class="sidebar-header">
                            <h2><i class="fas fa-comments"></i> Messages</h2>
                            <button class="btn-create-group" id="btnCreateConversation" title="Create Conversation">
                                <i class="fas fa-plus-circle"></i>
                            </button>
                        </div>
                        <div class="search-bar">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchConversations" placeholder="Search conversations...">
                        </div>
                        <div class="sort-filter">
                            <select id="sortConversations">
                                <option value="recent">Most Recent</option>
                                <option value="oldest">Oldest</option>
                                <option value="unread">Unread</option>
                            </select>
                        </div>
                        <div class="conversation-tabs">
                            <button class="tab-btn active" id="tabActive" data-tab="active">Active</button>
                            <button class="tab-btn" id="tabArchived" data-tab="archived">Archived</button>
                        </div>
                        <div class="conversations-list" id="conversationsList"></div>
                    </aside>

                    <section class="chat-main">
                        <div class="chat-header" id="chatHeader">
                            <div class="chat-header-placeholder">
                                <i class="fas fa-comment-dots"></i>
                                <p>Select a conversation to start chatting</p>
                            </div>
                        </div>
                        <div class="chat-actions-bar" id="chatActionsBar" style="display: none;">
                            <button class="btn-chat-action" id="btnSearchMessages" title="Search Messages">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <button class="btn-chat-action" id="btnArchiveConversation" title="Archive Conversation">
                                <i class="fas fa-archive"></i> Archive
                            </button>
                        </div>
                        <div class="search-messages-container" id="searchMessagesContainer" style="display: none;">
                            <div class="search-messages-header">
                                <input type="text" id="searchMessagesInput" placeholder="Search messages in this conversation...">
                                <button class="btn-close-search" id="btnCloseSearch">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="search-results" id="searchResults"></div>
                        </div>
                        <div class="chat-messages" id="chatMessages"></div>
                        <div class="chat-input-container" id="chatInputContainer" style="display: none;">
                            <div class="typing-indicator" id="typingIndicator" style="display: none;">
                                <span id="typingUser"></span> is typing...
                            </div>
                            <div class="chat-input-wrapper">
                                <button class="btn-attach" id="btnAttach" title="Attach File">
                                    <i class="fas fa-paperclip"></i>
                                </button>
                                <input type="file" id="fileInput" style="display: none;" multiple>
                                <textarea id="messageInput" placeholder="Type your message..." rows="1"></textarea>
                                <button class="btn-send" id="btnSend" title="Send">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </section>

                    <aside class="info-sidebar" id="infoSidebar" style="display: none;">
                        <div class="info-header">
                            <h3 id="infoTitle">Group Info</h3>
                            <button class="btn-close-info" id="btnCloseInfo">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="info-content" id="infoContent"></div>
                    </aside>
                </div>
            </div>
        </main>
    </div>

    <!-- Conversation Type Selection Modal -->
    <div class="modal" id="createConversationModal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2>Create Conversation</h2>
                <button class="modal-close" id="closeConversationModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="conversation-type-selector" style="display: flex; flex-direction: column; gap: 1rem;">
                    <button type="button" class="conversation-type-btn" onclick="openCreateDMModal()" style="padding: 1.5rem; border: 2px solid #e5e7eb; border-radius: 0.75rem; background: white; cursor: pointer; text-align: left; transition: all 0.2s;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #1877f2 0%, #42a5f5 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <div style="font-weight: 600; font-size: 1.1rem; color: #1f2937;">Direct Message</div>
                                <div style="font-size: 0.9rem; color: #6b7280; margin-top: 0.25rem;">Start a private conversation with a user</div>
                            </div>
                        </div>
                    </button>
                    <button type="button" class="conversation-type-btn" onclick="openCreateGroupModal()" style="padding: 1.5rem; border: 2px solid #e5e7eb; border-radius: 0.75rem; background: white; cursor: pointer; text-align: left; transition: all 0.2s;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <div style="font-weight: 600; font-size: 1.1rem; color: #1f2937;">Group Chat</div>
                                <div style="font-size: 0.9rem; color: #6b7280; margin-top: 0.25rem;">Create a group with multiple members</div>
                            </div>
                        </div>
                    </button>
                </div>
                <div class="modal-actions" style="margin-top: 1.5rem;">
                    <button type="button" class="btn-cancel" id="cancelConversationModal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create DM Modal -->
    <div class="modal" id="createDMModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>New Direct Message</h2>
                <button class="modal-close" id="closeDMModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="dmSearchUsers">Search or select a user</label>
                    <input type="text" id="dmSearchUsers" placeholder="Type to search users..." class="form-control" style="margin-bottom: 0.75rem;">
                    <div class="dm-users-list" id="dmUsersList" style="max-height: 400px; overflow-y: auto;"></div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="cancelDMModal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Group Chat Modal -->
    <div class="modal" id="createGroupModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Create Group Chat</h2>
                <button class="modal-close" id="closeGroupModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="createGroupForm">
                    <div class="form-group">
                        <label for="groupName">Group Name</label>
                        <input type="text" id="groupName" required>
                    </div>
                    <div class="form-group">
                        <label for="groupMembers">Add Members</label>
                        <input type="text" id="searchMembers" placeholder="Search users..." class="form-control" style="margin-bottom: 0.75rem;">
                        <div class="members-selector" id="membersSelector"></div>
                        <div class="selected-members" id="selectedMembers"></div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" id="cancelGroupModal">Cancel</button>
                        <button type="submit" class="btn-primary">Create Group</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('Forum/JS/messaging.js') }}"></script>
</body>
</html>
