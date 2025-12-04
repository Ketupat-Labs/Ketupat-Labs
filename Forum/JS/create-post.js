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

    // Store the ORIGINAL referrer page (where user clicked "create post" button)
    // This should be forum.html or forum-detail.html, NOT create-post.html
    const urlParams = new URLSearchParams(window.location.search);
    let originalReferrer = urlParams.get('referrer');
    
    // Safety check: Never use create-post.html as referrer
    if (originalReferrer && originalReferrer.includes('create-post.html')) {
        originalReferrer = null; // Reset if it's create-post.html
    }
    
    // If no referrer in URL parameter, try to get it from document.referrer
    // But EXCLUDE create-post.html to avoid going back to create-post page
    if (!originalReferrer) {
        const referrerUrl = document.referrer;
        if (referrerUrl && !referrerUrl.includes('create-post.html')) {
            try {
                const referrerObj = new URL(referrerUrl);
                const referrerPath = referrerObj.pathname;
                
                // Extract just the filename and query params
                const pathParts = referrerPath.split('/');
                const filename = pathParts[pathParts.length - 1];
                
                if (filename === 'forum.html' || referrerPath.includes('forum.html')) {
                    originalReferrer = 'forum.html';
                } else if (filename === 'forum-detail.html' || referrerPath.includes('forum-detail.html')) {
                    const forumId = referrerObj.searchParams.get('id');
                    originalReferrer = forumId ? `forum-detail.html?id=${forumId}` : 'forum-detail.html';
                } else if (filename === 'post-detail.html' || referrerPath.includes('post-detail.html')) {
                    // If coming from post-detail, preserve the full URL with query params
                    const queryString = referrerObj.search;
                    originalReferrer = queryString ? `post-detail.html${queryString}` : 'post-detail.html';
                }
            } catch (e) {
                // Invalid URL, ignore
                console.log('Could not parse referrer URL:', e);
            }
        }
    }
    
    // Final safety check: Never store create-post.html as referrer
    if (originalReferrer && !originalReferrer.includes('create-post.html')) {
        sessionStorage.setItem('postCreateReferrer', originalReferrer);
        console.log('Stored original referrer:', originalReferrer);
    } else if (originalReferrer) {
        console.warn('Ignored create-post.html as referrer');
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
    
    // Get poll option inputs
    const pollInputs = document.querySelectorAll('.poll-option-input');
    
    if (postType === 'post') {
        document.getElementById('postTypePost').classList.add('selected');
        document.getElementById('contentGroup').style.display = 'block';
        document.getElementById('linkGroup').style.display = 'none';
        document.getElementById('pollGroup').style.display = 'none';
        document.getElementById('postContent').required = true;
        document.getElementById('postLink').required = false;
        // Remove required from poll inputs when hidden
        pollInputs.forEach(input => input.removeAttribute('required'));
    } else if (postType === 'link') {
        document.getElementById('postTypeLink').classList.add('selected');
        document.getElementById('contentGroup').style.display = 'block';
        document.getElementById('linkGroup').style.display = 'block';
        document.getElementById('pollGroup').style.display = 'none';
        document.getElementById('postContent').required = true;
        document.getElementById('postLink').required = true;
        // Remove required from poll inputs when hidden
        pollInputs.forEach(input => input.removeAttribute('required'));
    } else if (postType === 'poll') {
        document.getElementById('postTypePoll').classList.add('selected');
        document.getElementById('contentGroup').style.display = 'none';
        document.getElementById('linkGroup').style.display = 'none';
        document.getElementById('pollGroup').style.display = 'block';
        document.getElementById('postContent').required = false;
        document.getElementById('postLink').required = false;
        // Add required to poll inputs when visible
        pollInputs.forEach(input => input.setAttribute('required', 'required'));
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
    
    // Check if poll type is currently selected
    const pollTypeSelected = document.querySelector('input[name="postType"]:checked')?.value === 'poll';
    const requiredAttr = pollTypeSelected ? 'required' : '';
    
    optionDiv.innerHTML = `
        <input type="text" class="form-control poll-option-input" placeholder="Enter poll option" ${requiredAttr}>
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

    // Clear container first
    container.innerHTML = '';

    // Render each file
    postState.selectedFiles.forEach((file, index) => {
        const fileSize = (file.size / 1024 / 1024).toFixed(2);
        const fileIcon = getFileIcon(file.name);
        const isImage = file.type.startsWith('image/') || 
                       ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(file.name.split('.').pop().toLowerCase());
        
        if (isImage) {
            // Create image preview using FileReader
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewDiv = document.createElement('div');
                previewDiv.className = 'attachment-image-preview';
                const dataUrl = e.target.result;
                const fileName = escapeHtml(file.name);
                
                // Create wrapper
                const wrapper = document.createElement('div');
                wrapper.className = 'image-preview-wrapper';
                wrapper.onclick = () => viewImagePreview(dataUrl, fileName);
                
                // Create image
                const img = document.createElement('img');
                img.src = dataUrl;
                img.alt = fileName;
                img.className = 'preview-image';
                
                // Create overlay
                const overlay = document.createElement('div');
                overlay.className = 'image-preview-overlay';
                
                const filenameSpan = document.createElement('span');
                filenameSpan.className = 'image-filename';
                filenameSpan.textContent = fileName;
                
                const filesizeSpan = document.createElement('span');
                filesizeSpan.className = 'image-filesize';
                filesizeSpan.textContent = fileSize + ' MB';
                
                const removeBtn = document.createElement('span');
                removeBtn.className = 'remove-btn';
                removeBtn.title = 'Remove';
                removeBtn.onclick = (event) => {
                    event.stopPropagation();
                    removeAttachment(index);
                };
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                
                overlay.appendChild(filenameSpan);
                overlay.appendChild(filesizeSpan);
                overlay.appendChild(removeBtn);
                
                wrapper.appendChild(img);
                wrapper.appendChild(overlay);
                previewDiv.appendChild(wrapper);
                container.appendChild(previewDiv);
            };
            reader.onerror = function() {
                // Fallback to file icon if image fails to load
                const fileDiv = document.createElement('div');
                fileDiv.className = 'attachment-preview-item';
                fileDiv.innerHTML = `
                    <i class="fas ${fileIcon}"></i>
                    <span>${escapeHtml(file.name)}</span>
                    <span class="file-size">(${fileSize} MB)</span>
                    <span class="remove-btn" onclick="removeAttachment(${index})">
                        <i class="fas fa-times"></i>
                    </span>
                `;
                container.appendChild(fileDiv);
            };
            reader.readAsDataURL(file);
        } else {
            // Non-image files - show file icon
            const fileDiv = document.createElement('div');
            fileDiv.className = 'attachment-preview-item';
            fileDiv.innerHTML = `
                <i class="fas ${fileIcon}"></i>
                <span>${escapeHtml(file.name)}</span>
                <span class="file-size">(${fileSize} MB)</span>
                <span class="remove-btn" onclick="removeAttachment(${index})">
                    <i class="fas fa-times"></i>
                </span>
            `;
            container.appendChild(fileDiv);
        }
    });
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
    
    // Verify the forum exists in the loaded forums
    const selectedForum = postState.forums.find(f => f.id == forumId);
    if (!selectedForum) {
        console.error('Selected forum not found in loaded forums:', {
            forumId: forumId,
            loadedForums: postState.forums
        });
        showError('Selected forum is invalid. Please refresh the page and try again.');
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
        
        // Debug: Log the data being sent
        console.log('Creating post with data:', {
            forum_id: postData.forum_id,
            title: postData.title,
            post_type: postData.post_type,
            has_content: !!postData.content,
            tags_count: postData.tags.length,
            attachments_count: postData.attachments.length
        });
        
        // Create post
        const response = await fetch('../api/forum_endpoints.php?action=create_post', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify(postData)
        });
        
        // Log response status for debugging
        console.log('Response status:', response.status, response.statusText);
        
        // Read response as text first (can only read once)
        let responseText;
        try {
            responseText = await response.text();
        } catch (readError) {
            console.error('Error reading response:', readError);
            showError('Failed to read server response. Please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check"></i> Create Post';
            return;
        }
        
        // Try to parse as JSON
        let data;
        const contentType = response.headers.get('content-type') || '';
        
        if (contentType.includes('application/json') || responseText.trim().startsWith('{') || responseText.trim().startsWith('[')) {
            try {
                data = JSON.parse(responseText);
                console.log('Server response:', data);
            } catch (jsonError) {
                console.error('JSON Parse Error:', jsonError);
                console.error('Response Text:', responseText.substring(0, 500));
                showError('Invalid JSON response from server. Please check the console for details.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check"></i> Create Post';
                return;
            }
        } else {
            console.error('Non-JSON response:', responseText.substring(0, 500));
            showError('Server returned non-JSON response. Please check the console for details.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check"></i> Create Post';
            return;
        }
        
        if (data.status === 200) {
            showSuccess('Post created successfully!');
            setTimeout(() => {
                // Get the ORIGINAL referrer (where they clicked "create post" button)
                // This should be forum.html or forum-detail.html, NOT create-post.html
                const originalReferrer = sessionStorage.getItem('postCreateReferrer') || '';
                const referrerParam = originalReferrer ? `&referrer=${encodeURIComponent(originalReferrer)}` : '';
                window.location.href = `post-detail.html?id=${data.data.post_id}${referrerParam}`;
            }, 1500);
        } else {
            // Extract error message - check both 'message' field and status code
            let errorMessage = data.message || 'Failed to create post';
            
            // Provide more specific messages for common errors
            if (response.status === 403) {
                if (data.message && data.message.includes('Not a member of this forum')) {
                    errorMessage = 'You are not a member of this forum. Please join the forum first before creating posts.';
                } else if (data.message && data.message.includes('muted')) {
                    // Show the detailed mute message from server
                    errorMessage = data.message || 'You are currently muted in this forum and cannot create posts.';
                    if (data.data && data.data.muted_until) {
                        const muteDate = new Date(data.data.muted_until);
                        errorMessage += ` The mute expires on ${muteDate.toLocaleString()}.`;
                    } else if (data.data && !data.data.muted_until) {
                        errorMessage += ' This is a permanent mute.';
                    }
                    if (data.data && data.data.reason) {
                        errorMessage += ` Reason: ${data.data.reason}`;
                    }
                } else {
                    errorMessage = data.message || 'Access denied. You may not have permission to create posts in this forum.';
                }
            } else if (response.status === 401) {
                errorMessage = 'Your session has expired. Please log in again.';
            }
            
            console.error('Post creation failed:', {
                status: data.status || response.status,
                message: data.message,
                fullResponse: data
            });
            showError(errorMessage);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check"></i> Create Post';
        }
    } catch (error) {
        console.error('Error creating post:', error);
        showError(`Failed to create post: ${error.message || 'Please try again.'}`);
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
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response from upload:', text.substring(0, 500));
            throw new Error('Server error during file upload. Please check the console for details.');
        }
        
        let data;
        try {
            data = await response.json();
        } catch (parseError) {
            console.error('JSON Parse Error in upload:', parseError);
            const text = await response.text();
            console.error('Upload Response Text:', text.substring(0, 500));
            throw new Error('Invalid response from upload server. Please try again.');
        }
        
        if (data.status === 200) {
            return data.data.files || [];
        } else {
            throw new Error(data.message || `File upload failed (Status: ${data.status || response.status})`);
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

// View image in full size
function viewImagePreview(imageUrl, imageName) {
    // Create a modal to view the image
    const modal = document.createElement('div');
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        cursor: pointer;
    `;
    
    const img = document.createElement('img');
    img.src = imageUrl;
    img.alt = imageName;
    img.style.cssText = `
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
        border-radius: 8px;
    `;
    
    const closeBtn = document.createElement('div');
    closeBtn.innerHTML = '<i class="fas fa-times"></i>';
    closeBtn.style.cssText = `
        position: absolute;
        top: 20px;
        right: 20px;
        color: white;
        font-size: 24px;
        cursor: pointer;
        background-color: rgba(0, 0, 0, 0.5);
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.2s;
    `;
    closeBtn.onmouseover = () => closeBtn.style.backgroundColor = 'rgba(0, 0, 0, 0.8)';
    closeBtn.onmouseout = () => closeBtn.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
    
    const closeModal = () => {
        document.body.removeChild(modal);
    };
    
    closeBtn.onclick = (e) => {
        e.stopPropagation();
        closeModal();
    };
    modal.onclick = closeModal;
    img.onclick = (e) => e.stopPropagation();
    
    modal.appendChild(img);
    modal.appendChild(closeBtn);
    document.body.appendChild(modal);
}

// Make functions globally accessible
window.removeTag = removeTag;
window.removeAttachment = removeAttachment;
window.removePollOption = removePollOption;
window.viewImagePreview = viewImagePreview;

