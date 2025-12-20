<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Assign Lessons') }}
        </h2>
    </x-slot>

    <script>
        window.assignmentData = {
            states: @json($lessonStates),
            activityStates: @json($activityStates),
            calendar: {
                month: @json($month),
                year: @json($year),
                events: @json($events)
            }
        };

        // Manual DOM Manipulation Bridge (The "Nuclear Option")
        window.openManualEdit = function(id) {
            console.log('Opening Manual Edit for ID:', id);
             // 1. Get Data
            const data = window.assignmentData.activityStates[id];
            if (!data) {
                alert('Error: Data not found for ID ' + id);
                return;
            }

            // 2. Populate Fields Directly
            const titleEl = document.getElementById('manual-edit-title');
            if(titleEl) titleEl.innerText = data.title;
            
            const dateEl = document.getElementById('manual-edit-date');
            if(dateEl) dateEl.value = data.date ? data.date.replace(' ', 'T') : '';
            
            const notesEl = document.getElementById('manual-edit-notes');
            if(notesEl) notesEl.value = data.notes || '';
            
            // 3. Handle Public Logic
            const publicCheck = document.getElementById('manual-edit-public-check');
            
            if (publicCheck) {
                publicCheck.checked = !!data.is_public;
                // Re-use the toggle function to ensure UI state matches
                window.toggleManualPublic(publicCheck);
            }

             // 4. Handle Class Logic (Vanilla Loop)
            const classChecks = document.querySelectorAll('.manual-class-checkbox');
            // Ensure we have an array of strings for comparison
            const assignedIds = (data.classroom_ids || []).map(String); 
            
            classChecks.forEach(cb => {
                if (assignedIds.includes(cb.value)) {
                    cb.checked = true;
                } else {
                    cb.checked = false;
                }
            });
            
            // 5. Show Modal
            const modal = document.getElementById('manual-edit-modal');
            if(modal) modal.style.display = 'block'; // Force visible
            
            // 5. Update Hidden inputs
            const form = modal.querySelector('form');
            if(form) {
                const idInput = form.querySelector('input[name="activity_id"]');
                if(idInput) idInput.value = data.activity_id;
                
                const assignmentIdInput = form.querySelector('input[name="assignment_id"]');
                if(assignmentIdInput) assignmentIdInput.value = data.id;
            }
        }

        window.toggleManualPublic = function(checkbox) {
            const dateEl = document.getElementById('manual-edit-date');
            const warningEl = document.getElementById('manual-public-warning');
            
            if (checkbox.checked) {
                // Public = No Date
                if(dateEl) {
                    dateEl.disabled = true;
                    dateEl.value = ''; 
                    dateEl.classList.add('bg-gray-100', 'cursor-not-allowed');
                    dateEl.classList.remove('bg-white');
                }
                if(warningEl) warningEl.style.display = 'block';
            } else {
                // Private = Allow Date
                if(dateEl) {
                    dateEl.disabled = false;
                    dateEl.classList.remove('bg-gray-100', 'cursor-not-allowed');
                    dateEl.classList.add('bg-white');
                }
                if(warningEl) warningEl.style.display = 'none';
            }
        }

        window.closeManualEdit = function() {
            document.getElementById('manual-edit-modal').style.display = 'none';
        }

        window.submitManualForm = function() {
            try {
                const modal = document.getElementById('manual-edit-modal');
                if (!modal) throw new Error('Modal element not found');

                // Get Activity ID
                const formInModal = modal.querySelector('form');
                if (!formInModal) throw new Error('Form element inside modal not found');

                const idInput = formInModal.querySelector('input[name="activity_id"]');
                const id = idInput ? idInput.value : null;

                if (!id) {
                    alert('CRITICAL ERROR: No Activity ID found. Please refresh the page and try again.');
                    return;
                }

                // Create Dynamic Form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('schedule.store') }}";
                form.style.display = 'none';

                // CSRF (Blade Injection - Safe)
                const csrfInput = document.createElement('input');
                csrfInput.name = '_token';
                csrfInput.value = "{{ csrf_token() }}";
                form.appendChild(csrfInput);

                // Activity ID
                const actInput = document.createElement('input');
                actInput.name = 'activity_id';
                actInput.value = id;
                form.appendChild(actInput);
                
                // Append to body to ensure submit works correctly in all browsers
                document.body.appendChild(form);

                // Assignment ID (if updating existing)
                const assIdInput = formInModal.querySelector('input[name="assignment_id"]');
                if(assIdInput && assIdInput.value && !assIdInput.value.startsWith('public_')) {
                    const aInput = document.createElement('input');
                    aInput.name = 'assignment_id';
                    aInput.value = assIdInput.value;
                    form.appendChild(aInput);
                }

                // Redirect
                const redInput = document.createElement('input');
                redInput.name = 'redirect_to';
                redInput.value = 'assignments';
                form.appendChild(redInput);

                // Public boolean
                const publicCheck = document.getElementById('manual-edit-public-check');
                const pInput = document.createElement('input');
                pInput.name = 'is_public';
                pInput.value = (publicCheck && publicCheck.checked) ? '1' : '0';
                form.appendChild(pInput);

                // Due Date
                const dateEl = document.getElementById('manual-edit-date');
                if(dateEl && !dateEl.disabled && dateEl.value) {
                    const dInput = document.createElement('input');
                    dInput.name = 'due_date';
                    dInput.value = dateEl.value;
                    form.appendChild(dInput);
                }

                // Notes
                const notesEl = document.getElementById('manual-edit-notes');
                if(notesEl) {
                    const nInput = document.createElement('input');
                    nInput.name = 'notes';
                    nInput.value = notesEl.value;
                    form.appendChild(nInput);
                }

                // Classes
                const classChecks = document.querySelectorAll('.manual-class-checkbox:checked');
                
                if (classChecks.length === 0 && (!publicCheck || !publicCheck.checked)) {
                    alert('Sila pilih sekurang-kurangnya satu kelas atau set sebagai Public.');
                    return;
                }

                classChecks.forEach(cb => {
                    const cInput = document.createElement('input');
                    cInput.name = 'classroom_ids[]';
                    cInput.value = cb.value;
                    form.appendChild(cInput);
                });

                document.body.appendChild(form);
                form.submit();

            } catch (e) {
                console.error(e);
                alert('System Error: ' + e.message);
            }
        }

        // Daily Agenda Modal Logic
        window.openDateAgenda = function(dateTitle, events) {
            document.getElementById('agenda-date-title').innerText = dateTitle;
            const container = document.getElementById('agenda-events-container');
            container.innerHTML = '';

            if (events.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-sm italic py-4">Tiada aktiviti dijadualkan.</p>';
            } else {
                events.forEach(ev => {
                    const div = document.createElement('div');
                    div.className = 'p-3 bg-indigo-50/50 rounded-2xl border border-indigo-100/50 mb-2 text-left';
                    div.innerHTML = `
                        <div class="font-bold text-indigo-900 text-sm">${ev.title}</div>
                        <div class="text-xs text-indigo-600 mt-0.5 font-medium">${ev.class ? ev.class : 'Public'}</div>
                        ${ev.notes ? `<div class="text-xs text-gray-500 mt-1 italic">"${ev.notes}"</div>` : ''}
                    `;
                    container.appendChild(div);
                });
            }

            document.getElementById('date-agenda-modal').style.display = 'block';
        }

        window.closeDateAgenda = function() {
            document.getElementById('date-agenda-modal').style.display = 'none';
        }
    </script>

    <script>
        // ... (previous scripts) ...

        // NEW: Manual Lesson Edit Bridge
        window.openLessonEdit = function(id, title) {
            console.log('Opening Lesson Edit for ID:', id);
            const data = window.assignmentData.states[id];
            
            // Set IDs and Titles
            const modal = document.getElementById('lesson-edit-modal');
            const form = document.getElementById('lesson-edit-form');
            const titleEl = document.getElementById('lesson-edit-title');
            const idInput = document.getElementById('lesson-edit-id-input');
            
            if(titleEl) titleEl.innerText = title;
            if(idInput) idInput.value = id;
            if(modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'block';
            }

            // Populate Checkboxes
            // 1. Public Checkbox
            const publicCheck = document.getElementById('lesson-edit-public');
            if (publicCheck) {
                publicCheck.checked = data ? !!data.is_public : false;
            }

            // 2. Class Checkboxes
            const classChecks = document.querySelectorAll('.lesson-class-checkbox');
            const assignedIds = (data && data.classroom_ids) ? data.classroom_ids.map(String) : [];
            
            classChecks.forEach(cb => {
                if (assignedIds.includes(cb.value)) {
                    cb.checked = true;
                } else {
                    cb.checked = false;
                }
            });
        }

        window.closeLessonEdit = function() {
            const modal = document.getElementById('lesson-edit-modal');
            if(modal) {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }
        }
    </script>

    <div class="py-12" @open-edit-activity.window="openEditActivity($event.detail.id)" x-data="{ 
        // Simplified Alpine Data - Only needed for Tabs and Activity logic now
        states: window.assignmentData.states,
        activityStates: window.assignmentData.activityStates,
        activeTab: '{{ request('tab') ?? $activeTab ?? 'lesson' }}',
        
        // Activity Edit State (Keep existing)
        editActTitle: '',
        editActDate: '',
        editActNotes: '',
        editActActivityId: null,
        editActIsPublic: false,
        editActClassroomIds: [],
        editSearch: '',
        editActModalOpen: false,

        openEditActivity(id) {
             // ... (Keep existing Activity logic) ...
             const state = this.activityStates[id];
             if (state) {
                 this.editActTitle = state.title;
                 this.editActDate = state.date ? state.date.replace(' ', 'T').substring(0, 16) : '';
                 this.editActNotes = state.notes || '';
                 this.editActActivityId = state.activity_id;
                 this.editActIsPublic = !!state.is_public;
                 this.editActClassroomIds = (state.classroom_ids || []).map(Number);
                 this.editActModalOpen = true;
             }
        },
        toggleEditActClass(id) {
            if (this.editActClassroomIds.includes(id)) {
                this.editActClassroomIds = this.editActClassroomIds.filter(x => x != id);
            } else {
                this.editActClassroomIds.push(id);
            }
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <!-- Tabs Navigation -->
                    <div class="flex space-x-4 mb-6 border-b border-gray-200">
                        <button @click="activeTab = 'lesson'" 
                            :class="{ 'border-b-2 border-blue-500 text-blue-600': activeTab === 'lesson', 'text-gray-500 hover:text-gray-700': activeTab !== 'lesson' }"
                            class="pb-2 px-4 text-sm font-medium transition-colors duration-200 focus:outline-none">
                            Tugaskan Pelajaran
                        </button>
                        <button @click="activeTab = 'activity'" 
                            :class="{ 'border-b-2 border-blue-500 text-blue-600': activeTab === 'activity', 'text-gray-500 hover:text-gray-700': activeTab !== 'activity' }"
                            class="pb-2 px-4 text-sm font-medium transition-colors duration-200 focus:outline-none">
                            Mengendalikan Aktiviti
                        </button>
                    </div>

                    <!-- LESSON TAB -->
                    <div x-show="activeTab === 'lesson'">
                        <form method="POST" action="{{ route('assignments.store') }}">
                            @csrf

                            <!-- Multi-Select Class Dropdown (Create Mode) -->
                            <div class="mb-6" x-data="{ 
                                open: false, 
                                search: '',
                                selectedCount: 0,
                                selectedItems: [],
                                updateLabel() {
                                    this.selectedItems = [];
                                    // Scan checked inputs to build tags
                                    let checked = document.querySelectorAll('.create-class-check:checked, .create-public-check:checked');
                                    checked.forEach(el => {
                                        this.selectedItems.push({
                                            id: el.id,
                                            name: el.getAttribute('data-name'),
                                            value: el.value 
                                        });
                                    });
                                    this.selectedCount = this.selectedItems.length;
                                },
                                removeItem(id) {
                                    const el = document.getElementById(id);
                                    if(el) {
                                        el.checked = false;
                                        this.updateLabel();
                                    }
                                }
                            }" x-init="updateLabel()">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Kelas / Target (Boleh pilih lebih dari satu)</label>
                                
                                <!-- Dropdown Trigger Button -->
                                <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.searchInput.focus())"
                                    class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <span class="block truncate text-gray-600" x-text="selectedCount > 0 ? selectedCount + ' Dipilih' : 'Pilih Kelas...'"></span>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </button>

                                <!-- Selected Tags (Pills) -->
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <template x-for="item in selectedItems" :key="item.id">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            <span x-text="item.name"></span>
                                            <button type="button" @click="removeItem(item.id)" class="flex-shrink-0 ml-1.5 h-4 w-4 rounded-full inline-flex items-center justify-center text-indigo-400 hover:bg-indigo-200 hover:text-indigo-500 focus:outline-none">
                                                <span class="sr-only">Remove</span>
                                                <svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8">
                                                    <path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>
                                </div>

                                <!-- Dropdown Content -->
                                <div x-show="open" @click.away="open = false" 
                                    class="absolute z-10 mt-1 w-full sm:w-[500px] bg-white shadow-lg rounded-md border border-gray-200 py-1 text-base ring-1 ring-black ring-opacity-5 overflow-hidden focus:outline-none sm:text-sm"
                                    style="display: none;">
                                    
                                    <!-- Search Input -->
                                    <div class="p-2 border-b border-gray-100 bg-gray-50">
                                        <input x-ref="searchInput" x-model="search" type="text" placeholder="Cari kelas..." 
                                            class="w-full border-gray-300 rounded-md text-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>

                                    <div class="max-h-60 overflow-y-auto p-2 space-y-2">
                                        <!-- Public Option -->
                                        <div class="flex items-center p-2 bg-green-50 rounded-md border border-green-100"
                                             x-show="'public (semua pelajar)'.includes(search.toLowerCase())">
                                            <input id="create_public_option" name="is_public" type="checkbox" value="1" data-name="Public (Semua Pelajar)"
                                                class="create-public-check h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                                                @change="updateLabel()">
                                            <label for="create_public_option" class="ml-2 block text-sm font-bold text-green-900 cursor-pointer w-full">
                                                Public (Semua Pelajar)
                                            </label>
                                        </div>

                                        <!-- Class Options -->
                                        @foreach($classrooms as $classroom)
                                            <div class="flex items-center p-2 bg-white rounded-md border border-gray-200 hover:bg-indigo-50"
                                                 x-show="$el.textContent.toLowerCase().includes(search.toLowerCase())">
                                                <input id="create_class_{{ $classroom->id }}" name="classroom_ids[]" type="checkbox" value="{{ $classroom->id }}" data-name="{{ $classroom->name }}"
                                                    class="create-class-check h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                    @change="updateLabel()">
                                                <label for="create_class_{{ $classroom->id }}" class="ml-2 block text-sm text-gray-900 cursor-pointer w-full">
                                                    {{ $classroom->name }} <span class="text-gray-500 text-xs">({{ $classroom->subject }})</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="bg-gray-50 px-4 py-2 text-xs text-gray-500 text-center border-t border-gray-100">
                                        Klik luar untuk tutup
                                    </div>
                                </div>
                            </div>

                            <!-- Lesson Selection -->
                            <div class="mb-6" x-data="{ 
                                lessonSearch: '',
                                fuzzyMatch(text, query) {
                                    text = (text || '').toLowerCase();
                                    query = (query || '').toLowerCase().trim();
                                    if (!query) return true;
                                    let i = 0, n = -1, l;
                                    for (; l = query[i++];) {
                                        if (!~(n = text.indexOf(l, n + 1))) return false;
                                    }
                                    return true;
                                }
                            }">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Pelajaran untuk Ditugaskan</label>
                                
                                <!-- Search Input -->
                                <div class="mb-2">
                                    <input type="text" x-model="lessonSearch" placeholder="Cari pelajaran..." 
                                        class="w-full border-gray-300 rounded-md text-sm p-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                                </div>

                                <div class="border rounded-md p-4 max-h-60 overflow-y-auto bg-gray-50">
                                    @foreach($lessons as $lesson)
                                        <div class="flex items-start mb-3 pb-3 border-b border-gray-200 last:border-0 last:mb-0 last:pb-0"
                                             x-show="fuzzyMatch('{{ $lesson->title }}', lessonSearch) || fuzzyMatch('{{ $lesson->topic }}', lessonSearch)">
                                            <div class="flex items-center h-5">
                                                <input id="lesson_{{ $lesson->id }}" name="lessons[]" value="{{ $lesson->id }}"
                                                    type="checkbox"
                                                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label for="lesson_{{ $lesson->id }}"
                                                    class="font-medium text-gray-700">{{ $lesson->title }}</label>
                                                <p class="text-gray-500">{{ $lesson->topic }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                    <p x-show="lessonSearch !== '' && $el.parentNode.querySelectorAll('div[x-show]:not([style*=\'display: none\'])').length === 0" 
                                       class="text-center text-gray-400 text-sm italic py-2" style="display: none;">
                                        Tiada pelajaran dijumpai.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <button type="submit"
                                    class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg font-bold text-sm text-white uppercase tracking-wider shadow-md transform transition hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 bg-[#2454FF] hover:bg-blue-700">
                                    {{ __('Tugaskan Pelajaran') }}
                                </button>
                            </div>
                        </form>

                        <!-- Assignments List -->
                        <div class="mt-12 border-t pt-8">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Tugasan Pelajaran Terkini</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelajaran</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarikh Tugasan</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tindakan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($assignments as $assignment)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $assignment->lesson->title ?? 'Unknown Lesson' }}
                                                    @if($assignment->lesson->is_public)
                                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                            Awam
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $assignment->classroom->name }} ({{ $assignment->classroom->subject }})
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $assignment->assigned_at ? \Carbon\Carbon::parse($assignment->assigned_at)->format('d M Y') : '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <button type="button" 
                                                        onclick="window.openLessonEdit({{ $assignment->lesson_id }}, {{ \Illuminate\Support\Js::from($assignment->lesson->title) }})"
                                                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-md shadow-sm transition duration-150 ease-in-out mr-2">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                        Sunting
                                                    </button>
                                                    <form action="{{ route('assignments.destroy', $assignment->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Adakah anda pasti mahu memadam tugasan ini?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                            class="inline-flex items-center px-3 py-1.5 bg-white border border-red-300 text-red-600 hover:bg-red-50 text-xs font-semibold rounded-md shadow-sm transition duration-150 ease-in-out">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                            Padam
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Tiada tugasan ditemui.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- ACTIVITY TAB -->
                    <div x-show="activeTab === 'activity'" style="display: none;">
                        <form action="{{ route('schedule.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="redirect_to" value="assignments">

                            <!-- Multi-Select Class Dropdown -->
                            <div class="mb-6" x-data="{ 
                                open: false, 
                                search: '',
                                selectedCount: 0,
                                selectedItems: [],
                                 updateLabel() {
                                    this.selectedItems = [];
                                    let checked = document.querySelectorAll('.act-class-check:checked, .act-public-check:checked');
                                    let hasPublic = false;
                                    checked.forEach(el => {
                                        if(el.id === 'act_public_option') hasPublic = true;
                                        this.selectedItems.push({
                                            id: el.id,
                                            name: el.getAttribute('data-name'),
                                            value: el.value 
                                        });
                                    });
                                    this.selectedCount = this.selectedItems.length;

                                    // Dynamic UI update for Public assignments
                                    const warning = document.getElementById('tab-public-warning');
                                    const dateInput = document.getElementById('tab-due-date');
                                    if(warning && dateInput) {
                                        if(hasPublic) {
                                            warning.style.display = 'block';
                                            dateInput.disabled = true;
                                            dateInput.value = '';
                                            dateInput.classList.add('bg-gray-100', 'cursor-not-allowed');
                                        } else {
                                            warning.style.display = 'none';
                                            dateInput.disabled = false;
                                            dateInput.classList.remove('bg-gray-100', 'cursor-not-allowed');
                                        }
                                    }
                                },
                                removeItem(id) {
                                    const el = document.getElementById(id);
                                    if(el) {
                                        el.checked = false;
                                        this.updateLabel();
                                    }
                                }
                            }" x-init="updateLabel()">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Kelas / Target (Boleh pilih lebih dari satu)</label>
                                
                                <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.actSearchInput.focus())"
                                    class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <span class="block truncate text-gray-600" x-text="selectedCount > 0 ? selectedCount + ' Dipilih' : 'Pilih Kelas...'"></span>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </button>

                                <div class="mt-2 flex flex-wrap gap-2">
                                    <template x-for="item in selectedItems" :key="item.id">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <span x-text="item.name"></span>
                                            <button type="button" @click="removeItem(item.id)" class="flex-shrink-0 ml-1.5 h-4 w-4 rounded-full inline-flex items-center justify-center text-blue-400 hover:bg-blue-200 hover:text-blue-500 focus:outline-none">
                                                <span class="sr-only">Remove</span>
                                                <svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8">
                                                    <path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>
                                </div>

                                <div x-show="open" @click.away="open = false" 
                                    class="absolute z-10 mt-1 w-full sm:w-[500px] bg-white shadow-lg rounded-md border border-gray-200 py-1 text-base ring-1 ring-black ring-opacity-5 overflow-hidden focus:outline-none sm:text-sm"
                                    style="display: none;">
                                    
                                    <div class="p-2 border-b border-gray-100 bg-gray-50">
                                        <input x-ref="actSearchInput" x-model="search" type="text" placeholder="Cari kelas..." 
                                            class="w-full border-gray-300 rounded-md text-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>

                                    <div class="max-h-60 overflow-y-auto p-2 space-y-2">
                                        <div class="flex items-center p-2 bg-green-50 rounded-md border border-green-100"
                                             x-show="'public (semua pelajar)'.includes(search.toLowerCase())">
                                            <input id="act_public_option" name="is_public" type="checkbox" value="1" data-name="Public (Semua)"
                                                {{ $isActivityPublic ? 'checked' : '' }}
                                                class="act-public-check h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                                                @change="updateLabel()">
                                            <label for="act_public_option" class="ml-2 block text-sm font-bold text-green-900 cursor-pointer w-full">
                                                Public (Semua)
                                            </label>
                                        </div>

                                        @foreach($classrooms as $c)
                                            <div class="flex items-center p-2 bg-white rounded-md border border-gray-200 hover:bg-blue-50"
                                                 x-show="$el.textContent.toLowerCase().includes(search.toLowerCase())">
                                                <input id="act_class_{{ $c->id }}" name="classroom_ids[]" type="checkbox" value="{{ $c->id }}" data-name="{{ $c->name }}"
                                                    {{ in_array($c->id, $assignedClassroomIdsForActivity) ? 'checked' : '' }}
                                                    class="act-class-check h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                                    @change="updateLabel()">
                                                <label for="act_class_{{ $c->id }}" class="ml-2 block text-sm text-gray-900 cursor-pointer w-full">
                                                    {{ $c->name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Activity Selection -->
                            <div class="mb-6" x-data="{ 
                                actSearch: '',
                                fuzzyMatch(text, query) {
                                    text = (text || '').toLowerCase();
                                    query = (query || '').toLowerCase().trim();
                                    if (!query) return true;
                                    let i = 0, n = -1, l;
                                    for (; l = query[i++];) {
                                        if (!~(n = text.indexOf(l, n + 1))) return false;
                                    }
                                    return true;
                                }
                            }">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Aktiviti untuk Ditugaskan</label>
                                <div class="mb-2">
                                    <input type="text" x-model="actSearch" placeholder="Cari aktiviti..." 
                                        class="w-full border-gray-300 rounded-md text-sm p-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                </div>

                                <div class="border rounded-md p-4 max-h-60 overflow-y-auto bg-gray-50 flex flex-col gap-2">
                                    @foreach(['Games' => $games, 'Quizzes' => $quizzes] as $label => $collection)
                                        @if($collection->isNotEmpty())
                                            <div class="mt-2 first:mt-0">
                                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $label }}</span>
                                            </div>
                                            @foreach($collection as $act)
                                                <div class="flex items-center p-3 bg-white border border-gray-200 rounded-md hover:border-blue-300 transition-all cursor-pointer group"
                                                     x-show="fuzzyMatch('{{ $act->title }}', actSearch)">
                                                    <input id="act_{{ $act->id }}" name="activity_id" value="{{ $act->id }}" 
                                                        type="radio" required
                                                        {{ ($preselectedActivityId == $act->id) ? 'checked' : '' }}
                                                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                                    <label for="act_{{ $act->id }}" class="ml-3 block w-full cursor-pointer">
                                                        <span class="block text-sm font-bold text-gray-700 group-hover:text-blue-600 transition-colors">{{ $act->title }}</span>
                                                        <span class="block text-[10px] text-gray-400 uppercase tracking-tighter">{{ $act->type }}</span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        
                            <!-- Additional Fields -->
                            <div id="tab-public-warning" style="display: none;" class="mb-6 text-xs text-amber-700 bg-amber-50 p-4 rounded-lg border border-amber-200">
                                <span class="font-bold">Informasi:</span> Tarikh akhir tidak diperlukan bagi tugasan yang ditetapkan sebagai Awam (Public).
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tarikh Akhir (Pilihan)</label>
                                    <input id="tab-due-date" type="datetime-local" name="due_date"
                                        class="w-full border-gray-300 rounded-md shadow-sm text-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nota Tugasan</label>
                                    <input type="text" name="notes" placeholder="Tulis arahan tambahan di sini..."
                                        class="w-full border-gray-300 rounded-md shadow-sm text-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <button type="submit"
                                    class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg font-bold text-sm text-white uppercase tracking-wider shadow-md transform transition hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 bg-[#2454FF] hover:bg-blue-700">
                                    {{ __('Jadualkan Aktiviti') }}
                                </button>
                            </div>
                        </form>

                        <!-- Recent Activity Table -->
                        <div class="mt-12 border-t pt-8">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Tugasan Aktiviti Terkini</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktiviti</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarikh Akhir</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tindakan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($activityAssignments as $aa)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $aa->activity->title }}
                                                    @if($aa->activity->is_public)
                                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Awam</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $aa->classroom->name }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $aa->due_date ? \Carbon\Carbon::parse($aa->due_date)->format('d M Y, h:i A') : '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    @if(!isset($aa->is_pseudo))
                                                        <button type="button" onclick="window.openManualEdit('{{ $aa->id }}')"
                                                            class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-md shadow-sm transition duration-150 ease-in-out mr-2">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                            Sunting
                                                        </button>
                                                        <a href="{{ route('activities.assignments.submissions', $aa->id) }}"
                                                            class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 text-xs font-semibold rounded-md shadow-sm transition duration-150 ease-in-out mr-2 border border-indigo-200">
                                                            Keputusan
                                                        </a>
                                                        <form action="{{ route('schedule.destroy', $aa->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Padam tugasan ini?')">
                                                            @csrf @method('DELETE')
                                                            <button class="inline-flex items-center px-3 py-1.5 bg-white border border-red-300 text-red-600 hover:bg-red-50 text-xs font-semibold rounded-md shadow-sm transition duration-150 ease-in-out">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                </svg>
                                                                Padam
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 italic">Tiada aktiviti dijadualkan lagi.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                         <!-- CALENDAR VIEW (Simplified) -->
                         <div class="mt-12 pt-8 border-t">
                            <div class="bg-gray-50 rounded-xl p-8 border border-gray-200">
                                @php
                                    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                                    $firstDayOfMonth = date('w', strtotime("$year-$month-01")); 
                                    $malayMonths = [
                                        1 => 'Januari', 2 => 'Februari', 3 => 'Mac', 4 => 'April',
                                        5 => 'Mei', 6 => 'Jun', 7 => 'Julai', 8 => 'Ogos',
                                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Disember'
                                    ];
                                    $currentMonthName = $malayMonths[$month] . ' ' . $year;
                                    $prevMonth = $month - 1; $prevYear = $year;
                                    if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
                                    $nextMonth = $month + 1; $nextYear = $year;
                                    if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
                                @endphp

                                <div class="flex items-center justify-between mb-8">
                                    <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Kalendar Aktiviti: {{ $currentMonthName }}
                                    </h3>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('assignments.create', array_merge(request()->all(), ['month' => $prevMonth, 'year' => $prevYear, 'tab' => 'activity'])) }}"
                                            class="p-2 hover:bg-white rounded-md border border-gray-200 text-gray-600 transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                        </a>
                                        <a href="{{ route('assignments.create', array_merge(request()->all(), ['month' => date('n'), 'year' => date('Y'), 'tab' => 'activity'])) }}"
                                            class="px-4 py-1.5 text-xs font-bold bg-white border border-gray-200 text-gray-600 rounded-md hover:text-blue-600 transition-all">
                                            Hari Ini
                                        </a>
                                        <a href="{{ route('assignments.create', array_merge(request()->all(), ['month' => $nextMonth, 'year' => $nextYear, 'tab' => 'activity'])) }}"
                                            class="p-2 hover:bg-white rounded-md border border-gray-200 text-gray-600 transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </div>
                                </div>

                                <div class="grid grid-cols-7 gap-px bg-gray-200 border border-gray-200 rounded-lg overflow-hidden">
                                    @foreach(['Ahad', 'Isnin', 'Selasa', 'Rabu', 'Khamis', 'Jumaat', 'Sabtu'] as $dayName)
                                        <div class="bg-gray-50 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center py-2">{{ $dayName }}</div>
                                    @endforeach

                                    @for($i = 0; $i < $firstDayOfMonth; $i++)
                                        <div class="bg-white min-h-[80px]"></div>
                                    @endfor

                                    @for($day = 1; $day <= $daysInMonth; $day++)
                                        @php
                                            $fullDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                            $dayEvents = collect($events)->filter(fn($e) => substr($e['start'], 0, 10) === $fullDate);
                                            $isToday = $fullDate === date('Y-m-d');
                                        @endphp
                                        <div class="bg-white min-h-[80px] p-2 relative group hover:bg-blue-50/30 transition-colors cursor-pointer"
                                             onclick="window.openDateAgenda('{{ $day }} {{ $currentMonthName }}', {{ json_encode($dayEvents->values()) }})">
                                            <span class="text-sm font-bold {{ $isToday ? 'bg-blue-600 text-white w-6 h-6 rounded-full flex items-center justify-center' : 'text-gray-400 group-hover:text-blue-600' }}">
                                                {{ $day }}
                                            </span>
                                            
                                            <div class="mt-1 space-y-1">
                                                @foreach($dayEvents->take(2) as $ev)
                                                    <div class="text-[9px] leading-tight truncate px-1.5 py-0.5 bg-blue-100 text-blue-700 rounded border border-blue-200 font-medium">
                                                        {{ $ev['title'] }}
                                                    </div>
                                                @endforeach
                                                @if($dayEvents->count() > 2)
                                                    <div class="text-[9px] text-blue-500 font-bold ml-1">+{{ $dayEvents->count() - 2 }} lagi</div>
                                                @endif
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Classic Vanilla Lesson Edit Modal -->
        <div id="lesson-edit-modal" class="hidden fixed z-50 inset-0 overflow-y-auto" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" onclick="window.closeLessonEdit()">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form id="lesson-edit-form" method="POST" action="{{ route('assignments.store') }}">
                        @csrf
                        <input type="hidden" id="lesson-edit-id-input" name="lessons[]">

                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Edit Assignment: <span id="lesson-edit-title" class="font-bold text-blue-600"></span>
                            </h3>
                            <div class="mt-4">
                                <!-- Search Input (Purely Visual for now in vanilla adapter) -->
                                <div class="mb-2">
                                    <input type="text" placeholder="Search classes..." 
                                        onkeyup="const val = this.value.toLowerCase(); document.querySelectorAll('.lesson-class-item').forEach(el => el.style.display = el.textContent.toLowerCase().includes(val) ? 'flex' : 'none')"
                                        class="w-full border-gray-300 rounded-md text-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div class="border border-gray-200 rounded-md max-h-60 overflow-y-auto bg-gray-50 p-2 space-y-2">
                                    <!-- Public Option -->
                                    <div class="flex items-center p-2 bg-green-50 rounded-md border border-green-100 lesson-class-item">
                                        <input id="lesson-edit-public" name="is_public" type="checkbox" value="1"
                                            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                        <label for="lesson-edit-public" class="ml-2 block text-sm font-bold text-green-900 cursor-pointer w-full">
                                            Public (All Students)
                                        </label>
                                    </div>

                                    <!-- Class Options -->
                                    @foreach($classrooms as $classroom)
                                        <div class="flex items-center p-2 bg-white rounded-md border border-gray-200 hover:bg-indigo-50 lesson-class-item">
                                            <input id="lesson_edit_class_{{ $classroom->id }}" name="classroom_ids[]" type="checkbox" value="{{ $classroom->id }}"
                                                class="lesson-class-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                            <label for="lesson_edit_class_{{ $classroom->id }}" class="ml-2 block text-sm text-gray-900 cursor-pointer w-full">
                                                {{ $classroom->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <p class="text-xs text-gray-500 mt-2">Uncheck to remove assignment. Check to add.</p>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end gap-3">
                            <button type="button" onclick="window.closeLessonEdit()" 
                                class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancel
                            </button>
                            <button type="submit" 
                                class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#2454FF] text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Reassign
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
        <!-- Edit Activity Modal -->
        <div id="manual-edit-modal" x-show="editActModalOpen" class="fixed z-50 inset-0 overflow-y-auto" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="editActModalOpen = false">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form method="POST" action="{{ route('schedule.store') }}">
                        @csrf
                        <input type="hidden" name="activity_id">
                        <input type="hidden" name="assignment_id">
                        <input type="hidden" name="redirect_to" value="assignments">
                        
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-bold text-gray-900 mb-4 flex items-center" id="modal-title">
                                <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                Sunting Tugasan: <span id="manual-edit-title" class="ml-1 text-blue-800"></span>
                            </h3>

                            <!-- Date & Notes Section (Primary Focus) -->
                            <div class="mb-6 bg-blue-50 p-4 rounded-xl border border-blue-100">
                                <label class="block text-sm font-bold text-gray-800 mb-2 flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    Kemaskini Tarikh Akhir (Due Date)
                                </label>
                                <input id="manual-edit-date" type="datetime-local" name="due_date"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 p-2.5 bg-white">
                                
                                <div id="manual-public-warning" style="display: none;" class="mt-2 text-xs text-amber-700 bg-amber-50 p-4 rounded-lg border border-amber-200">
                                    <span class="font-bold">Informasi:</span> Tarikh akhir tidak diperlukan bagi tugasan yang ditetapkan sebagai Awam (Public).
                                </div>

                                <label class="block text-sm font-medium text-gray-700 mt-3 mb-1">Nota</label>
                                <textarea id="manual-edit-notes" name="notes" rows="2" placeholder="Nota tugasan..."
                                    class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 bg-white"></textarea>
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Kelas:</label>
                                <!-- Multi-Select Logic -->
                                <div class="mb-2">
                                    <input x-model="editSearch" type="text" placeholder="Cari kelas..." 
                                        class="w-full border-gray-300 rounded-md text-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="border border-gray-200 rounded-md max-h-40 overflow-y-auto bg-gray-50 p-2 space-y-2">
                                    <!-- Public Option -->
                                    <div class="flex items-center p-2 bg-green-50 rounded-md border border-green-100">
                                        <input id="manual-edit-public-check" name="is_public" type="checkbox" value="1"
                                            onchange="window.toggleManualPublic(this)"
                                            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                        <label for="edit_act_public" class="ml-2 block text-sm font-bold text-green-900 cursor-pointer w-full">
                                            Public (Semua Pelajar)
                                        </label>
                                    </div>
                                    <!-- Class Options -->
                                    @foreach($classrooms as $classroom)
                                        <div class="flex items-center p-2 bg-white rounded-md border border-gray-200 hover:bg-indigo-50 manual-class-item">
                                            <input id="edit_act_class_{{ $classroom->id }}" name="classroom_ids[]" type="checkbox" value="{{ $classroom->id }}"
                                                class="manual-class-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                            <label for="edit_act_class_{{ $classroom->id }}" class="ml-2 block text-sm text-gray-900 cursor-pointer w-full">
                                                {{ $classroom->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end gap-3 border-t border-gray-200">
                            <button type="button" onclick="window.closeManualEdit()" 
                                class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Batal
                            </button>
                            <button type="button" onclick="window.submitManualForm()"
                                class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#2454FF] text-sm font-bold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Jadualkan Semula
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
</x-app-layout>

<!-- Daily Agenda Modal -->
<div id="date-agenda-modal" class="fixed inset-0 z-[60] overflow-y-auto" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500/30 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeDateAgenda()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-middle bg-white rounded-[2rem] text-center overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-sm w-full border border-gray-100">
            <div class="bg-white px-6 pt-8 pb-6">
                <div class="text-center">
                    <h3 class="text-xl font-bold text-gray-900 mb-1" id="agenda-date-title">Date</h3>
                    <p class="text-xs text-gray-400 uppercase tracking-wide font-bold mb-6">Senarai Tugasan</p>
                    <div class="mt-2 space-y-3 max-h-[60vh] overflow-y-auto px-1" id="agenda-events-container">
                        <!-- Events will be populated here -->
                    </div>
                </div>
            </div>
            <div class="bg-gray-50/50 px-4 py-3 sm:px-6">
                <button type="button" onclick="closeDateAgenda()" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-3 py-2 bg-indigo-600 text-sm font-bold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>