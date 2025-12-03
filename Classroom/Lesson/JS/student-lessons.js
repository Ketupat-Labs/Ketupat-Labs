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
    const grid = document.getElementById('lessonsGrid');
    
    if (!lessons || lessons.length === 0) {
        renderEmptyState();
        return;
    }

    grid.innerHTML = lessons.map(lesson => `
        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-shadow">
            <h3 class="text-xl font-semibold text-[#2454FF] mb-2">${escapeHtml(lesson.title)}</h3>
            <p class="text-gray-600 text-sm mb-2">Topic: ${escapeHtml(lesson.topic)}</p>
            ${lesson.duration ? `<p class="text-gray-500 text-sm mb-4"><i class="far fa-clock"></i> ${lesson.duration} mins</p>` : ''}
            ${lesson.teacher_name ? `<p class="text-gray-500 text-sm mb-4">By: ${escapeHtml(lesson.teacher_name)}</p>` : ''}
            <div class="flex gap-2">
                <a href="view-lesson.php?id=${lesson.id}" 
                   class="inline-block bg-[#5FAD56] hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition ease-in-out duration-150 flex-1 text-center">
                    View Lesson
                </a>
                ${lesson.is_enrolled == 1 
                    ? '<span class="inline-block bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-lg flex-1 text-center">Enrolled</span>'
                    : `<button onclick="enrollLesson(${lesson.id})" class="inline-block bg-[#2454FF] hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition ease-in-out duration-150 flex-1">
                        Enroll
                    </button>`
                }
            </div>
        </div>
    `).join('');
}

function renderEmptyState() {
    const grid = document.getElementById('lessonsGrid');
    grid.innerHTML = `
        <div class="col-span-full text-center py-12">
            <i class="fas fa-book text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-500 text-lg">No lessons available at the moment.</p>
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
            loadLessons(); // Reload to update enrollment status
        } else {
            showAlert('error', data.message || 'Failed to enroll in lesson');
        }
    } catch (error) {
        console.error('Error enrolling in lesson:', error);
        showAlert('error', 'Failed to enroll in lesson');
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

// Make enrollLesson available globally
window.enrollLesson = enrollLesson;

