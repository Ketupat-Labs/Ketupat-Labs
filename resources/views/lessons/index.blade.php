<x-app-layout>
    <div class="py-12 bg-gray-50">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 px-4 sm:px-0 gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-[#2454FF] tracking-tight">
                        {{ __('Lessons Inventory (Teacher View)') }}</h1>
                    <p class="text-gray-600 mt-2">{{ __('Create and manage your lesson content for students') }}</p>
                </div>
                <a href="{{ route('lessons.create') }}"
                    class="bg-[#5FAD56] hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition ease-in-out duration-150 shadow-md hover:shadow-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    {{ __('Create New Lesson') }}
                </a>
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

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 lesson-list-card">
                <table class="min-w-full divide-y divide-gray-200" id="lessonTable">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Title') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Topic') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Duration') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Status') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Material') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($lessons as $lesson)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $lesson->title }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $lesson->topic }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $lesson->duration ?? 'N/A' }} {{ __('mins') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($lesson->is_published)
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">{{ __('Published') }}</span>
                                    @else
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ __('Draft') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($lesson->material_path)
                                        <a href="{{ Storage::url($lesson->material_path) }}" target="_blank"
                                            class="text-[#F26430] hover:text-orange-700 font-medium">{{ __('Download File') }}</a>
                                    @else
                                        <span class="text-gray-400">{{ __('No file') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    {{-- Edit Button (UC003) --}}
                                    <a href="{{ route('lessons.edit', $lesson->id) }}"
                                        class="text-[#2454FF] hover:text-blue-900 font-medium mr-3">{{ __('Edit') }}</a>

                                    {{-- Delete Button (UC003) --}}
                                    <form action="{{ route('lessons.destroy', $lesson->id) }}" method="POST" class="inline"
                                        onsubmit="return confirm('{{ __('Are you sure you want to delete this lesson?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-[#E92222] hover:text-red-900 font-medium">{{ __('Delete') }}</button>
                                    </form>

                                    {{-- View Button (UC004) --}}
                                    <a href="{{ route('lessons.show', $lesson->id) }}"
                                        class="text-[#5FAD56] hover:text-green-700 font-medium ml-3">{{ __('View') }}</a>
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
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No lessons') }}</h3>
                                        <p class="mt-1 text-sm text-gray-500">
                                            {{ __('Get started by creating a new lesson.') }}</p>
                                        <div class="mt-6">
                                            <a href="{{ route('lessons.create') }}"
                                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[#5FAD56] hover:bg-green-700 focus:outline-none">
                                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 4v16m8-8H4"></path>
                                                </svg>
                                                {{ __('Create New Lesson') }}
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
</x-app-layout>