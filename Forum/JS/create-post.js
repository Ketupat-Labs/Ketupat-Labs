let postState = {
    forums: [],
    selectedFiles: [],
    tags: [],
    pollOptions: []
};

document.addEventListener('DOMContentLoaded', () => {
    // Check if user is logged in
    if (sessionStorage.getItem('userLoggedIn') !== 'true') {
        window.location.href = '../login.html';
        return;
    }

    initEventListeners();
    loadForums();
    initializePollOptions();
    
    // Set initial post type
    const initialPostType = document.querySelector('input[name="postType"]:checked');
    if (initialPostType) {
        handlePostTypeChange({ target: initialPostType });
    }
});

function initEventListeners() {
    // Post type radio buttons
    document.querySelectorAll('input[name="postType"]').forEach(radio => {
        radio.addEventListener('change', handlePostTypeChange);
    });

    // Tags input
    const tagsInput = document.getElementById('tagsInput');
    if (tagsInput) {
        tagsInput.addEventListener('keypress', handleTagInput);
    }

    // File input
    const attachmentInput = document.getElementById('attachmentInput');
    if (attachmentInput) {
        attachmentInput.addEventListener('change', handleFileSelect);
    }

    // Poll option add button
    const addPollOption = document.getElementById('addPollOption');
    if (addPollOption) {
        addPollOption.addEventListener('click', addPollOptionField);
    }

    // Form submission
    const form = document.getElementById('createPostForm');
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }
}

async function loadForums() {
    try {
        const response = await fetch('../api/forum_endpoints.php?action=get_forum', {
            method: 'GET',
            credentials: 'include'
        });
        
        // Check if response is OK
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Get response text first to check if it's JSON
        const responseText = await response.text();
        
        // Check if response is HTML (PHP error)
        if (responseText.trim().startsWith('<')) {
            console.error('PHP Error Response:', responseText);
            showError('Server error occurred. Please check the console for details.');
            return;
        }
        
        // Parse as JSON
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON Parse Error:', parseError);
            console.error('Response Text:', responseText);
            showError('Invalid response from server. Please try again.');
            return;
        }
        
        if (data.status === 200 && data.data) {
            // Handle both 'forum' (singular) and 'forums' (plural) response keys
            const forums = data.data.forum || data.data.forums || [];
            if (Array.isArray(forums)) {
                postState.forums = forums;
                renderForumsDropdown();
                
                if (forums.length === 0) {
                    showError('You are not a member of any forums. Please join a forum from the forum list page first.');
                }
            } else {
                showError('Invalid forums data received');
            }
        } else {
            showError(data.message || 'Failed to load forums');
        }
    } catch (error) {
        console.error('Error loading forums:', error);
        showError('Failed to load forums. Please refresh the page and try again.');
    }
}

function renderForumsDropdown() {
    const select = document.getElementById('forumSelect');
    if (!select) return;

    // Clear existing options except the first one
    select.innerHTML = '<option value="">Choose a forum...</option>';

    postState.forums.forEach(forum => {
        const option = document.createElement('option');
        option.value = forum.id;
        option.textContent = forum.title;
        select.appendChild(option);
    });
}

function handlePostTypeChange(e) {
    const postType = e.target.value;
    
    // Update selected state
    document.querySelectorAll('.category-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    
    if (postType === 'post') {
        document.getElementById('postTypePost').classList.add('selected');
        document.getElementById('contentGroup').style.display = 'block';
        document.getElementById('linkGroup').style.display = 'none';
        document.getElementById('pollGroup').style.display = 'none';
        document.getElementById('postContent').required = true;
        document.getElementById('postLink').required = false;
    } else if (postType === 'link') {
        document.getElementById('postTypeLink').classList.add('selected');
        document.getElementById('contentGroup').style.display = 'block';
        document.getElementById('linkGroup').style.display = 'block';
        document.getElementById('pollGroup').style.display = 'none';
        document.getElementById('postContent').required = true;
        document.getElementById('postLink').required = true;
    } else if (postType === 'poll') {
        document.getElementById('postTypePoll').classList.add('selected');
        document.getElementById('contentGroup').style.display = 'none';
        document.getElementById('linkGroup').style.display = 'none';
        document.getElementById('pollGroup').style.display = 'block';
        document.getElementById('postContent').required = false;
        document.getElementById('postLink').required = false;
    }
}

function initializePollOptions() {
    // Add initial poll options
    addPollOptionField();
    addPollOptionField();
}

function addPollOptionField() {
    const container = document.getElementById('pollOptionsContainer');
    if (!container) return;

    const optionId = Date.now();
    const optionDiv = document.createElement('div');
    optionDiv.className = 'poll-option-item';
    optionDiv.id = `pollOption_${optionId}`;
    optionDiv.innerHTML = `
        <input type="text" class="form-control poll-option-input" placeholder="Enter poll option" required>
        <button type="button" class="remove-btn" onclick="removePollOption(${optionId})">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(optionDiv);
    
    postState.pollOptions.push(optionId);
}

function removePollOption(optionId) {
    const optionDiv = document.getElementById(`pollOption_${optionId}`);
    if (optionDiv) {
        optionDiv.remove();
        postState.pollOptions = postState.pollOptions.filter(id => id !== optionId);
    }
}

function handleTagInput(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const input = e.target;
        const tag = input.value.trim();
        
        if (tag && !postState.tags.includes(tag)) {
            postState.tags.push(tag);
            renderTags();
            input.value = '';
        }
    }
}

function removeTag(tag) {
    postState.tags = postState.tags.filter(t => t !== tag);
    renderTags();
}

function renderTags() {
    const container = document.getElementById('tagsContainer');
    if (!container) return;

    container.innerHTML = postState.tags.map(tag => `
        <div class="tag-pill">
            <span>${escapeHtml(tag)}</span>
            <span class="remove-tag" onclick="removeTag('${escapeHtml(tag)}')">
                <i class="fas fa-times"></i>
            </span>
        </div>
    `).join('');
}

function handleFileSelect(e) {
    const files = Array.from(e.target.files);
    const maxSize = 10 * 1024 * 1024; // 10MB
    
    files.forEach(file => {
        // Validate file size
        if (file.size > maxSize) {
            showError(`File "${file.name}" exceeds 10MB limit`);
            return;
        }
        
        // Validate file type (images and common file types)
        const validTypes = [
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        
        const validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx'];
        const fileExtension = file.name.split('.').pop().toLowerCase();
        
        if (!validTypes.includes(file.type) && !validExtensions.includes(fileExtension)) {
            showError(`File "${file.name}" is not a valid image or file type`);
            return;
        }
        
        // Add to selected files
        if (!postState.selectedFiles.find(f => f.name === file.name && f.size === file.size)) {
            postState.selectedFiles.push(file);
        }
    });
    
    renderAttachments();
}

function removeAttachment(index) {
    postState.selectedFiles.splice(index, 1);
    renderAttachments();
    
    // Update file input
    const input = document.getElementById('attachmentInput');
    if (input) {
        input.value = '';
    }
}

function renderAttachments() {
    const container = document.getElementById('attachmentsPreview');
    if (!container) return;

    if (postState.selectedFiles.length === 0) {
        container.innerHTML = '';
        return;
    }

    container.innerHTML = postState.selectedFiles.map((file, index) => {
        const fileSize = (file.size / 1024 / 1024).toFixed(2);
        const fileIcon = getFileIcon(file.name);
        
        return `
            <div class="attachment-preview-item">
                <i class="fas ${fileIcon}"></i>
                <span>${escapeHtml(file.name)}</span>
                <span class="file-size">(${fileSize} MB)</span>
                <span class="remove-btn" onclick="removeAttachment(${index})">
                    <i class="fas fa-times"></i>
                </span>
            </div>
        `;
    }).join('');
}

function getFileIcon(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    const icons = {
        'pdf': 'fa-file-pdf',
        'doc': 'fa-file-word',
        'docx': 'fa-file-word',
        'jpg': 'fa-file-image',
        'jpeg': 'fa-file-image',
        'png': 'fa-file-image',
        'gif': 'fa-file-image',
        'webp': 'fa-file-image'
    };
    return icons[ext] || 'fa-file';
}

async function handleFormSubmit(e) {
    e.preventDefault();
    
    hideMessages();
    
    // Get form values
    const forumId = document.getElementById('forumSelect').value;
    const postType = document.querySelector('input[name="postType"]:checked').value;
    const title = document.getElementById('postTitle').value.trim();
    const content = document.getElementById('postContent').value.trim();
    const link = document.getElementById('postLink').value.trim();
    
    // Validation
    if (!forumId) {
        showError('Please select a forum');
        return;
    }
    
    if (!title) {
        showError('Post title is required');
        return;
    }
    
    if (postType === 'poll') {
        const pollOptions = getPollOptions();
        if (pollOptions.length < 2) {
            showError('Poll must have at least 2 options');
            return;
        }
    } else {
        if (!content) {
            showError('Content is required');
            return;
        }
        if (content.length < 10) {
            showError('Content must be at least 10 characters');
            return;
        }
        if (postType === 'link' && !link) {
            showError('URL link is required for link posts');
            return;
        }
    }
    
    // Disable submit button
    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
    
    try {
        // Upload files first if any
        let attachments = [];
        if (postState.selectedFiles.length > 0) {
            attachments = await uploadFiles();
        }
        
        // Prepare post data
        const postData = {
            forum_id: parseInt(forumId),
            title: title,
            content: postType === 'link' ? link : content,
            post_type: postType,
            tags: postState.tags,
            attachments: attachments
        };
        
        if (postType === 'poll') {
            postData.poll_option = getPollOptions();
        }
        
        // Create post
        const response = await fetch('../api/forum_endpoints.php?action=create_post', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify(postData)
        });
        
        const data = await response.json();
        
        if (data.status === 200) {
            showSuccess('Post created successfully!');
            setTimeout(() => {
                window.location.href = `post-detail.html?id=${data.data.post_id}`;
            }, 1500);
        } else {
            showError(data.message || 'Failed to create post');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check"></i> Create Post';
        }
    } catch (error) {
        console.error('Error creating post:', error);
        showError('Failed to create post. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-check"></i> Create Post';
    }
}

async function uploadFiles() {
    if (postState.selectedFiles.length === 0) {
        return [];
    }
    
    const formData = new FormData();
    postState.selectedFiles.forEach(file => {
        formData.append('files[]', file);
    });
    
    try {
        const response = await fetch('../api/upload_endpoint.php', {
            method: 'POST',
            credentials: 'include',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.status === 200) {
            return data.data.files || [];
        } else {
            throw new Error(data.message || 'File upload failed');
        }
    } catch (error) {
        console.error('Error uploading files:', error);
        throw error;
    }
}

function getPollOptions() {
    const inputs = document.querySelectorAll('.poll-option-input');
    const options = [];
    inputs.forEach(input => {
        const value = input.value.trim();
        if (value) {
            options.push(value);
        }
    });
    return options;
}

function showSuccess(message) {
    const successMsg = document.getElementById('successMessage');
    const errorMsg = document.getElementById('errorMessage');
    
    if (successMsg) {
        successMsg.textContent = message;
        successMsg.classList.add('show');
    }
    
    if (errorMsg) {
        errorMsg.classList.remove('show');
    }
}

function showError(message) {
    const successMsg = document.getElementById('successMessage');
    const errorMsg = document.getElementById('errorMessage');
    
    if (errorMsg) {
        errorMsg.textContent = message;
        errorMsg.classList.add('show');
    }
    
    if (successMsg) {
        successMsg.classList.remove('show');
    }
}

function hideMessages() {
    const successMsg = document.getElementById('successMessage');
    const errorMsg = document.getElementById('errorMessage');
    
    if (successMsg) successMsg.classList.remove('show');
    if (errorMsg) errorMsg.classList.remove('show');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Make functions globally accessible
window.removeTag = removeTag;
window.removeAttachment = removeAttachment;
window.removePollOption = removePollOption;

