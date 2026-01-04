<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Senarai Aktiviti') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Aktiviti Saya</h3>
                        <a href="{{ route('activities.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow transition ease-in-out duration-150">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Tambah Aktiviti Baru
                        </a>
                    </div>

                    @if($activities->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tajuk</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cadangan Masa</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tindakan</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($activities as $activity)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $activity->title }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                                    {{ $activity->type }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $activity->suggested_duration }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex items-center gap-2">
                                                    {{-- Assign Button (Text Only) --}}
                                                    {{-- Assign Button (Modernized) --}}
                                                    <a href="{{ route('assignments.create', ['activity_id' => $activity->id, 'tab' => 'activity']) }}" 
                                                        class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-md shadow-sm transition duration-150 ease-in-out">
                                                        Tugaskan
                                                    </a>

                                                    {{-- Edit Button (Icon) --}}
                                                    <a href="{{ route('activities.edit', $activity->id) }}" class="text-blue-600 hover:text-blue-900 p-1.5 hover:bg-blue-50 rounded-full transition" title="Edit Aktiviti">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                    </a>

                                                    {{-- Delete Button (Icon) --}}
                                                    <form action="{{ route('activities.destroy', $activity->id) }}" method="POST" class="inline-flex" onsubmit="return confirm('Adakah anda pasti?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-500 hover:text-red-700 p-1.5 hover:bg-red-50 rounded-full transition" title="Padam Aktiviti">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-12">
                            <span class="block mb-2">🔭</span>
                            Tiada aktiviti ditemui. Sila tambah aktiviti baru.
                        </p>
                    @endif
                </div>
            </div>

            <!-- Assigned Activities Section -->
             @if(isset($assignments) && $assignments->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-6">Aktiviti Ditugaskan & Markah</h3>

                    @foreach($assignments as $assignment)
                        <div class="mb-6 border rounded-lg p-4 bg-gray-50">
                            <div class="flex justify-between items-center mb-4">
                                <div>
                                    <h4 class="text-md font-bold text-gray-800">{{ $assignment->activity->title }}</h4>
                                    <p class="text-sm text-gray-600">Kelas: {{ $assignment->classroom->name }} | Tarikh Akhir: {{ $assignment->due_date ? $assignment->due_date->format('d M Y') : 'Tiada' }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800 uppercase">{{ $assignment->activity->type }}</span>
                            </div>

                            <div class="overflow-x-auto bg-white rounded shadow-sm border">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Pelajar</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Markah</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Maklum Balas</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tindakan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach($assignment->classroom->students as $student)
                                            @php
                                                $submission = $assignment->submissions->where('user_id', $student->id)->first();
                                            @endphp
                                            <tr>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $student->full_name }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                    @if($submission)
                                                        <span class="inline-flex px-2 py-1 text-xs font-semibold leading-5 text-green-800 bg-green-100 rounded-full">Dinilai</span>
                                                    @else
                                                        <span class="inline-flex px-2 py-1 text-xs font-semibold leading-5 text-gray-800 bg-gray-100 rounded-full">Belum</span>
                                                    @endif
                                                </td>
                                                <form action="{{ route('activities.grade', $assignment->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ $student->id }}">
                                                    <td class="px-4 py-3 whitespace-nowrap">
                                                        <input type="number" name="score" value="{{ $submission ? $submission->score : '' }}" class="w-20 text-sm border-gray-300 rounded shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="0-100" required>
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap">
                                                        <input type="text" name="feedback" value="{{ $submission ? $submission->feedback : '' }}" class="w-full text-sm border-gray-300 rounded shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Komen...">
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap">
                                                        <button type="submit" class="inline-flex items-center px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-md shadow-sm transition duration-150 ease-in-out">
                                                            Simpan
                                                        </button>
                                                    </td>
                                                </form>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
