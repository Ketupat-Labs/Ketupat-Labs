<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\AppLayout::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <?php echo e(__('Assign Lessons')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <script>
        window.assignmentData = {
            states: <?php echo json_encode($lessonStates, 15, 512) ?>,
            activityStates: <?php echo json_encode($activityStates, 15, 512) ?>
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
            // We need to find the specific hidden input inside the modal form
            const form = modal.querySelector('form');
            if(form) {
                const idInput = form.querySelector('input[name="activity_id"]');
                if(idInput) idInput.value = data.activity_id;
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
                form.action = "<?php echo e(route('schedule.store')); ?>";
                form.style.display = 'none';

                // CSRF (Blade Injection - Safe)
                const csrfInput = document.createElement('input');
                csrfInput.name = '_token';
                csrfInput.value = "<?php echo e(csrf_token()); ?>";
                form.appendChild(csrfInput);

                // Activity ID
                const actInput = document.createElement('input');
                actInput.name = 'activity_id';
                actInput.value = id;
                form.appendChild(actInput);
                
                // Append to body to ensure submit works correctly in all browsers
                document.body.appendChild(form);

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

    <div class="py-12" @open-edit-activity.window="openEditActivity($event.detail.id)" x-data="{ 
        states: window.assignmentData.states,
        activityStates: window.assignmentData.activityStates,
        editModalOpen: false,
        activeTab: '<?php echo e(request('tab') ?? $activeTab ?? 'lesson'); ?>',
        targetLessonId: null,
        targetTitle: '',
        editIsPublic: false,
        editClassroomIds: [],
        
        // Activity Edit State
        editActTitle: '',
        editActDate: '',
        editActNotes: '',
        editActActivityId: null,
        editActIsPublic: false,
        editActClassroomIds: [],
        editSearch: '',
        
        // Activity Edit State
        editActModalOpen: false,
        editActId: null,
        editActAction: '',

        openEditActivity(id) {
             // Debugging: Check if data exists
             if (!this.activityStates) {
                 alert('System Error: Assignment data not loaded. Please refresh the page.');
                 return;
             }

             const state = this.activityStates[id];
             
             if (state) {
                 this.editActTitle = state.title;
                 this.editActDate = state.date ? state.date.replace(' ', 'T').substring(0, 16) : '';
                 this.editActNotes = state.notes || '';
                 this.editActActivityId = state.activity_id;
                 this.editActIsPublic = !!state.is_public;
                 this.editActClassroomIds = (state.classroom_ids || []).map(Number);
                 
                 this.editActModalOpen = true;
             } else {
                 console.error('State not found for ID:', id, 'in', this.activityStates);
                 alert('Error: Could not load details for assignment #' + id);
             }
        },
        toggleEditActClass(id) {
            if (this.editActClassroomIds.includes(id)) {
                this.editActClassroomIds = this.editActClassroomIds.filter(x => x != id);
            } else {
                this.editActClassroomIds.push(id);
            }
        },
        
        openEdit(id, title) {
            this.targetLessonId = id;
            this.targetTitle = title;
            const state = this.states[id];
            if(state) {
                this.editIsPublic = state.is_public;
                this.editClassroomIds = state.classroom_ids.map(Number);
            } else {
                this.editIsPublic = false;
                this.editClassroomIds = [];
            }
            this.editModalOpen = true;
        },
        
        toggleEditClass(id) {
            const index = this.editClassroomIds.indexOf(id);
            if (index === -1) this.editClassroomIds.push(id);
            else this.editClassroomIds.splice(index, 1);
        },

        get editSelectedCount() {
            return (this.editIsPublic ? 1 : 0) + this.editClassroomIds.length;
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
                        <form method="POST" action="<?php echo e(route('assignments.store')); ?>">
                            <?php echo csrf_field(); ?>

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
                                        <?php $__currentLoopData = $classrooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classroom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="flex items-center p-2 bg-white rounded-md border border-gray-200 hover:bg-indigo-50"
                                                 x-show="$el.textContent.toLowerCase().includes(search.toLowerCase())">
                                                <input id="create_class_<?php echo e($classroom->id); ?>" name="classroom_ids[]" type="checkbox" value="<?php echo e($classroom->id); ?>" data-name="<?php echo e($classroom->name); ?>"
                                                    class="create-class-check h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                    @change="updateLabel()">
                                                <label for="create_class_<?php echo e($classroom->id); ?>" class="ml-2 block text-sm text-gray-900 cursor-pointer w-full">
                                                    <?php echo e($classroom->name); ?> <span class="text-gray-500 text-xs">(<?php echo e($classroom->subject); ?>)</span>
                                                </label>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                                    <?php $__currentLoopData = $lessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="flex items-start mb-3 pb-3 border-b border-gray-200 last:border-0 last:mb-0 last:pb-0"
                                             x-show="fuzzyMatch('<?php echo e($lesson->title); ?>', lessonSearch) || fuzzyMatch('<?php echo e($lesson->topic); ?>', lessonSearch)">
                                            <div class="flex items-center h-5">
                                                <input id="lesson_<?php echo e($lesson->id); ?>" name="lessons[]" value="<?php echo e($lesson->id); ?>"
                                                    type="checkbox"
                                                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label for="lesson_<?php echo e($lesson->id); ?>"
                                                    class="font-medium text-gray-700"><?php echo e($lesson->title); ?></label>
                                                <p class="text-gray-500"><?php echo e($lesson->topic); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <p x-show="lessonSearch !== '' && $el.parentNode.querySelectorAll('div[x-show]:not([style*=\'display: none\'])').length === 0" 
                                       class="text-center text-gray-400 text-sm italic py-2" style="display: none;">
                                        Tiada pelajaran dijumpai.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <button type="submit"
                                    class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg font-bold text-sm text-white uppercase tracking-wider shadow-md transform transition hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 bg-[#2454FF] hover:bg-blue-700">
                                    <?php echo e(__('Tugaskan Pelajaran')); ?>

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
                                        <?php $__empty_1 = true; $__currentLoopData = $assignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    <?php echo e($assignment->lesson->title ?? 'Unknown Lesson'); ?>

                                                    <?php if($assignment->lesson->is_public): ?>
                                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                            Awam
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo e($assignment->classroom->name); ?> (<?php echo e($assignment->classroom->subject); ?>)
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo e($assignment->assigned_at ? \Carbon\Carbon::parse($assignment->assigned_at)->format('d M Y') : '-'); ?>

                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <button type="button" @click="openEdit(<?php echo e($assignment->lesson_id); ?>, '<?php echo e(addslashes($assignment->lesson->title)); ?>')" 
                                                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-md shadow-sm transition duration-150 ease-in-out mr-2">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                        Sunting
                                                    </button>
                                                    <form action="<?php echo e(route('assignments.destroy', $assignment->id)); ?>" method="POST" class="inline-block" onsubmit="return confirm('Adakah anda pasti mahu memadam tugasan ini?');">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
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
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr>
                                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Tiada tugasan ditemui.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- ACTIVITY TAB (Calendar View) -->
                    <div x-show="activeTab === 'activity'" style="display: none;">
                        
                        <!-- Create Activity Button -->
                        <div class="mb-6">
                            <a href="<?php echo e(route('activities.create')); ?>" 
                                class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg font-bold text-sm text-white uppercase tracking-wider shadow-md transform transition hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 bg-green-600 hover:bg-green-700">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Cipta Aktiviti Baru
                            </a>
                        </div>

                        <!-- Activities List -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                            <div class="p-6 border-b border-gray-200">
                                <h3 class="text-lg font-bold text-gray-900 mb-4">Senarai Aktiviti Anda</h3>
                                
                                <?php
                                    $allActivities = collect($games ?? [])->merge($quizzes ?? [])->sortByDesc('created_at');
                                ?>

                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Aktiviti</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tempoh (min)</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tindakan</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php $__empty_1 = true; $__currentLoopData = $allActivities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <tr class="hover:bg-gray-50 transition-colors">
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            <?php echo e($activity->title); ?>

                                                            <?php if($activity->is_public): ?>
                                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                                    Awam
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($activity->type === 'Quiz' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'); ?>">
                                                            <?php echo e($activity->type); ?>

                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        <?php echo e($activity->duration ?? '-'); ?>

                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                                        <!-- View Button -->
                                                        <a href="<?php echo e(route('activities.show', $activity->id)); ?>" 
                                                            class="inline-flex items-center px-3 py-1.5 bg-gray-600 hover:bg-gray-700 text-white text-xs font-semibold rounded-md shadow-sm transition duration-150 ease-in-out">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                            </svg>
                                                            Lihat
                                                        </a>

                                                        <!-- Assign Button -->
                                                        <a href="<?php echo e(route('schedule.index', ['activity_id' => $activity->id, 'tab' => 'activity'])); ?>" 
                                                            class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-md shadow-sm transition duration-150 ease-in-out">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                            </svg>
                                                            Tugaskan
                                                        </a>

                                                        <!-- Edit Button -->
                                                        <a href="<?php echo e(route('activities.edit', $activity->id)); ?>" 
                                                            class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-md shadow-sm transition duration-150 ease-in-out">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                            </svg>
                                                            Sunting
                                                        </a>

                                                        <!-- Delete Button -->
                                                        <form action="<?php echo e(route('activities.destroy', $activity->id)); ?>" method="POST" class="inline-block" onsubmit="return confirm('Adakah anda pasti mahu memadam aktiviti ini?');">
                                                            <?php echo csrf_field(); ?>
                                                            <?php echo method_field('DELETE'); ?>
                                                            <button type="submit" 
                                                                class="inline-flex items-center px-3 py-1.5 bg-white border border-red-300 text-red-600 hover:bg-red-50 text-xs font-semibold rounded-md shadow-sm transition duration-150 ease-in-out">
                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                </svg>
                                                                Padam
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <tr>
                                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                                        <div class="flex flex-col items-center">
                                                            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                            </svg>
                                                            <p class="text-sm font-medium">Tiada aktiviti dijumpai.</p>
                                                            <p class="text-xs text-gray-400 mt-1">Cipta aktiviti pertama anda menggunakan butang di atas.</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                            

                    </div>
                </div>
            </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div x-show="editModalOpen" class="fixed z-50 inset-0 overflow-y-auto" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="editModalOpen = false">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form method="POST" action="<?php echo e(route('assignments.store')); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="lessons[]" :value="targetLessonId">

                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Edit Assignment: <span x-text="targetTitle"></span>
                            </h3>
                            <div class="mt-4">
                                <!-- Reusing Multi-Select Logic for Edit -->
                                <!-- Search Input -->
                                <div class="mb-2">
                                    <input x-model="editSearch" type="text" placeholder="Search classes to re-assign..." 
                                        class="w-full border-gray-300 rounded-md text-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div class="border border-gray-200 rounded-md max-h-60 overflow-y-auto bg-gray-50 p-2 space-y-2">
                                    <!-- Public Option -->
                                    <div class="flex items-center p-2 bg-green-50 rounded-md border border-green-100"
                                         x-show="'public'.includes(editSearch.toLowerCase())">
                                        <input id="edit_public" name="is_public" type="checkbox" value="1"
                                            x-model="editIsPublic"
                                            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                        <label for="edit_public" class="ml-2 block text-sm font-bold text-green-900 cursor-pointer w-full">
                                            Public (All Students)
                                        </label>
                                    </div>

                                    <!-- Class Options -->
                                    <?php $__currentLoopData = $classrooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classroom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="flex items-center p-2 bg-white rounded-md border border-gray-200 hover:bg-indigo-50"
                                             x-show="$el.textContent.toLowerCase().includes(editSearch.toLowerCase())">
                                            <input id="edit_class_<?php echo e($classroom->id); ?>" name="classroom_ids[]" type="checkbox" value="<?php echo e($classroom->id); ?>"
                                                :checked="editClassroomIds.includes(<?php echo e($classroom->id); ?>)"
                                                @change="toggleEditClass(<?php echo e($classroom->id); ?>)"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                            <label for="edit_class_<?php echo e($classroom->id); ?>" class="ml-2 block text-sm text-gray-900 cursor-pointer w-full">
                                                <?php echo e($classroom->name); ?>

                                            </label>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">Uncheck to remove assignment. Check to add.</p>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end gap-3">
                            <button type="button" @click="editModalOpen = false" 
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
        <!-- Edit Activity Modal -->
        <div id="manual-edit-modal" x-show="editActModalOpen" class="fixed z-50 inset-0 overflow-y-auto" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="editActModalOpen = false">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form method="POST" action="<?php echo e(route('schedule.store')); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="activity_id">
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
                                
                                <div id="manual-public-warning" style="display: none;" class="mt-2 text-xs text-amber-700 bg-amber-50 p-2 rounded border border-amber-200">
                                    <span class="font-bold">Nota:</span> Aktiviti Awam tidak mempunyai tarikh akhir (cannot reassign).
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
                                    <?php $__currentLoopData = $classrooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classroom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="flex items-center p-2 bg-white rounded-md border border-gray-200 hover:bg-indigo-50 manual-class-item">
                                            <input id="edit_act_class_<?php echo e($classroom->id); ?>" name="classroom_ids[]" type="checkbox" value="<?php echo e($classroom->id); ?>"
                                                class="manual-class-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                            <label for="edit_act_class_<?php echo e($classroom->id); ?>" class="ml-2 block text-sm text-gray-900 cursor-pointer w-full">
                                                <?php echo e($classroom->name); ?>

                                            </label>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>

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
</div><?php /**PATH C:\xampp\htdocs\Material\resources\views/assignments/create.blade.php ENDPATH**/ ?>