<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $classroom->name }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Success Message --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6"
                    role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
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
                        Class Code: <span
                            class="font-mono font-bold text-gray-700">{{ $classroom->id }}-{{ Str::upper(Str::random(4)) }}</span>
                        (Simulated)
                    </div>
                    @if($user->role === 'teacher')
                        <a href="{{ route('lessons.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-[#F26430] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Create New Lesson
                        </a>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- LEFT COLUMN: People & Roster --}}
                <div class="lg:col-span-1 space-y-6">

                    {{-- Teachers Card --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-bold text-[#2454FF] mb-4 border-b border-gray-100 pb-2">Instructors</h3>
                        <div class="flex items-center space-x-3">
                            <div
                                class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-[#2454FF] font-bold">
                                {{ substr($classroom->teacher->full_name, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-gray-900 font-medium">{{ $classroom->teacher->full_name }}</p>
                                <p class="text-gray-500 text-xs">Lead Teacher</p>
                            </div>
                        </div>
                    </div>

                    {{-- Students Card --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex justify-between items-center mb-4 border-b border-gray-100 pb-2">
                            <h3 class="text-lg font-bold text-[#5FAD56]">Class Roster</h3>
                            <span
                                class="text-xs font-semibold bg-green-100 text-green-800 px-2 py-1 rounded-full">{{ $classroom->students->count() }}
                                Students</span>
                        </div>

                        {{-- Add Student Form (Teacher Only) --}}
                        @if($user->role === 'teacher')
                            <div class="mb-6 bg-gray-50 p-3 rounded-lg border border-gray-200">
                                <p class="text-xs font-bold text-gray-500 mb-2 uppercase">Add Student</p>
                                <form method="POST" action="{{ route('classrooms.students.add', $classroom) }}">
                                    @csrf
                                    <div class="flex space-x-2">
                                        <select name="student_id" required
                                            class="flex-1 text-sm border-gray-300 rounded-md focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <option value="">Select...</option>
                                            @foreach($availableStudents as $student)
                                                <option value="{{ $student->id }}">{{ $student->full_name }}</option>
                                            @endforeach
                                        </select>
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
                                    <div class="flex items-center space-x-3">
                                        <div
                                            class="h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-bold text-xs ring-2 ring-transparent group-hover:ring-purple-200 transition">
                                            {{ substr($student->full_name, 0, 1) }}
                                        </div>
                                        <span class="text-sm text-gray-700 font-medium">{{ $student->full_name }}</span>
                                    </div>
                                    @if($user->role === 'teacher')
                                        <form method="POST"
                                            action="{{ route('classrooms.students.remove', [$classroom, $student->id]) }}"
                                            onsubmit="return confirm('Remove {{ $student->full_name }} from this class?');">
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
                                    <p class="text-gray-400 text-sm italic">No students enrolled yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN: Timeline & Content --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 min-h-[500px]">
                        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                            <span class="bg-[#2454FF] w-2 h-8 rounded-full mr-3"></span>
                            Course Timeline
                        </h3>

                        <div class="space-y-6">
                            @php
                                $timelineItems = $classroom->lessons->map(function($lesson) {
                                    $lesson->timeline_date = $lesson->created_at;
                                    $lesson->type_label = 'Lesson';
                                    return $lesson;
                                })->concat($classroom->activityAssignments->map(function($assignment) {
                                    $assignment->timeline_date = $assignment->created_at; // Or assigned_at
                                    $assignment->type_label = 'Activity';
                                    return $assignment;
                                }))->sortByDesc('timeline_date');
                            @endphp

                            @forelse($timelineItems as $item)
                                @php
                                    $isActivity = $item->type_label === 'Activity';
                                @endphp
                                <div class="relative pl-8 border-l-2 border-gray-200 pb-6 last:pb-0 last:border-0">
                                    {{-- Timeline Dot --}}
                                    <div
                                        class="absolute -left-[9px] top-0 bg-white border-4 {{ $isActivity ? 'border-purple-600' : 'border-[#2454FF]' }} h-4 w-4 rounded-full">
                                    </div>

                                    <div
                                        class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:shadow-md transition duration-200">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h4 class="text-lg font-bold text-gray-800 {{ $isActivity ? 'hover:text-purple-600' : 'hover:text-[#2454FF]' }} transition">
                                                    @if($isActivity)
                                                        <a href="{{ route('activities.show', $item->activity) }}">{{ $item->activity->title }}</a>
                                                        <span class="text-xs font-normal text-gray-500 ml-1">({{ $item->activity->suggested_duration }})</span>
                                                    @else
                                                        <a href="{{ route('lesson.show', $item) }}">{{ $item->title }}</a>
                                                    @endif
                                                </h4>
                                                @if($isActivity)
                                                     <p class="text-sm text-gray-500 mb-2">{{ $item->activity->type }} &bull; Assigned {{ $item->created_at->format('M d, Y') }}</p>
                                                     <p class="text-gray-600 text-sm mb-4">{{ Str::limit($item->activity->description, 120) }}</p>
                                                     @if($item->due_date)
                                                        <p class="text-xs text-red-500 font-bold">Due: {{ \Carbon\Carbon::parse($item->due_date)->format('M d, Y') }}</p>
                                                     @endif
                                                @else
                                                    <p class="text-sm text-gray-500 mb-2">{{ $item->topic }} &bull; Posted
                                                        {{ $item->created_at ? $item->created_at->format('M d, Y') : 'N/A' }}</p>
                                                    <p class="text-gray-600 text-sm mb-4">
                                                        {{ Str::limit($item->content, 120) }}</p>
                                                @endif
                                            </div>
                                            
                                            {{-- Status Badges --}}
                                            @if($user->role === 'teacher')
                                                @if(!$isActivity)
                                                    <div class="bg-blue-100 text-[#2454FF] text-xs font-bold px-2 py-1 rounded whitespace-nowrap ml-2">
                                                        {{ $item->submissions->whereIn('user_id', $classroom->students->pluck('id'))->count() }}
                                                        / {{ $classroom->students->count() }} Turned In
                                                    </div>
                                                @else
                                                    <div class="bg-purple-100 text-purple-600 text-xs font-bold px-2 py-1 rounded whitespace-nowrap ml-2">
                                                        Activity
                                                    </div>
                                                @endif
                                            @else
                                                @if(!$isActivity)
                                                    @php
                                                        $enrollment = $item->enrollments->first();
                                                        $status = $enrollment ? $enrollment->status : 'not_started';
                                                        $statusColors = [
                                                            'completed' => 'bg-green-100 text-green-800',
                                                            'in_progress' => 'bg-yellow-100 text-yellow-800',
                                                            'not_started' => 'bg-gray-100 text-gray-600',
                                                        ];
                                                        $statusLabel = [
                                                            'completed' => 'Completed',
                                                            'in_progress' => 'In Progress',
                                                            'not_started' => 'Not Started',
                                                        ];
                                                    @endphp
                                                    <div class="{{ $statusColors[$status] ?? 'bg-gray-100' }} text-xs font-bold px-2 py-1 rounded whitespace-nowrap ml-2 uppercase tracking-wide">
                                                        {{ $statusLabel[$status] ?? 'Not Started' }}
                                                    </div>
                                                @else
                                                     <!-- Activity Status Logic if needed, currently just showing it exists -->
                                                @endif
                                            @endif
                                        </div>

                                        <div class="flex space-x-3 mt-2">
                                            @if($isActivity)
                                                <a href="{{ route('activities.show', $item->activity) }}"
                                                    class="text-sm font-semibold text-purple-600 hover:underline">
                                                    View Details
                                                </a>
                                            @else
                                                <a href="{{ route('lesson.show', $item) }}"
                                                    class="text-sm font-semibold text-[#2454FF] hover:underline">
                                                    {{ $user->role === 'student' && ($item->enrollments->first()->status ?? '') === 'completed' ? 'Review Lesson' : 'Open Lesson' }}
                                                </a>
                                                @if($user->role === 'teacher')
                                                    <span class="text-gray-300">|</span>
                                                    <a href="{{ route('submission.index', ['lesson_id' => $item->id, 'classroom_id' => $classroom->id]) }}"
                                                        class="text-sm font-semibold text-[#5FAD56] hover:underline">
                                                        Grade Submissions
                                                    </a>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No content yet</h3>
                                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new lesson or assigning an activity.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>