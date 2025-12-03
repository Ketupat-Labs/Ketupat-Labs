// API Configuration
const API_BASE = '../../api/lesson_endpoints.php';

// Load lessons on page load
document.addEventListener('DOMContentLoaded', () => {
    loadLessons();
});

async function loadLessons() {
    try {
        const response = await fetch(`${API_BASE}?action=get_lessons`, {
            credentials: 'include'
        });
        const data = await response.json();

        if (data.status === 200) {
            renderLessons(data.data.lessons);
        } else {
            showAlert('error', data.message || 'Failed to load lessons');
            renderEmptyState();
        }
    } catch (error) {
        console.error('Error loading lessons:', error);
        showAlert('error', 'Failed to load lessons');
        renderEmptyState();
    }
}

function renderLessons(lessons) {
    const tbody = document.getElementById('lessonsTableBody');
    
    if (!lessons || lessons.length === 0) {
        renderEmptyState();
        return;
    }

    tbody.innerHTML = lessons.map(lesson => `
        <tr>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(lesson.title)}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${escapeHtml(lesson.topic)}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${lesson.duration ? lesson.duration + ' mins' : 'N/A'}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
                ${lesson.is_published == 1 
                    ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Published</span>'
                    : '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Draft</span>'
                }
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
                ${lesson.material_path 
                    ? `<a href="../../${escapeHtml(lesson.material_path)}" target="_blank" class="text-[#F26430] hover:text-orange-700 font-medium">Download File</a>`
                    : '<span class="text-gray-400">No file</span>'
                }
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <a href="edit-lesson.php?id=${lesson.id}" class="text-[#2454FF] hover:text-blue-900 font-medium mr-3">Edit</a>
                <button onclick="deleteLesson(${lesson.id})" class="text-[#E92222] hover:text-red-900 font-medium">Delete</button>
                <a href="view-lesson-teacher.php?id=${lesson.id}" class="text-[#5FAD56] hover:text-green-700 font-medium ml-3">View</a>
            </td>
        </tr>
    `).join('');
}

function renderEmptyState() {
    const tbody = document.getElementById('lessonsTableBody');
    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="px-6 py-4 text-center">
                <div class="py-8">
                    <i class="fas fa-book text-4xl text-gray-400 mb-4"></i>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No lessons</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new lesson.</p>
                    <div class="mt-6">
                        <a href="create-lesson.php" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[#5FAD56] hover:bg-green-700">
                            <i class="fas fa-plus -ml-1 mr-2 h-5 w-5"></i>
                            Create New Lesson
                        </a>
                    </div>
                </div>
            </td>
        </tr>
    `;
}

async function deleteLesson(lessonId) {
    if (!confirm('Are you sure you want to delete this lesson? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch(`${API_BASE}?action=delete_lesson&lesson_id=${lessonId}`, {
            method: 'DELETE',
            credentials: 'include'
        });
        const data = await response.json();

        if (data.status === 200) {
            showAlert('success', 'Lesson deleted successfully');
            loadLessons();
        } else {
            showAlert('error', data.message || 'Failed to delete lesson');
        }
    } catch (error) {
        console.error('Error deleting lesson:', error);
        showAlert('error', 'Failed to delete lesson');
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

// Make deleteLesson available globally
window.deleteLesson = deleteLesson;

