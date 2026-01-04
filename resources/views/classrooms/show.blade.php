<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $classroom->name }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Back Button -->
            <a href="{{ route('classrooms.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-6 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Senarai Kelas
            </a>

            {{-- Success Message --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6"
                    role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6"
                    role="alert">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Classroom Banner --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-8 border border-gray-200">
                <div class="bg-gradient-to-r from-[#2454FF] to-[#1a3fcc] p-8 text-white">
                    <h1 class="text-4xl font-bold mb-2">{{ $classroom->name }}</h1>
                    <div class="flex items-center space-x-4 opacity-90">
                        <span class="text-lg">{{ $classroom->subject }}</span>
                        @if($classroom->year)
                            <span>&bull;</span>
                            <span class="text-lg">{{ $classroom->year }}</span>
                        @endif
                    </div>
                </div>
                <div class="p-4 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        Dicipta: <span
                            class="font-bold text-gray-700">{{ $classroom->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="flex gap-3">
                        @if($user->role === 'teacher')
                            <a href="{{ route('lessons.create') }}"
                                class="inline-flex items-center px-4 py-2 bg-[#F26430] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cipta Pelajaran Baharu
                            </a>
                            <a href="{{ route('activities.create') }}"
                                class="inline-flex items-center px-4 py-2 bg-[#5FAD56] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cipta Aktiviti Baharu
                            </a>
                        @endif
                        @if($forum)
                            <a href="{{ route('forum.detail', $forum->id) }}"
                                class="inline-flex items-center px-4 py-2 bg-[#2454FF] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Lihat Forum
                            </a>
                        @elseif($user->role === 'teacher')
                            <button onclick="createClassroomForum({{ $classroom->id }})"
                                class="inline-flex items-center px-4 py-2 bg-[#2454FF] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cipta Forum
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- LEFT COLUMN: People & Roster --}}
                <div class="lg:col-span-1 space-y-6">

                    {{-- Teachers Card --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-bold text-[#2454FF] mb-4 border-b border-gray-100 pb-2">Guru Kelas</h3>
                        <a href="{{ route('profile.show', $classroom->teacher->id) }}" class="flex items-center space-x-3 hover:opacity-80 transition-opacity cursor-pointer">
                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-[#2454FF] font-bold overflow-hidden flex-shrink-0">
                                @if($classroom->teacher->avatar_url)
                                    <img src="{{ asset($classroom->teacher->avatar_url) }}" 
                                         alt="{{ $classroom->teacher->full_name }}" 
                                         class="h-full w-full object-cover"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div style="display: none; width: 100%; height: 100%; align-items: center; justify-content: center;">
                                        {{ strtoupper(substr($classroom->teacher->full_name, 0, 1)) }}
                                    </div>
                                @else
                                    {{ strtoupper(substr($classroom->teacher->full_name, 0, 1)) }}
                                @endif
                            </div>
                            <div>
                                <p class="text-gray-900 font-medium hover:text-[#2454FF] transition-colors">{{ $classroom->teacher->full_name }}</p>
                                <p class="text-gray-500 text-xs">Sejak: {{ $classroom->teacher->created_at->format('M Y') }}</p>
                            </div>
                        </a>
                    </div>

                    {{-- Students Card --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex justify-between items-center mb-4 border-b border-gray-100 pb-2">
                            <h3 class="text-lg font-bold text-[#5FAD56]">Senarai Pelajar</h3>
                            <span
                                class="text-xs font-semibold bg-green-100 text-green-800 px-2 py-1 rounded-full">{{ $classroom->students->count() }}
                                Pelajar</span>
                        </div>

                        {{-- Add Student Form (Teacher Only) --}}
                        @if($user->role === 'teacher')
                            <div class="mb-6 bg-gray-50 p-3 rounded-lg border border-gray-200">
                                <p class="text-xs font-bold text-gray-500 mb-2 uppercase">Tambah Pelajar</p>
                                <form method="POST" action="{{ route('classrooms.students.add', $classroom) }}">
                                    @csrf
                                    <div class="flex space-x-2">
                                    <div x-data="{ 
                                            open: false, 
                                            search: '', 
                                            selectedIds: [],
                                            toggleStudent(id) {
                                                if (this.selectedIds.includes(id)) {
                                                    this.selectedIds = this.selectedIds.filter(i => i !== id);
                                                } else {
                                                    this.selectedIds.push(id);
                                                }
                                            }
                                        }" 
                                        class="relative flex-1"
                                        @click.away="open = false">
                                        
                                        <template x-for="id in selectedIds" :key="id">
                                            <input type="hidden" name="student_ids[]" :value="id">
                                        </template>

                                        <div class="relative">
                                            <input 
                                                x-ref="searchInput"
                                                type="text" 
                                                x-model="search"
                                                @focus="open = true"
                                                @input="open = true"
                                                :placeholder="selectedIds.length > 0 ? selectedIds.length + ' Pelajar Terpilih' : 'Cari Pelajar...'"
                                                class="w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                autocomplete="off"
                                            >
                                            <span 
                                                @click="$refs.searchInput.focus(); open = !open"
                                                class="absolute inset-y-0 right-0 flex items-center pr-2 cursor-pointer group"
                                            >
                                                <svg class="h-5 w-5 text-gray-400 group-hover:text-indigo-500 transition-colors" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </div>

                                        <div x-show="open" 
                                            x-transition:leave="transition ease-in duration-100"
                                            x-transition:leave-start="opacity-100"
                                            x-transition:leave-end="opacity-0"
                                            class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
                                            style="display: none;">
                                            
                                            <ul class="pt-1">
                                                @foreach($availableStudents as $student)
                                                    <li class="cursor-pointer select-none relative py-2 pl-3 pr-9 text-gray-900 hover:bg-slate-50 transition-colors group border-b border-gray-50 last:border-0"
                                                        x-show="'{{ strtolower($student->full_name) }} {{ strtolower($student->email) }}'.includes(search.toLowerCase())"
                                                        @click.stop="toggleStudent('{{ $student->id }}')">
                                                        <div class="flex items-center space-x-3 w-full">
                                                            <input type="checkbox" :checked="selectedIds.includes('{{ $student->id }}')" @click.stop="toggleStudent('{{ $student->id }}')" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                            <div class="flex flex-col flex-1">
                                                                <span class="block truncate font-medium text-gray-900">
                                                                    {{ $student->full_name }}
                                                                </span>
                                                                <span class="block truncate text-gray-500 text-xs">
                                                                    {{ $student->email }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </li>
                                                @endforeach
                                                <li x-show="search !== '' && $el.parentNode.querySelectorAll('li[x-show]:not([style*=\'display: none\'])').length === 0" class="cursor-default select-none relative py-2 pl-3 pr-9 text-gray-500 italic text-sm">
                                                    Tiada pelajar dijumpai.
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                        <button type="submit"
                                            class="p-2 bg-[#5FAD56] text-white rounded-md hover:bg-green-700 transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif

                        {{-- Student List --}}
                        <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                            @forelse($classroom->students as $student)
                                <div
                                    class="flex items-center justify-between group p-2 hover:bg-gray-50 rounded transition">
                                    <a href="{{ route('profile.show', $student->id) }}" class="flex items-center space-x-3 flex-1 hover:opacity-80 transition-opacity cursor-pointer">
                                        <div class="h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-bold text-xs ring-2 ring-transparent group-hover:ring-purple-200 transition overflow-hidden flex-shrink-0">
                                            @if($student->avatar_url)
                                                <img src="{{ asset($student->avatar_url) }}" 
                                                     alt="{{ $student->full_name }}" 
                                                     class="h-full w-full object-cover"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div style="display: none; width: 100%; height: 100%; align-items: center; justify-content: center;">
                                                    {{ strtoupper(substr($student->full_name, 0, 1)) }}
                                                </div>
                                            @else
                                                {{ strtoupper(substr($student->full_name, 0, 1)) }}
                                            @endif
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm text-gray-700 font-medium hover:text-[#5FAD56] transition-colors line-clamp-1">{{ $student->full_name }}</span>
                                            <span class="text-xs text-gray-400 line-clamp-1">{{ $student->email }}</span>
                                        </div>
                                    </a>
                                    @if($user->role === 'teacher')
                                        <form method="POST"
                                            action="{{ route('classrooms.students.remove', [$classroom, $student->id]) }}"
                                            onsubmit="return confirm('Keluarkan {{ $student->full_name }} daripada kelas ini?');"
                                            class="ml-2"
                                            onclick="event.stopPropagation();">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-gray-300 hover:text-red-500 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <p class="text-gray-400 text-sm italic">Tiada pelajar yang berdaftar lagi.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN: Content Lists --}}
                <div class="lg:col-span-2" x-data="{ 
                    search: '',
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
                    
                    {{-- Search Bar --}}
                    <div class="mb-6">
                        <div class="relative">
                            <input type="text" x-model="search" placeholder="Cari pelajaran atau aktiviti..." 
                                class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring focus:ring-blue-100 transition shadow-sm text-gray-700">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        
                        {{-- LESSONS COLUMN --}}
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col h-[600px]">
                            <div class="p-4 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                                <h3 class="font-bold text-gray-800 flex items-center">
                                    <span class="bg-[#2454FF] w-2 h-6 rounded-full mr-2"></span>
                                    Pelajaran
                                </h3>
                            </div>
                            <div class="p-4 overflow-y-auto flex-1 space-y-4">
                                @forelse($classroom->lessons->sortByDesc('created_at') as $lesson)
                                    <div class="bg-white border border-gray-100 rounded-lg p-4 shadow-sm hover:shadow-md transition duration-200"
                                         x-show="fuzzyMatch('{{ $lesson->title }}', search)"
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 transform scale-95"
                                         x-transition:enter-end="opacity-100 transform scale-100">
                                        
                                        <div class="flex justify-between items-start mb-2">
                                            <h4 class="font-bold text-[#2454FF] hover:underline leading-snug">
                                                <a href="{{ route('lesson.show', $lesson) }}">{{ $lesson->title }}</a>
                                            </h4>
                                            
                                            {{-- Status for Students --}}
                                            @if($user->role === 'student' && $lesson->enrollments->where('user_id', $user->id)->first())
                                                @php
                                                    $status = $lesson->enrollments->where('user_id', $user->id)->first()->status;
                                                    $statusColors = [
                                                        'completed' => 'bg-green-100 text-green-800',
                                                        'in_progress' => 'bg-yellow-100 text-yellow-800',
                                                        'not_started' => 'bg-gray-100 text-gray-600',
                                                    ];
                                                    $statusLabels = [
                                                        'completed' => 'Selesai',
                                                        'in_progress' => 'Sedang Berjalan',
                                                        'not_started' => 'Belum Bermula',
                                                    ];
                                                @endphp
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $statusColors[$status] ?? 'bg-gray-100' }}">
                                                    {{ $statusLabels[$status] ?? str_replace('_', ' ', ucfirst($status)) }}
                                                </span>
                                            @endif
                                        </div>

                                        <p class="text-xs text-gray-500 mb-3">{{ $lesson->topic }}</p>
                                        <p class="text-sm text-gray-600 line-clamp-2 mb-3">{{ $lesson->content }}</p>

                                        <div class="grid grid-cols-1 gap-2">
                                            <a href="{{ route('lesson.show', $lesson) }}" 
                                               class="text-center text-xs font-bold text-[#2454FF] bg-blue-50 py-2 rounded hover:bg-blue-100 transition">
                                                Buka Pelajaran
                                            </a>
                                            @if($user->role === 'teacher')
                                                <a href="{{ route('submission.index', ['lesson_id' => $lesson->id, 'classroom_id' => $classroom->id]) }}"
                                                   class="text-center text-xs font-bold text-[#5FAD56] bg-green-50 py-2 rounded hover:bg-green-100 transition">
                                                   Semak Tugasan ({{ $lesson->submissions->count() }})
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-center text-gray-400 text-sm italic py-4">Tiada pelajaran.</p>
                                @endforelse
                                <p x-show="search !== '' && $el.parentNode.querySelectorAll('div[x-show]:not([style*=\'display: none\'])').length === 0" 
                                   class="text-center text-gray-400 text-sm italic py-4" style="display: none;">
                                    Tiada pelajaran dijumpai.
                                </p>
                            </div>
                        </div>

                        {{-- ACTIVITIES COLUMN --}}
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col h-[600px]">
                            <div class="p-4 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                                <h3 class="font-bold text-gray-800 flex items-center">
                                    <span class="bg-purple-600 w-2 h-6 rounded-full mr-2"></span>
                                    Aktiviti
                                </h3>
                            </div>
                            <div class="p-4 overflow-y-auto flex-1 space-y-4">
                                @forelse($classroom->activityAssignments->sortByDesc('created_at') as $assignment)
                                    <div class="bg-white border border-gray-100 rounded-lg p-4 shadow-sm hover:shadow-md transition duration-200"
                                         x-show="fuzzyMatch('{{ $assignment->activity->title }}', search)"
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 transform scale-95"
                                         x-transition:enter-end="opacity-100 transform scale-100">
                                        
                                        <div class="flex justify-between items-start mb-2">
                                            <h4 class="font-bold text-purple-600 hover:underline leading-snug">
                                                <a href="{{ route('activities.show', $assignment->activity) }}">{{ $assignment->activity->title }}</a>
                                            </h4>
                                            <span class="text-[10px] font-bold bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">
                                                {{ $assignment->activity->type }}
                                            </span>
                                        </div>

                                        @if($assignment->due_date)
                                            <p class="text-xs text-red-500 font-bold mb-2">
                                                Tamat: {{ \Carbon\Carbon::parse($assignment->due_date)->format('d M Y') }}
                                            </p>
                                        @endif

                                        <p class="text-sm text-gray-600 line-clamp-2 mb-3">{{ $assignment->activity->description }}</p>

                                        <a href="{{ route('activities.show', $assignment->activity) }}" 
                                           class="block text-center text-xs font-bold text-purple-600 bg-purple-50 py-2 rounded hover:bg-purple-100 transition">
                                            Lihat Aktiviti
                                        </a>
                                    </div>
                                @empty
                                    <p class="text-center text-gray-400 text-sm italic py-4">Tiada aktiviti.</p>
                                @endforelse
                                <p x-show="search !== '' && $el.parentNode.querySelectorAll('div[x-show]:not([style*=\'display: none\'])').length === 0" 
                                   class="text-center text-gray-400 text-sm italic py-4" style="display: none;">
                                    Tiada aktiviti dijumpai.
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        async function createClassroomForum(classroomId) {
            if (!confirm('Cipta forum untuk kelas ini? Forum akan dapat dilihat oleh ahli kelas sahaja.')) {
                return;
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                const response = await fetch('/api/classroom/' + classroomId + '/create-forum', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'include',
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.status === 200) {
                    // Redirect to forum detail page
                    window.location.href = '/forum/' + data.data.forum_id;
                } else {
                    alert(data.message || 'Gagal mencipta forum');
                }
            } catch (error) {
                console.error('Error creating forum:', error);
                alert('Gagal mencipta forum. Sila cuba lagi.');
            }
        }
    </script>
</x-app-layout>