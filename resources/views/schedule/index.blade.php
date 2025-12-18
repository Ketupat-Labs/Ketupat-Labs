<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Mengendalikan Aktiviti</h2>
                <p class="text-gray-600">Tetapkan tarikh akhir untuk pelajaran dan pantau perkembangan pelajar</p>
            </div>

            @if($isTeacher)
                <!-- Class Selection (Teacher Only) -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form method="GET" action="{{ route('schedule.index') }}" class="flex items-center gap-4">
                            <div class="w-full max-w-xs">
                                <label for="classroom_id" class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                                <select name="classroom_id" id="classroom_id" onchange="this.form.submit()"
                                    class="block w-full pl-3 pr-10 py-2 text-base text-gray-900 bg-white border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm">
                                    <option value="" disabled {{ !$selectedClass ? 'selected' : '' }}>Sila Pilih Kelas</option>
                                    <option value="public" class="font-bold text-green-600" {{ request('classroom_id') == 'public' ? 'selected' : '' }}>-- Public (Semua Pelajar) --</option>
                                    @foreach($classrooms as $classroom)
                                        <option value="{{ $classroom->id }}" class="text-gray-900 bg-white" {{ ($selectedClass && $selectedClass->id == $classroom->id) ? 'selected' : '' }}>
                                            {{ $classroom->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Calendar Section -->
                <div class="lg:col-span-3">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg h-full">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <!-- Simple Calendar Implementation or FullCalendar -->
                            <!-- For simplicity and matching the screenshot, we can build a custom month view using PHP/Blade logic or a lightweight JS picker. 
                                However, constructing a full navigable calendar in pure Blade is complex. 
                                I will provide a static current month view for now, or a simple JS based one. 
                                Let's use a simple Grid layout for the current month. -->

                            @php
                                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                                $firstDayOfMonth = date('N', strtotime("$year-$month-01")); // 1 (Mon) - 7 (Sun)

                                // Malay Month Names
                                $malayMonths = [
                                    1 => 'Januari',
                                    2 => 'Februari',
                                    3 => 'Mac',
                                    4 => 'April',
                                    5 => 'Mei',
                                    6 => 'Jun',
                                    7 => 'Julai',
                                    8 => 'Ogos',
                                    9 => 'September',
                                    10 => 'Oktober',
                                    11 => 'November',
                                    12 => 'Disember'
                                ];
                                $currentMonthName = $malayMonths[$month] . ' ' . $year;

                                // Navigation calculation
                                $prevMonth = $month - 1;
                                $prevYear = $year;
                                if ($prevMonth < 1) {
                                    $prevMonth = 12;
                                    $prevYear--;
                                }

                                $nextMonth = $month + 1;
                                $nextYear = $year;
                                if ($nextMonth > 12) {
                                    $nextMonth = 1;
                                    $nextYear++;
                                }
                            @endphp

                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold text-blue-600 flex items-center gap-2">
                                    📅 {{ $currentMonthName }}
                                </h3>
                                <div class="flex gap-2">
                                    <a href="{{ route('schedule.index', array_merge(request()->all(), ['month' => $prevMonth, 'year' => $prevYear])) }}"
                                        class="px-3 py-1 text-sm border rounded hover:bg-gray-50 text-gray-600 no-underline">
                                        ← Sebelum
                                    </a>
                                    <a href="{{ route('schedule.index', array_merge(request()->all(), ['month' => date('n'), 'year' => date('Y')])) }}"
                                        class="px-3 py-1 text-sm border rounded hover:bg-gray-50 text-gray-600 no-underline">
                                        Hari Ini
                                    </a>
                                    <a href="{{ route('schedule.index', array_merge(request()->all(), ['month' => $nextMonth, 'year' => $nextYear])) }}"
                                        class="px-3 py-1 text-sm border rounded hover:bg-gray-50 text-gray-600 no-underline">
                                        Seterusnya →
                                    </a>
                                </div>
                            </div>

                            <div class="gap-1 text-center mb-2"
                                style="display: grid; grid-template-columns: repeat(7, 1fr);">
                                <div class="text-xs font-bold text-gray-500 uppercase">Ahad</div>
                                <div class="text-xs font-bold text-gray-500 uppercase">Isnin</div>
                                <div class="text-xs font-bold text-gray-500 uppercase">Selasa</div>
                                <div class="text-xs font-bold text-gray-500 uppercase">Rabu</div>
                                <div class="text-xs font-bold text-gray-500 uppercase">Khamis</div>
                                <div class="text-xs font-bold text-gray-500 uppercase">Jumaat</div>
                                <div class="text-xs font-bold text-gray-500 uppercase">Sabtu</div>
                            </div>

                            <div class="gap-1 mobile-calendar"
                                style="display: grid; grid-template-columns: repeat(7, 1fr);">
                                {{-- Empty slots for previous month --}}
                                @for($i = 0; $i < ($firstDayOfMonth % 7); $i++)
                                    <div class="border border-gray-100 bg-gray-50" style="min-height: 100px;"></div>
                                @endfor

                                {{-- Days of the month --}}
                                @for($day = 1; $day <= $daysInMonth; $day++)
                                    <div class="border border-gray-200 bg-white p-1 relative hover:bg-gray-50 transition cursor-pointer group flex flex-col gap-1 overflow-y-auto"
                                        style="min-height: 140px; max-height: 140px;">
                                        <div class="flex justify-between items-start sticky top-0 bg-white z-10 p-1">
                                            <span
                                                class="text-sm font-semibold {{ ($day == date('j') && $month == date('n') && $year == date('Y')) ? 'text-white bg-blue-600 px-2 py-0.5 rounded-full' : 'text-gray-700' }}">
                                                {{ $day }}
                                            </span>
                                        </div>

                                        {{-- Check for events on this day --}}
                                        @foreach($events as $event)
                                            @php
                                                $eventDate = date('j', strtotime($event['start']));
                                                $eventMonth = date('n', strtotime($event['start']));
                                                $eventYear = date('Y', strtotime($event['start']));
                                            @endphp
                                            @if($eventDate == $day && $eventMonth == $month && $eventYear == $year)
                                                <div class="text-xs bg-indigo-50 text-indigo-700 p-1.5 rounded border border-indigo-100 hover:bg-indigo-100 transition shadow-sm text-left"
                                                    title="{{ $event['title'] }}&#010;{{ $event['notes'] }}">
                                                    <span
                                                        class="font-bold block truncate">{{ Str::limit($event['title'], 20) }}</span>
                                                    @if($event['notes'])
                                                        <span
                                                            class="block text-[10px] text-gray-500 truncate italic">{{ $event['notes'] }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endfor
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Sidebar: Form and Upcoming -->
                <div class="lg:col-span-1 space-y-6">

                    @if($isTeacher)
                        @if($selectedLessonId)
                             @php
                                $targetLesson = $lessons->firstWhere('id', $selectedLessonId);
                             @endphp
                             <!-- Assign Lesson Form -->
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-6 bg-white border-b border-gray-200">
                                    <h3 class="font-bold text-gray-900 mb-4">Tugaskan Pelajaran</h3>
                                    <p class="text-sm text-gray-600 mb-4">Anda sedang menugaskan: <strong>{{ $targetLesson->title ?? 'Pelajaran' }}</strong></p>

                                    <form action="{{ route('schedule.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="lesson_id" value="{{ $selectedLessonId }}">

                                        <!-- Multi-Select Class Dropdown -->
                                        <div class="mb-4" x-data="{ 
                                            open: false, 
                                            search: '',
                                            toggle() { this.open = !this.open; if(this.open) $nextTick(() => $refs.searchInput.focus()) }
                                        }">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Sasaran (Boleh pilih lebih dari satu)</label>
                                            
                                            <!-- Dropdown Trigger Button -->
                                            <button type="button" @click="toggle()"
                                                class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                                                <span class="block truncate text-gray-600">
                                                    Klik untuk memilih kelas... (Pilihan Semasa: {{ $isLessonPublic ? 'Public' : '' }} {{ count($assignedClassroomIds) > 0 ? ($isLessonPublic ? '+ ' : '') . count($assignedClassroomIds) . ' Kelas' : ($isLessonPublic ? '' : 'Tiada') }})
                                                </span>
                                                <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </span>
                                            </button>

                                            <!-- Dropdown Content -->
                                            <div x-show="open" @click.away="open = false" 
                                                class="absolute z-10 mt-1 w-full sm:w-[500px] bg-white shadow-lg rounded-md border border-gray-200 py-1 text-base ring-1 ring-black ring-opacity-5 overflow-hidden focus:outline-none sm:text-sm"
                                                style="display: none;">
                                                
                                                <!-- Search Input -->
                                                <div class="p-2 border-b border-gray-100 bg-gray-50">
                                                    <input x-ref="searchInput" x-model="search" type="text" placeholder="Cari kelas..." 
                                                        class="w-full border-gray-300 rounded-md text-sm p-2 focus:ring-purple-500 focus:border-purple-500">
                                                </div>

                                                <div class="max-h-60 overflow-y-auto p-2 space-y-2">
                                                    <!-- Public Option -->
                                                    <div class="flex items-center p-2 bg-green-50 rounded-md border border-green-100"
                                                         x-show="$el.textContent.toLowerCase().includes(search.toLowerCase())">
                                                        <input id="public_option" name="is_public" type="checkbox" value="1" 
                                                            {{ $isLessonPublic ? 'checked' : '' }}
                                                            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                                        <label for="public_option" class="ml-2 block text-sm font-bold text-green-900 cursor-pointer w-full">
                                                            Public (Semua Pelajar)
                                                        </label>
                                                    </div>

                                                    <!-- Class Options -->
                                                    @foreach($classrooms as $c)
                                                        <div class="flex items-center p-2 bg-white rounded-md border border-gray-200 hover:bg-indigo-50"
                                                             x-show="$el.textContent.toLowerCase().includes(search.toLowerCase())">
                                                            <input id="class_{{ $c->id }}" name="classroom_ids[]" type="checkbox" value="{{ $c->id }}"
                                                                {{ in_array($c->id, $assignedClassroomIds) ? 'checked' : '' }}
                                                                class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                                                            <label for="class_{{ $c->id }}" class="ml-2 block text-sm text-gray-900 cursor-pointer w-full">
                                                                {{ $c->name }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <div class="bg-gray-50 px-4 py-2 text-xs text-gray-500 text-center border-t border-gray-100">
                                                    Klik di luar untuk menutup
                                                </div>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1">Gunakan drop down di atas untuk menambah atau membuang kelas.</p>
                                        </div>

                                        <button type="submit"
                                            class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                            Kemas Kini Tugasan
                                        </button>
                                        
                                        <div class="mt-4 text-center">
                                            <a href="{{ route('schedule.index') }}" class="text-xs text-gray-500 hover:text-gray-700 underline">Batal / Kembali</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @elseif($selectedClass)
                        <!-- Set Due Date Form (Existing Activity Form) -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <h3 class="font-bold text-gray-900 mb-4">Jadualkan Aktiviti</h3>

                                <form action="{{ route('schedule.store') }}" method="POST">
                                    @csrf
                                    <!-- Multi-Select Class Dropdown (Activity) -->
                                    <div class="mb-4" x-data="{ 
                                        open: false, 
                                        search: '',
                                        toggle() { this.open = !this.open; if(this.open) $nextTick(() => $refs.searchInput.focus()) }
                                    }">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Sasaran (Boleh pilih lebih dari satu)</label>
                                        
                                        <!-- Dropdown Trigger Button -->
                                        <button type="button" @click="toggle()"
                                            class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <span class="block truncate text-gray-600">
                                                Klik untuk memilih kelas... (Pilihan Semasa: {{ $isLessonPublic ? 'Public' : '' }} {{ count($assignedClassroomIds) > 0 ? ($isLessonPublic ? '+ ' : '') . count($assignedClassroomIds) . ' Kelas' : ($isLessonPublic ? '' : 'Tiada') }})
                                            </span>
                                            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </button>

                                        <!-- Dropdown Content -->
                                        <div x-show="open" @click.away="open = false" 
                                            class="absolute z-10 mt-1 w-full sm:w-[500px] bg-white shadow-lg rounded-md border border-gray-200 py-1 text-base ring-1 ring-black ring-opacity-5 overflow-hidden focus:outline-none sm:text-sm"
                                            style="display: none;">
                                            
                                            <!-- Search Input -->
                                            <div class="p-2 border-b border-gray-100 bg-gray-50">
                                                <input x-ref="searchInput" x-model="search" type="text" placeholder="Cari kelas..." 
                                                    class="w-full border-gray-300 rounded-md text-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                                            </div>

                                            <div class="max-h-60 overflow-y-auto p-2 space-y-2">
                                                <!-- Public Option -->
                                                <div class="flex items-center p-2 bg-green-50 rounded-md border border-green-100"
                                                     x-show="$el.textContent.toLowerCase().includes(search.toLowerCase())">
                                                    <input id="public_option_act" name="is_public" type="checkbox" value="1" 
                                                        {{ $isLessonPublic ? 'checked' : '' }}
                                                        class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                                    <label for="public_option_act" class="ml-2 block text-sm font-bold text-green-900 cursor-pointer w-full">
                                                        Public (Semua Pelajar)
                                                    </label>
                                                </div>

                                                <!-- Class Options -->
                                                @foreach($classrooms as $c)
                                                    <div class="flex items-center p-2 bg-white rounded-md border border-gray-200 hover:bg-indigo-50"
                                                         x-show="$el.textContent.toLowerCase().includes(search.toLowerCase())">
                                                        <input id="class_act_{{ $c->id }}" name="classroom_ids[]" type="checkbox" value="{{ $c->id }}"
                                                            {{ in_array($c->id, $assignedClassroomIds) ? 'checked' : '' }}
                                                            {{ ($selectedClass && $selectedClass->id == $c->id && count($assignedClassroomIds)==0) ? 'checked' : '' }}
                                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                        <label for="class_act_{{ $c->id }}" class="ml-2 block text-sm text-gray-900 cursor-pointer w-full">
                                                            {{ $c->name }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="bg-gray-50 px-4 py-2 text-xs text-gray-500 text-center border-t border-gray-100">
                                                Klik di luar untuk menutup
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="activity_id" class="block text-sm font-medium text-gray-700 mb-1">Pilih
                                            Aktiviti</label>
                                        <select name="activity_id" id="activity_id"
                                            class="block w-full text-base text-gray-900 bg-white border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm"
                                            required>
                                            <option value="" disabled {{ !$preselectedActivityId ? 'selected' : '' }}>Sila Pilih Aktiviti</option>
                                            
                                            @if($games->isNotEmpty())
                                            <optgroup label="Permainan Ciptaan Guru">
                                                @foreach($games as $game)
                                                    <option value="{{ $game->id }}" {{ ($preselectedActivityId == $game->id) ? 'selected' : '' }}>{{ $game->title }} ({{ $game->suggested_duration }})</option>
                                                @endforeach
                                            </optgroup>
                                            @endif

                                            @if($quizzes->isNotEmpty())
                                            <optgroup label="Kuiz Tambahan">
                                                @foreach($quizzes as $quiz)
                                                    <option value="{{ $quiz->id }}" {{ ($preselectedActivityId == $quiz->id) ? 'selected' : '' }}>{{ $quiz->title }} ({{ $quiz->suggested_duration }})</option>
                                                @endforeach
                                            </optgroup>
                                            @endif
                                        </select>
                                    </div>

                                    <div class="mb-4">
                                        <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1">Tarikh
                                            Akhir</label>
                                        <input type="date" name="due_date" id="due_date"
                                            class="block w-full text-base text-gray-900 bg-white border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm"
                                            required>
                                    </div>

                                    <div class="mb-4">
                                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Nota
                                            (Pilihan)</label>
                                        <textarea name="notes" id="notes" rows="2"
                                            class="block w-full text-base text-gray-900 bg-white border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm"
                                            placeholder="Tambah nota peringatan..."></textarea>
                                    </div>

                                    <button type="submit"
                                        class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Tetapkan Tarikh Akhir
                                    </button>
                                </form>
                            </div>
                        </div>
                        @else
                           <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center text-gray-500 hidden lg:block">
                               <p>Pilih kelas di kiri untuk menjadualkan aktiviti, atau pilih "Assign" dari senarai Pelajaran.</p>
                           </div>
                        @endif
                    @endif

                    <!-- Upcoming Activities List -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="font-bold text-gray-900 mb-4">Aktiviti Terkini</h3>

                            @forelse($events as $event)
                                @php
                                    $eventStart = strtotime($event['start']);
                                    $isFuture = $eventStart >= strtotime('today');
                                    // Default to false if not set (e.g. for teachers)
                                    $isCompleted = $event['is_completed'] ?? false;
                                @endphp
                                @if($isFuture || $isCompleted) 
                                    {{-- Show if future OR completed (to show history/status? User said "available lessons page", usually implies upcoming/active. If completed, maybe keep it but mark it.) --}}
                                    
                                    <div class="mb-4 pb-4 border-b border-gray-100 last:border-0 last:mb-0 last:pb-0">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2">
                                                    <h4 class="text-sm font-bold {{ $isCompleted ? 'text-gray-500 line-through' : 'text-gray-900' }}">
                                                        {{ $event['title'] }}
                                                    </h4>
                                                    @if($isCompleted)
                                                        <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full flex items-center gap-1">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                            Selesai
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                <p class="text-xs {{ $isCompleted ? 'text-gray-400' : 'text-red-600 font-medium' }}">
                                                    Tarikh Akhir: {{ date('d M Y', $eventStart) }}
                                                </p>
                                                
                                                @if($event['notes'])
                                                    <p class="text-xs text-gray-400 mt-1 italic">📝 {{ $event['notes'] }}</p>
                                                @endif
                                            </div>
                                            
                                            @if($isTeacher)
                                                {{-- Delete Button (Teacher Only) --}}
                                                <form action="{{ route('schedule.destroy', $event['id']) }}" method="POST" class="ml-4" onsubmit="return confirm('Adakah anda pasti mahu membatalkan tugasan aktiviti ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-400 hover:text-red-600 transition p-1 rounded-full hover:bg-red-50" title="Batalkan Tugasan">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <p class="text-sm text-gray-500 text-center py-4">Tiada aktiviti dijadualkan.</p>
                            @endforelse

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>