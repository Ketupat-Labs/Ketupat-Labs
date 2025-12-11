<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Jadual Prestasi Pelajar</h2>
                <p class="text-gray-600">Pantau dan analisis prestasi pelajar merentasi pelajaran</p>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="GET" action="{{ route('performance.index') }}"
                        class="flex flex-wrap items-center gap-6">

                        <!-- Class Filter -->
                        <div class="flex flex-col gap-1">
                            <label for="class_id" class="text-sm font-medium text-gray-700">Kelas</label>
                            <select name="class_id" id="class_id" onchange="this.form.submit()"
                                class="block pl-3 pr-10 py-2 text-base text-gray-900 bg-white border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md min-w-[200px] shadow-sm">
                                <option value="" disabled {{ !$selectedClass ? 'selected' : '' }}>Sila Pilih Kelas
                                </option>
                                @foreach($classrooms as $classroom)
                                    <option value="{{ $classroom->id }}" class="text-gray-900 bg-white" {{ ($selectedClass && $selectedClass->id == $classroom->id) ? 'selected' : '' }}>
                                        {{ $classroom->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Lesson Filter -->
                        <div class="flex flex-col gap-1">
                            <label for="lesson_id" class="text-sm font-medium text-gray-700">Pelajaran</label>
                            <select name="lesson_id" id="lesson_id" onchange="this.form.submit()"
                                class="block pl-3 pr-10 py-2 text-base text-gray-900 bg-white border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md min-w-[200px]">
                                <option value="all" class="text-gray-900 bg-white" {{ $selectedLessonId === 'all' ? 'selected' : '' }}>Semua Pelajaran</option>
                                @foreach($lessons as $lesson)
                                    <option value="{{ $lesson->id }}" class="text-gray-900 bg-white" {{ $selectedLessonId == $lesson->id ? 'selected' : '' }}>
                                        {{ $lesson->title ?? 'Pelajaran ' . $loop->iteration }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </form>
                </div>
            </div>

            @if($selectedClass)

                <!-- Header Banner -->
                <div class="bg-blue-600 rounded-t-lg px-6 py-4">
                    <h3 class="text-lg font-medium text-white flex items-center gap-2">
                        @if($mode === 'all')
                            📊 Melihat: {{ $selectedClass->name }} | Semua Pelajaran
                        @else
                            📊 Melihat: {{ $selectedClass->name }} |
                            {{ $lessons->find($selectedLessonId)->title ?? 'Pelajaran Terpilih' }}
                        @endif
                    </h3>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-b-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            NAMA PELAJAR</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            KELAS</th>

                                        @if($mode === 'all')
                                            @foreach($lessons as $lesson)
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                                    {{ strtoupper($lesson->title ?? 'P ' . $loop->iteration) }}
                                                </th>
                                            @endforeach
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                                PURATA KESELURUHAN</th>
                                        @else
                                            <th scope="col"
                                                class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                                S1</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                                S2</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                                S3</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                                GRED GURU</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                                JUMLAH MARKAH (SISTEM)</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($data as $row)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div
                                                        class="text-sm font-medium text-gray-900 text-blue-600 hover:underline">
                                                        👤 {{ $row['student']->full_name ?? $row['student']->name }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $selectedClass->name }}
                                            </td>

                                            @if($mode === 'all')
                                                @foreach($lessons as $lesson)
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        @php
                                                            $grade = $row['grades'][$lesson->id];
                                                        @endphp
                                                        @if($grade['display'] !== '-')
                                                            <span
                                                                class="{{ $grade['score'] == 3 ? 'text-green-600' : ($grade['score'] < 2 ? 'text-red-500' : 'text-gray-900') }}">
                                                                {{ $grade['display'] }}
                                                            </span>
                                                        @else
                                                            <span class="text-gray-400">-</span>
                                                        @endif
                                                    </td>
                                                @endforeach
                                                <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900">
                                                    {{ $row['average'] }}
                                                </td>
                                            @else
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    @if($row['s1'] === '✓') <span class="text-green-500 font-bold">✓</span>
                                                    @elseif($row['s1'] === '✗') <span class="text-red-500 font-bold">✗</span>
                                                    @else <span class="text-gray-300">-</span> @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    @if($row['s2'] === '✓') <span class="text-green-500 font-bold">✓</span>
                                                    @elseif($row['s2'] === '✗') <span class="text-red-500 font-bold">✗</span>
                                                    @else <span class="text-gray-300">-</span> @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    @if($row['s3'] === '✓') <span class="text-green-500 font-bold">✓</span>
                                                    @elseif($row['s3'] === '✗') <span class="text-red-500 font-bold">✗</span>
                                                    @else <span class="text-gray-300">-</span> @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center font-bold text-lg">
                                                    @if($row['teacher_grade'] !== '-')
                                                        <div>
                                                            <span class="text-indigo-600 block">{{ $row['teacher_grade'] }}%</span>
                                                            <span class="text-xs text-gray-400 font-normal">Gred Guru</span>
                                                        </div>
                                                    @else
                                                        <span class="text-gray-300">-</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span
                                                        class="{{ $row['total_marks'] == 3 ? 'text-green-600' : ($row['total_marks'] < 2 ? 'text-red-500' : 'text-blue-600') }} font-bold text-lg">
                                                        {{ $row['total_marks'] }}
                                                    </span>
                                                    <span class="text-xs text-gray-400 font-normal">/3</span>
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="100%" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                                Tiada data dijumpai.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>