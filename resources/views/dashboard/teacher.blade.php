
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl leading-tight" style="color: #3E3E3E;">
                    Selamat kembali, {{ $user->full_name ?? 'Guru' }}!
                </h2>
                <p class="text-sm mt-1" style="color: #969696;">Urus kelas dan pelajaran anda</p>
            </div>
            <div class="flex space-x-4">
                <a href="{{ route('classrooms.create') }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors shadow-sm flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Cipta Kelas
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
                            <p class="text-sm font-medium text-compuplay-gray">Pelajaran Diterbitkan</p>
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
                            <p class="text-base font-medium text-compuplay-gray">Menunggu Penilaian</p>
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
                        <h4 class="text-xl font-bold mb-2">Semak Penyerahan</h4>
                        <p class="text-sm opacity-90">Nilai tugasan pelajar</p>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center text-sm" style="color: #969696;">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                            <span>{{ \App\Models\Submission::where('status', 'Submitted - Awaiting Grade')->count() }}
                                {{ __('pending') }}</span>
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
                        <h4 class="text-xl font-bold mb-2">Tugaskan Pelajaran</h4>
                        <p class="text-sm opacity-90">Tugaskan pelajaran kepada pelajar</p>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center text-sm" style="color: #969696;">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                            <span>Urus tugasan</span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- My Classrooms Section -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold" style="color: #3E3E3E;">Kelas Saya</h3>
                    <a href="{{ route('classrooms.create') }}" class="text-sm font-semibold text-[#2454FF] hover:underline">
                        + Cipta Kelas Baru
                    </a>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @forelse($classrooms as $classroom)
                        <a href="{{ route('classrooms.show', $classroom) }}" class="block bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all group overflow-hidden">
                            <div class="h-2 bg-gradient-to-r from-[#2454FF] to-[#1a3fcc]"></div>
                            <div class="p-5">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="text-lg font-bold text-gray-800 group-hover:text-[#2454FF] transition">{{ $classroom->name }}</h4>
                                    <span class="bg-blue-100 text-[#2454FF] text-xs font-bold px-2 py-1 rounded-full">
                                        {{ $classroom->students_count }} Pelajar
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 mb-4">{{ $classroom->subject }} &bull; {{ $classroom->year }}</p>
                                <div class="flex items-center text-sm font-medium text-[#5FAD56]">
                                    <span>Pergi ke Kelas</span>
                                    <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                    </svg>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-3 bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <p class="text-gray-500 mb-4">{{ __('You haven\'t created any classrooms yet.') }}</p>
                            <a href="{{ route('classrooms.create') }}" class="inline-block bg-[#2454FF] text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                                Cipta Kelas Pertama Anda
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Activity Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Lessons -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold" style="color: #3E3E3E;">Pelajaran Terkini Saya</h3>
                        <a href="{{ route('lessons.index') }}" class="text-sm hover:underline font-medium"
                            style="color: #2454FF;">Lihat semua</a>
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
                                        {{ $lesson->is_published ? 'Diterbitkan' : 'Draf' }}</p>
                                </div>
                                <svg class="w-5 h-5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    style="color: #969696;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                    </path>
                                </svg>
                            </a>
                        @empty
                            <div class="text-center py-8" style="color: #969696;">
                                <p>Tiada pelajaran dicipta lagi</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold mb-4" style="color: #3E3E3E;">Tindakan Pantas</h3>
                    <div class="space-y-3">
                        <a href="{{ route('lessons.create') }}"
                            class="flex items-center justify-between p-4 text-white rounded-lg hover:shadow-lg transition-all group"
                            style="background: linear-gradient(to right, #5FAD56, #4a8d45);">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span class="font-semibold">Cipta Pelajaran Baru</span>
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
                                <span class="font-semibold">Semak Penyerahan</span>
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
                                <span class="font-semibold">Tugaskan Pelajaran</span>
                            </div>
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </a>

                        <a href="{{ route('assignments.create', ['tab' => 'activity']) }}"
                            class="flex items-center justify-between p-4 text-white rounded-lg hover:shadow-lg transition-all group"
                            style="background: linear-gradient(to right, #F26430, #c44d26);">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="font-semibold">Tugaskan Aktiviti</span>
                            </div>
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </a>

                        <a href="{{ route('progress.index') }}"
                            class="flex items-center justify-between p-4 text-white rounded-lg hover:shadow-lg transition-all group"
                            style="background: linear-gradient(to right, #8B5CF6, #7C3AED);">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                    </path>
                                </svg>
                                <span class="font-semibold">Pantau Kemajuan</span>
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