function showSuccess() {
    const successDiv = document.getElementById('successMessage');
    successDiv.classList.add('show');
    successDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    
    setTimeout(() => {
        window.location.href = 'forum.html';
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

function handleVisibilityChange() {
    const visibility = document.getElementById('forumVisibility').value;
    const classSelectionGroup = document.getElementById('classSelectionGroup');
    const selectedClass = document.getElementById('selectedClass');
    
    if (visibility === 'class') {
        classSelectionGroup.style.display = 'block';
        selectedClass.required = true;
    } else {
        classSelectionGroup.style.display = 'none';
        selectedClass.required = false;
    }
}

async function loadClasses() {
    try {
        const response = await fetch('../api/forum_endpoints.php?action=get_classes', {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.status === 200 && data.data.classes) {
            const select = document.getElementById('selectedClass');
            data.data.classes.forEach(cls => {
                const option = document.createElement('option');
                option.value = cls.id;
                option.textContent = `${cls.name}${cls.subject ? ' - ' + cls.subject : ''}${cls.year ? ' (Year ' + cls.year + ')' : ''}`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading classes:', error);
    }
}

document.getElementById('createForumForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const title = document.getElementById('forumTitle').value.trim();
    const description = document.getElementById('forumDescription').value.trim();
    const visibility = document.getElementById('forumVisibility').value;
    const classId = visibility === 'class' ? document.getElementById('selectedClass').value : null;
    const startDate = document.getElementById('forumStartDate').value || null;
    const endDate = document.getElementById('forumEndDate').value || null;
    
    if (description.length < 20) {
        showError('Description must be at least 20 characters long.');
        return;
    }

    if (visibility === 'class' && !classId) {
        showError('Please select a classroom for class-only forums.');
        return;
    }

    try {
        const response = await fetch('../api/forum_endpoints.php?action=create_forum', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({
                title,
                description,
                visibility,
                class_id: classId,
                tags: [],
                start_date: startDate,
                end_date: endDate
            })
        });
        
        const data = await response.json();
        
        if (data.status === 200) {
            showSuccess();
        } else {
            showError(data.message || 'Failed to create forum. Please try again.');
        }
    } catch (error) {
        console.error('Error creating forum:', error);
        showError('An error occurred. Please check your connection and try again.');
    }
});

// Check if user is logged in
if (sessionStorage.getItem('userLoggedIn') !== 'true') {
    window.location.href = 'login.html';
}

// Load classes on page load
loadClasses();

// Make functions globally accessible
window.handleVisibilityChange = handleVisibilityChange;

