// API Configuration
const API_BASE = '../../api/lesson_endpoints.php';

// Load lesson on page load
document.addEventListener('DOMContentLoaded', () => {
    const lessonId = window.lessonId;
    if (lessonId) {
        loadLesson(lessonId);
    } else {
        showError('No lesson ID provided');
    }
});

async function loadLesson(lessonId) {
    try {
        const response = await fetch(`${API_BASE}?action=get_lesson&lesson_id=${lessonId}`, {
            credentials: 'include'
        });
        const data = await response.json();

        if (data.status === 200) {
            renderLesson(data.data.lesson);
        } else {
            showError(data.message || 'Failed to load lesson');
        }
    } catch (error) {
        console.error('Error loading lesson:', error);
        showError('Failed to load lesson');
    }
}

function renderLesson(lesson) {
    const container = document.getElementById('lessonContent');
    
    if (!lesson) {
        container.innerHTML = '<div class="text-center py-12"><p class="text-gray-500">Lesson not found</p></div>';
        return;
    }

    container.innerHTML = `
        <div class="lesson-detail">
            <div class="mb-6 border-b-2 border-[#2454FF] pb-4">
                <h1 class="text-3xl font-extrabold text-[#2454FF] mb-2">${escapeHtml(lesson.title)}</h1>
                <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                    <span><i class="fas fa-book"></i> Topic: ${escapeHtml(lesson.topic)}</span>
                    ${lesson.duration ? `<span><i class="far fa-clock"></i> ${lesson.duration} mins</span>` : ''}
                    ${lesson.teacher_name ? `<span><i class="fas fa-user"></i> ${escapeHtml(lesson.teacher_name)}</span>` : ''}
                    ${lesson.created_at ? `<span><i class="far fa-calendar"></i> ${formatDate(lesson.created_at)}</span>` : ''}
                </div>
            </div>

            ${lesson.content ? `
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-3">Lesson Content</h2>
                    <div class="prose max-w-none text-gray-700 whitespace-pre-wrap">${escapeHtml(lesson.content)}</div>
                </div>
            ` : ''}

            ${lesson.url ? `
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-3">External Resources</h2>
                    <a href="${escapeHtml(lesson.url)}" target="_blank" class="text-[#2454FF] hover:text-blue-700 font-medium">
                        <i class="fas fa-external-link-alt"></i> ${escapeHtml(lesson.url)}
                    </a>
                </div>
            ` : ''}

            ${lesson.material_path ? `
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-3">Lesson Materials</h2>
                    <a href="../../${escapeHtml(lesson.material_path)}" target="_blank" 
                       class="inline-flex items-center px-4 py-2 bg-[#F26430] hover:bg-orange-700 text-white font-bold rounded-lg transition">
                        <i class="fas fa-download mr-2"></i>
                        Download Material
                    </a>
                </div>
            ` : ''}

            ${!lesson.enrollment ? `
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <button onclick="enrollLesson(${lesson.id})" 
                            class="w-full bg-[#2454FF] hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition">
                        <i class="fas fa-user-plus mr-2"></i>
                        Enroll in this Lesson
                    </button>
                </div>
            ` : `
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        <i class="fas fa-check-circle mr-2"></i>
                        You are enrolled in this lesson
                    </div>
                </div>
            `}
        </div>
    `;
}

async function enrollLesson(lessonId) {
    try {
        const response = await fetch(`${API_BASE}?action=enroll_lesson`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ lesson_id: lessonId }),
            credentials: 'include'
        });
        const data = await response.json();

        if (data.status === 200) {
            showAlert('success', 'Successfully enrolled in lesson!');
            setTimeout(() => {
                loadLesson(lessonId); // Reload to update enrollment status
            }, 1000);
        } else {
            showAlert('error', data.message || 'Failed to enroll in lesson');
        }
    } catch (error) {
        console.error('Error enrolling in lesson:', error);
        showAlert('error', 'Failed to enroll in lesson');
    }
}

function showError(message) {
    const container = document.getElementById('lessonContent');
    container.innerHTML = `
        <div class="text-center py-12">
            <i class="fas fa-exclamation-circle text-4xl text-red-400 mb-4"></i>
            <p class="text-red-500">${escapeHtml(message)}</p>
        </div>
    `;
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

    setTimeout(() => {
        container.innerHTML = '';
    }, 5000);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Make enrollLesson available globally
window.enrollLesson = enrollLesson;

