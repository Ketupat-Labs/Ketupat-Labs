
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl leading-tight" style="color: #3E3E3E;">
                    Welcome back, {{ $user->full_name ?? 'Teacher' }}!
                </h2>
                <p class="text-sm mt-1" style="color: #969696;">Manage your classes and lessons</p>
            </div>
            <div class="flex space-x-4">
                <a href="{{ route('classrooms.create') }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors shadow-sm flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create Class
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-gray-50 to-white min-h-screen">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            <!-- Quick Stats Section -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-compuplay-gray">Published Lessons</p>
                            <p class="text-3xl font-bold mt-2" style="color: #2454FF;">
                                {{ \App\Models\Lesson::where('is_published', true)->where('teacher_id', $user->id)->count() }}
                            </p>
                        </div>
                        <div class="p-3 rounded-lg" style="background-color: rgba(36, 84, 255, 0.1);">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                style="color: #2454FF;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-compuplay-gray">Pending Grading</p>
                            <p class="text-3xl font-bold mt-2" style="color: #F26430;">
                                {{ \App\Models\Submission::where('status', 'Submitted - Awaiting Grade')->count() }}</p>
                        </div>
                        <div class="p-3 rounded-lg" style="background-color: rgba(242, 100, 48, 0.1);">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                style="color: #F26430;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- View Submissions Card -->
                <a href="{{ route('submission.index') }}"
                    class="group bg-white rounded-xl shadow-sm border-2 border-transparent hover:shadow-lg transition-all duration-300 overflow-hidden"
                    onmouseover="this.style.borderColor='#F26430'"
                    onmouseout="this.style.borderColor='transparent'">
                    <div class="p-6 text-white"
                        style="background: linear-gradient(to bottom right, #F26430, #c44d26);">
                        <div class="flex items-center justify-between mb-4">
                            <svg class="w-12 h-12 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                </path>
                            </svg>
                            <svg class="w-6 h-6 opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                        <h4 class="text-xl font-bold mb-2">Review Submissions</h4>
                        <p class="text-sm opacity-90">Grade student assignments</p>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center text-sm" style="color: #969696;">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                            <span>{{ \App\Models\Submission::where('status', 'Submitted - Awaiting Grade')->count() }}
                                pending</span>
                        </div>
                    </div>
                </a>

                <!-- Assign Lessons Card -->
                <a href="{{ route('assignments.create') }}"
                    class="group bg-white rounded-xl shadow-sm border-2 border-transparent hover:shadow-lg transition-all duration-300 overflow-hidden"
                    onmouseover="this.style.borderColor='#2454FF'"
                    onmouseout="this.style.borderColor='transparent'">
                    <div class="p-6 text-white"
                        style="background: linear-gradient(to bottom right, #2454FF, #1a3fcc);">
                        <div class="flex items-center justify-between mb-4">
                            <svg class="w-12 h-12 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                            <svg class="w-6 h-6 opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                        <h4 class="text-xl font-bold mb-2">Assign Lessons</h4>
                        <p class="text-sm opacity-90">Assign lessons to students</p>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center text-sm" style="color: #969696;">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                            <span>Manage assignments</span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Recent Activity Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Lessons -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold" style="color: #3E3E3E;">My Recent Lessons</h3>
                        <a href="{{ route('lessons.index') }}" class="text-sm hover:underline font-medium"
                            style="color: #2454FF;">View all</a>
                    </div>
                    <div class="space-y-4">
                        @php
                            $recentLessons = \App\Models\Lesson::where('teacher_id', $user->id)->latest()->take(3)->get();
                        @endphp
                        @forelse($recentLessons as $lesson)
                            <a href="{{ route('lessons.show', $lesson->id) }}"
                                class="flex items-center p-4 bg-gray-50 rounded-lg transition-colors group"
                                style="background-color: #f9fafb;"
                                onmouseover="this.style.backgroundColor='rgba(36, 84, 255, 0.05)'"
                                onmouseout="this.style.backgroundColor='#f9fafb'">
                                <div class="flex-shrink-0 p-2 rounded-lg mr-4"
                                    style="background-color: rgba(36, 84, 255, 0.1);">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        style="color: #2454FF;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                        </path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold transition-colors" style="color: #3E3E3E;">
                                        {{ $lesson->title }}</p>
                                    <p class="text-xs mt-1" style="color: #969696;">{{ $lesson->topic }} •
                                        {{ $lesson->is_published ? 'Published' : 'Draft' }}</p>
                                </div>
                                <svg class="w-5 h-5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    style="color: #969696;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                    </path>
                                </svg>
                            </a>
                        @empty
                            <div class="text-center py-8" style="color: #969696;">
                                <p>No lessons created yet</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold mb-4" style="color: #3E3E3E;">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="{{ route('lessons.create') }}"
                            class="flex items-center justify-between p-4 text-white rounded-lg hover:shadow-lg transition-all group"
                            style="background: linear-gradient(to right, #5FAD56, #4a8d45);">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span class="font-semibold">Create New Lesson</span>
                            </div>
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </a>

                        <a href="{{ route('submission.index') }}"
                            class="flex items-center justify-between p-4 text-white rounded-lg hover:shadow-lg transition-all group"
                            style="background: linear-gradient(to right, #F26430, #c44d26);">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                    </path>
                                </svg>
                                <span class="font-semibold">Review Submissions</span>
                            </div>
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </a>

                        <a href="{{ route('assignments.create') }}"
                            class="flex items-center justify-between p-4 text-white rounded-lg hover:shadow-lg transition-all group"
                            style="background: linear-gradient(to right, #2454FF, #1a3fcc);">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span class="font-semibold">Assign Lessons</span>
                            </div>
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>