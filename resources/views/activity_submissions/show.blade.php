<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Butiran Penyerahan Aktiviti') }}
                </h2>
                @if($submission->activity)
                    <p class="text-sm text-gray-500 mt-1">
                        Aktiviti: <span class="font-medium text-gray-700">{{ $submission->activity->title }}</span>
                    </p>
                @endif
            </div>
            @if($submission->activity_assignment_id)
                <a href="{{ route('activities.assignments.submissions', $submission->activity_assignment_id) }}"
                    class="text-gray-600 hover:text-gray-900">
                    ← Kembali
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Pelajar</h3>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $submission->user->full_name ?? $submission->user->username ?? 'Unknown' }}
                            </p>
                            <p class="text-sm text-gray-500">{{ $submission->user->email ?? '' }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Markah</h3>
                            <p class="mt-1 text-2xl font-bold text-gray-900">
                                @if($submission->score !== null)
                                    {{ $submission->score }}%
                                @else
                                    <span class="text-gray-400">Belum dinilai</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Status</h3>
                            <p class="mt-1">
                                @if($submission->completed_at)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Selesai
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Belum Selesai
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Selesai Pada</h3>
                            <p class="mt-1 text-sm text-gray-900">
                                @if($submission->completed_at)
                                    {{ $submission->completed_at->format('d M Y, H:i') }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($submission->feedback)
                        <div class="mt-6">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Maklum Balas</h3>
                            <p class="text-sm text-gray-900 bg-gray-50 p-4 rounded">{{ $submission->feedback }}</p>
                        </div>
                    @endif

                    @if($submission->results)
                        <div class="mt-6">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Keputusan Terperinci</h3>
                            <div class="bg-gray-50 p-4 rounded">
                                <pre class="text-sm text-gray-900 whitespace-pre-wrap">{{ json_encode($submission->results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

