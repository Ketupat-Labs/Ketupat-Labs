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
                                    <option value="" disabled {{ !$selectedClass ? 'selected' : '' }}>Sila Pilih Kelas
                                    </option>
                                    @foreach($classrooms as $classroom)
                                        <option value="{{ $classroom->id }}" class="text-gray-900 bg-white" {{ ($selectedClass && $selectedClass->id == $classroom->id) ? 'selected' : '' }}>
                                            {{ $classroom->title }}
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

                    @if($isTeacher && $selectedClass)
                        <!-- Set Due Date Form -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <h3 class="font-bold text-gray-900 mb-4">Jadualkan Aktiviti</h3>

                                <form action="{{ route('schedule.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="classroom_id" value="{{ $selectedClass->id }}">

                                    <div class="mb-4">
                                        <label for="activity_id" class="block text-sm font-medium text-gray-700 mb-1">Pilih
                                            Aktiviti</label>
                                        <select name="activity_id" id="activity_id"
                                            class="block w-full text-base text-gray-900 bg-white border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm"
                                            required>
                                            <option value="" disabled {{ !$preselectedActivityId ? 'selected' : '' }}>Sila Pilih Aktiviti</option>
                                            @foreach($activities as $activity)
                                                <option value="{{ $activity->id }}" {{ ($preselectedActivityId == $activity->id) ? 'selected' : '' }}>{{ $activity->title }} ({{ $activity->suggested_duration }})</option>
                                            @endforeach
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
                    @endif

                    <!-- Upcoming Activities List -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="font-bold text-gray-900 mb-4">Aktiviti Terkini</h3>

                            @forelse($events as $event)
                                @php
                                    $eventStart = strtotime($event['start']);
                                    $isFuture = $eventStart >= strtotime('today');
                                @endphp
                                @if($isFuture)
                                    <div class="mb-4 pb-4 border-b border-gray-100 last:border-0 last:mb-0 last:pb-0">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h4 class="text-sm font-bold text-gray-900">{{ $event['title'] }}</h4>
                                                <p class="text-xs text-gray-500">Tarikh Akhir:
                                                    {{ date('d M Y', $eventStart) }}
                                                </p>
                                                @if($event['notes'])
                                                    <p class="text-xs text-gray-400 mt-1 italic">📝 {{ $event['notes'] }}</p>
                                                @endif
                                            </div>
                                            <!-- Delete functionality could be added here later -->
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