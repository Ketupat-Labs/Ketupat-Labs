<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Inventori Pelajaran & Aktiviti') }}
            </h2>
            <div x-data="{ activeTab: '{{ request('tab', 'lessons') }}' }">
                <a :href="activeTab === 'lessons' ? '{{ route('lessons.create') }}' : '{{ route('activities.create') }}'"
                    class="bg-[#5FAD56] hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition ease-in-out duration-150 shadow-sm flex items-center gap-2 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span x-text="activeTab === 'lessons' ? 'Cipta Pelajaran Baru' : 'Cipta Aktiviti Baru'"></span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50" x-data="{ activeTab: '{{ request('tab', 'lessons') }}' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Tab Navigation -->
            <div class="flex space-x-4 mb-6 border-b border-gray-200 px-4 sm:px-0">
                <button @click="activeTab = 'lessons'" 
                    :class="{ 'border-b-2 border-blue-500 text-blue-600': activeTab === 'lessons', 'text-gray-500 hover:text-gray-700': activeTab !== 'lessons' }"
                    class="pb-2 px-4 text-sm font-medium transition-colors duration-200 focus:outline-none">
                    Inventori Pelajaran
                </button>
                <button @click="activeTab = 'activities'" 
                    :class="{ 'border-b-2 border-blue-500 text-blue-600': activeTab === 'activities', 'text-gray-500 hover:text-gray-700': activeTab !== 'activities' }"
                    class="pb-2 px-4 text-sm font-medium transition-colors duration-200 focus:outline-none">
                    Inventori Aktiviti
                </button>
            </div>

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- LESSONS TAB -->
            <div x-show="activeTab === 'lessons'">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 lesson-list-card">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="lessonTable">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tajuk</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Topik</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tempoh</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Bahan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($lessons as $lesson)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $lesson->title }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $lesson->topic }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $lesson->duration ?? 'N/A' }} minit</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($lesson->is_published)
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Diterbitkan</span>
                                    @else
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Draf</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($lesson->material_path)
                                        <a href="{{ Storage::url($lesson->material_path) }}" target="_blank"
                                            class="text-[#F26430] hover:text-orange-700 font-medium">Muat Turun Fail</a>
                                    @else
                                        <span class="text-gray-400">Tiada fail</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        {{-- View Button (UC004) --}}
                                        <a href="{{ route('lessons.show', $lesson->id) }}"
                                            class="inline-flex items-center px-3 py-1.5 bg-[#5FAD56] hover:bg-green-700 text-white text-xs font-semibold rounded-md shadow-sm transition duration-150 ease-in-out">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            Lihat
                                        </a>

                                        {{-- Assign Button (NEW) --}}
                                        <a href="{{ route('assignments.create', ['lesson_id' => $lesson->id]) }}"
                                            class="inline-flex items-center px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-md shadow-sm transition duration-150 ease-in-out">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            Tugaskan
                                        </a>

                                        {{-- Edit Button (UC003) --}}
                                        <a href="{{ route('lessons.edit', $lesson->id) }}"
                                            class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-md shadow-sm transition duration-150 ease-in-out">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            Sunting
                                        </a>

                                        {{-- Delete Button (UC003) --}}
                                        <form action="{{ route('lessons.destroy', $lesson->id) }}" method="POST" class="inline"
                                            onsubmit="return confirm('{{ __('Are you sure you want to delete this lesson?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 bg-white border border-red-300 text-red-600 hover:bg-red-50 text-xs font-semibold rounded-md shadow-sm transition duration-150 ease-in-out">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                Padam
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center">
                                    <div class="py-8">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                            </path>
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">Tiada pelajaran</h3>
                                        <p class="mt-1 text-sm text-gray-500">
                                            Mulakan dengan mencipta pelajaran baru.</p>
                                        <div class="mt-6">
                                            <a href="{{ route('lessons.create') }}"
                                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[#5FAD56] hover:bg-green-700 focus:outline-none">
                                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 4v16m8-8H4"></path>
                                                </svg>
                                                Cipta Pelajaran Baru
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    </table>
                </div>
            </div>
            </div>

            <!-- ACTIVITIES TAB -->
            <div x-show="activeTab === 'activities'" style="display: none;">
                @php
                    $allActivities = collect($games ?? [])->merge($quizzes ?? [])->sortByDesc('created_at');
                @endphp

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tajuk</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tempoh</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($allActivities as $activity)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $activity->title }}
                                                @if($activity->is_public)
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                        Awam
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $activity->type === 'Quiz' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ $activity->type }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $activity->duration ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                <!-- View Button -->
                                                <a href="{{ route('activities.show', $activity->id) }}" 
                                                    class="inline-flex items-center px-3 py-1.5 bg-[#5FAD56] hover:bg-green-700 text-white text-xs font-semibold rounded-md shadow-sm transition duration-150 ease-in-out">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                    Lihat
                                                </a>

                                                <!-- Assign Button -->
                                                <a href="{{ route('assignments.create', ['activity_id' => $activity->id, 'tab' => 'activity']) }}" 
                                                    class="inline-flex items-center px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-md shadow-sm transition duration-150 ease-in-out">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    Tugaskan
                                                </a>

                                                <!-- Edit Button -->
                                                <a href="{{ route('activities.edit', $activity->id) }}" 
                                                    class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-md shadow-sm transition duration-150 ease-in-out">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                    Sunting
                                                </a>

                                                <!-- Delete Button -->
                                                <form action="{{ route('activities.destroy', $activity->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Adakah anda pasti mahu memadam aktiviti ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                        class="inline-flex items-center px-3 py-1.5 bg-white border border-red-300 text-red-600 hover:bg-red-50 text-xs font-semibold rounded-md shadow-sm transition duration-150 ease-in-out">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                        Padam
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
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
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>