const tags = [];
const attachments = [];
const pollOptions = [];
let pollOptionCounter = 0;

function goBack() {
    const forumId = sessionStorage.getItem('currentForumId');
    if (forumId) {
        window.location.href = `forum.html#forum=${forumId}`;
    } else {
        window.location.href = 'forum.html';
    }
}

// Handle post type changes
document.querySelectorAll('input[name="postType"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const postType = this.value;
        const contentGroup = document.getElementById('contentGroup');
        const linkGroup = document.getElementById('linkGroup');
        const pollGroup = document.getElementById('pollGroup');
        const attachmentSection = document.getElementById('attachmentSection');
        const pollAddBtn = document.getElementById('addPollOption');
        
        // Reset required attributes
        document.getElementById('postContent').required = false;
        document.getElementById('postLink').required = false;
        
        if (postType === 'post') {
            contentGroup.style.display = 'block';
            linkGroup.style.display = 'none';
            pollGroup.style.display = 'none';
            attachmentSection.style.display = 'block';
            document.getElementById('postContent').required = true;
        } else if (postType === 'link') {
            contentGroup.style.display = 'none';
            linkGroup.style.display = 'block';
            pollGroup.style.display = 'none';
            attachmentSection.style.display = 'none';
            document.getElementById('postLink').required = true;
        } else if (postType === 'poll') {
            contentGroup.style.display = 'block';
            linkGroup.style.display = 'none';
            pollGroup.style.display = 'block';
            attachmentSection.style.display = 'none';
            document.getElementById('postContent').required = false;
            pollAddBtn.style.display = 'inline-flex';
            
            // Initialize with 2 poll options
            if (pollOptions.length === 0) {
                addPollOption();
                addPollOption();
            }
        }
    });
});

// Handle poll options
function addPollOption() {
    const optionId = `pollOption_${pollOptionCounter++}`;
    pollOptions.push({ id: optionId, value: '' });
    
    const container = document.getElementById('pollOptionsContainer');
    const optionDiv = document.createElement('div');
    optionDiv.className = 'poll-option-item';
    optionDiv.id = optionId;
    optionDiv.innerHTML = `
        <input type="text" placeholder="Option ${pollOptions.length}" required>
        <button type="button" class="remove-btn" onclick="removePollOption('${optionId}')">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(optionDiv);
}

function removePollOption(optionId) {
    if (pollOptions.length <= 2) {
        showError('Poll must have at least 2 options');
        return;
    }
    
    const index = pollOptions.findIndex(opt => opt.id === optionId);
    if (index > -1) {
        pollOptions.splice(index, 1);
        document.getElementById(optionId).remove();
    }
}

document.getElementById('addPollOption').addEventListener('click', addPollOption);

// Handle attachments
document.getElementById('attachmentInput').addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    
    files.forEach(file => {
        if (file.size > 10 * 1024 * 1024) {
            showError(`File ${file.name} exceeds 10MB limit`);
            return;
        }
        
        attachments.push(file);
        renderAttachments();
    });
    
    // Reset input to allow selecting same file again
    e.target.value = '';
});

function removeAttachment(index) {
    attachments.splice(index, 1);
    renderAttachments();
}

function renderAttachments() {
    const container = document.getElementById('attachmentsPreview');
    
    if (attachments.length === 0) {
        container.innerHTML = '';
        return;
    }
    
    container.innerHTML = attachments.map((file, index) => {
        const fileIcon = getFileIcon(file.name);
        return `
            <div class="attachment-preview-item">
                <i class="fas ${fileIcon}"></i>
                <span>${escapeHtml(file.name)}</span>
                <span class="remove-btn" onclick="removeAttachment(${index})">
                    <i class="fas fa-times"></i>
                </span>
            </div>
        `;
    }).join('');
}

function getFileIcon(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    const iconMap = {
        'pdf': 'fa-file-pdf',
        'doc': 'fa-file-word',
        'docx': 'fa-file-word',
        'xls': 'fa-file-excel',
        'xlsx': 'fa-file-excel',
        'ppt': 'fa-file-powerpoint',
        'pptx': 'fa-file-powerpoint',
        'zip': 'fa-file-archive',
        'rar': 'fa-file-archive',
        'jpg': 'fa-file-image',
        'jpeg': 'fa-file-image',
        'png': 'fa-file-image',
        'gif': 'fa-file-image',
        'mp4': 'fa-file-video',
        'avi': 'fa-file-video',
        'mov': 'fa-file-video',
        'mp3': 'fa-file-audio',
        'wav': 'fa-file-audio'
    };
    return iconMap[ext] || 'fa-file';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function handleTagInput(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        const input = document.getElementById('tagsInput');
        const value = input.value.trim();
        
        if (value && !tags.includes(value)) {
            tags.push(value);
            renderTags();
            input.value = '';
        }
    }
}

function removeTag(tagToRemove) {
    const index = tags.indexOf(tagToRemove);
    if (index > -1) {
        tags.splice(index, 1);
        renderTags();
    }
}

function renderTags() {
    const container = document.getElementById('tagsContainer');
    if (tags.length === 0) {
        container.innerHTML = '';
        return;
    }
    
    container.innerHTML = tags.map(tag => `
        <span class="tag-pill">
            ${tag}
            <i class="fas fa-times" onclick="removeTag('${tag}')"></i>
        </span>
    `).join('');
}

// Category radio button styling
document.querySelectorAll('input[name="category"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.category-option').forEach(opt => {
            opt.classList.remove('selected');
        });
        if (this.checked) {
            this.closest('.category-option').classList.add('selected');
        }
    });
});

function showSuccess() {
    const successDiv = document.getElementById('successMessage');
    successDiv.classList.add('show');
    successDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    
    setTimeout(() => {
        goBack();
    }, 2000);
}

function showError(message) {
    const errorDiv = document.getElementById('errorMessage');
    errorDiv.textContent = message;
    errorDiv.classList.add('show');
    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    
    setTimeout(() => {
        errorDiv.classList.remove('show');
    }, 5000);
}

// Load forums for selection
async function loadForums() {
    try {
        const response = await fetch('../api/forum_endpoints.php?action=get_forums&sort=name');
        const data = await response.json();
        
        if (data.status === 200 && data.data.forums) {
            const select = document.getElementById('forumSelect');
            data.data.forums.forEach(forum => {
                const option = document.createElement('option');
                option.value = forum.id;
                option.textContent = forum.title;
                select.appendChild(option);
            });
            
            // Pre-select forum if coming from a specific forum
            const urlParams = new URLSearchParams(window.location.search);
            const forumId = urlParams.get('forum');
            if (forumId) {
                select.value = forumId;
            }
        }
    } catch (error) {
        console.error('Error loading forums:', error);
    }
}

document.getElementById('createPostForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const forumId = document.getElementById('forumSelect').value;
    const title = document.getElementById('postTitle').value.trim();
    const content = document.getElementById('postContent').value.trim();
    const postType = document.querySelector('input[name="postType"]:checked')?.value || 'post';
    const postLink = document.getElementById('postLink').value.trim();
    
    // Get poll options
    const pollOptionInputs = document.querySelectorAll('#pollOptionsContainer input');
    const pollOptionValues = Array.from(pollOptionInputs)
        .map(input => input.value.trim())
        .filter(val => val.length > 0);
    
    if (!forumId) {
        showError('Please select a forum.');
        return;
    }

    // Validate based on post type
    if (postType === 'post') {
        if (content.length < 10) {
            showError('Content must be at least 10 characters long.');
            return;
        }
    } else if (postType === 'link') {
        if (!postLink) {
            showError('Please enter a valid URL.');
            return;
        }
        if (content.length < 10) {
            showError('Content must be at least 10 characters long.');
            return;
        }
    } else if (postType === 'poll') {
        if (pollOptionValues.length < 2) {
            showError('Poll must have at least 2 options.');
            return;
        }
    }

    try {
        // First, upload attachments if any
        const uploadedAttachments = [];
        if (attachments.length > 0) {
            const formData = new FormData();
            attachments.forEach(file => {
                formData.append('files[]', file);
            });
            
            const uploadResponse = await fetch('../api/upload_endpoint.php', {
                method: 'POST',
                body: formData
            });
            
            const uploadData = await uploadResponse.json();
            
            if (uploadData.status === 200 && uploadData.data.files) {
                uploadedAttachments.push(...uploadData.data.files);
            } else {
                showError('Failed to upload attachments. Please try again.');
                return;
            }
        }
        
        // Create the post
        const response = await fetch('../api/forum_endpoints.php?action=create_post', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                forum_id: forumId,
                title,
                content: postType === 'link' ? `${postLink}\n\n${content}` : content,
                tags,
                attachments: uploadedAttachments,
                post_type: postType,
                poll_options: postType === 'poll' ? pollOptionValues : null
            })
        });
        
        const data = await response.json();
        
        if (data.status === 200) {
            sessionStorage.setItem('lastViewedForum', forumId);
            showSuccess();
        } else {
            showError(data.message || 'Failed to create post. Please try again.');
        }
    } catch (error) {
        console.error('Error creating post:', error);
        showError('An error occurred. Please check your connection and try again.');
    }
});

// Check if user is logged in
if (sessionStorage.getItem('userLoggedIn') !== 'true') {
    window.location.href = 'login.html';
}

// Load forums on page load
loadForums();

// Make functions globally accessible
window.removeAttachment = removeAttachment;
window.removePollOption = removePollOption;
window.removeTag = removeTag;
window.handleTagInput = handleTagInput;
window.goBack = goBack;

