// API Configuration
const API_BASE = '../../api/lesson_endpoints.php';

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('lessonForm');
    if (form) {
        form.addEventListener('submit', handleSubmit);
    }
});

async function handleSubmit(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    // Validate required fields
    const title = formData.get('title');
    const topic = formData.get('topic');
    const content = formData.get('content');

    if (!title || !topic || !content) {
        showAlert('error', 'Please fill in all required fields');
        return;
    }

    // Disable submit button
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';

    try {
        // Create JSON payload for non-file data
        const payload = {
            title: formData.get('title'),
            topic: formData.get('topic'),
            content: formData.get('content'),
            duration: formData.get('duration') || null,
            url: formData.get('url') || null,
            is_published: true
        };

        // If file is selected, use FormData, otherwise use JSON
        if (formData.get('material_file') && formData.get('material_file').size > 0) {
            // Use FormData for file upload
            const uploadFormData = new FormData();
            uploadFormData.append('material_file', formData.get('material_file'));
            uploadFormData.append('data', JSON.stringify(payload));

            const response = await fetch(`${API_BASE}?action=create_lesson`, {
                method: 'POST',
                body: uploadFormData,
                credentials: 'include'
            });
            const data = await response.json();

            if (data.status === 200) {
                showAlert('success', 'Lesson created successfully!');
                setTimeout(() => {
                    window.location.href = 'manage-lessons.php';
                }, 1500);
            } else {
                showAlert('error', data.message || 'Failed to create lesson');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        } else {
            // Use JSON for non-file upload
            const response = await fetch(`${API_BASE}?action=create_lesson`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload),
                credentials: 'include'
            });
            const data = await response.json();

            if (data.status === 200) {
                showAlert('success', 'Lesson created successfully!');
                setTimeout(() => {
                    window.location.href = 'manage-lessons.php';
                }, 1500);
            } else {
                showAlert('error', data.message || 'Failed to create lesson');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        }
    } catch (error) {
        console.error('Error creating lesson:', error);
        showAlert('error', 'Failed to create lesson. Please try again.');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
}

function showAlert(type, message) {
    const container = document.getElementById('alertContainer');
    const alertClass = type === 'success' 
        ? 'bg-green-100 border-green-400 text-green-700'
        : 'bg-red-100 border-red-400 text-red-700';
    
    container.innerHTML = `
        <div class="${alertClass} border px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">${escapeHtml(message)}</span>
        </div>
    `;

    // Auto-hide after 5 seconds
    setTimeout(() => {
        container.innerHTML = '';
    }, 5000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

