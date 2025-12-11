<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Jejak Perkembangan Pelajaran</h2>
                <p class="text-gray-600">Pantau status penyelesaian pelajar merentasi semua pelajaran</p>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="GET" action="{{ route('progress.index') }}" class="flex items-center gap-4">
                        <label for="class_id" class="text-sm font-medium text-gray-700">Kelas</label>
                        <select name="class_id" id="class_id" onchange="this.form.submit()"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base text-gray-900 bg-white border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm">
                            <option value="" disabled {{ !$selectedClass ? 'selected' : '' }}
                                style="color: #6b7280; background-color: #ffffff;">Sila Pilih Kelas</option>
                            @foreach($classrooms as $classroom)
                                <option value="{{ $classroom->id }}" class="text-gray-900 bg-white"
                                    style="color: #000000 !important; background-color: #ffffff !important;" {{ ($selectedClass && $selectedClass->id == $classroom->id) ? 'selected' : '' }}>
                                    {{ $classroom->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>

            @if($selectedClass)
                <!-- Progress Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <!-- Header Blue Bar -->
                    <div class="bg-blue-600 px-6 py-4 border-b border-blue-600">
                        <h3 class="text-lg font-medium text-white flex items-center gap-2">
                            📊 Jadual Perkembangan Pelajaran - {{ $selectedClass->name }}
                        </h3>
                    </div>

                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            NAMA PELAJAR</th>
                                        @foreach($lessons as $lesson)
                                            <th scope="col"
                                                class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                                {{ strtoupper($lesson->title ?? 'PELAJARAN ' . $loop->iteration) }}
                                            </th>
                                        @endforeach
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            PERATUS PENYELESAIAN</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($progressData as $progress)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div
                                                        class="text-sm font-medium text-gray-900 text-blue-600 hover:underline">
                                                        👤 {{ $progress['student']->full_name ?? $progress['student']->name }}
                                                    </div>
                                                </div>
                                            </td>
                                            @foreach($progress['lessons'] as $lessonProgress)
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    @php
                                                        $statusClass = 'bg-gray-100 text-gray-800';
                                                        $statusText = 'BELUM MULA';

                                                        if ($lessonProgress['status'] === 'Completed') {
                                                            $statusClass = 'bg-green-100 text-green-800';
                                                            $statusText = 'SELESAI';
                                                        } elseif ($lessonProgress['status'] === 'Completed (Low Score)') {
                                                            $statusClass = 'bg-red-100 text-red-800';
                                                            $statusText = 'MARKAH RENDAH';
                                                        } elseif ($lessonProgress['status'] === 'In Progress') {
                                                            $statusClass = 'bg-yellow-100 text-yellow-800';
                                                            $statusText = 'SEDANG BERJALAN';
                                                        }
                                                    @endphp
                                                    <span
                                                        class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full {{ $statusClass }}">
                                                        {{ $statusText }}
                                                    </span>
                                                </td>
                                            @endforeach
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-24 bg-gray-200 rounded-full h-2.5">
                                                        <div class="bg-blue-600 h-2.5 rounded-full"
                                                            style="width: {{ $progress['completionPercentage'] }}%"></div>
                                                    </div>
                                                    <span
                                                        class="text-sm font-bold text-gray-700">{{ $progress['completionPercentage'] }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ count($lessons) + 2 }}"
                                                class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                                Tiada pelajar dijumpai dalam kelas ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Ringkasan Perkembangan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="text-xs font-bold text-gray-500 uppercase">JUMLAH PELAJAR</div>
                            <div class="mt-1 text-3xl font-bold text-blue-600">{{ $summary['totalStudents'] }}</div>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="text-xs font-bold text-gray-500 uppercase">JUMLAH PELAJARAN</div>
                            <div class="mt-1 text-3xl font-bold text-blue-600">{{ $summary['totalLessons'] }}</div>
                        </div>
                        @foreach($summary['lessonCompletion'] as $lessonCompletion)
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="text-xs font-bold text-gray-500 uppercase truncate"
                                    title="{{ $lessonCompletion['lesson']->title }}">
                                    {{ $lessonCompletion['lesson']->title ?? 'PELAJARAN ' . $lessonCompletion['lesson']->id }}
                                </div>
                                <div class="mt-1 flex items-end gap-2">
                                    <span
                                        class="text-3xl font-bold text-blue-600">{{ $lessonCompletion['completed'] }}/{{ $lessonCompletion['total'] }}</span>
                                    <span class="text-xs text-gray-500 mb-1">{{ $lessonCompletion['percentage'] }}%
                                        selesai</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center text-gray-500">
                    Sila pilih kelas untuk melihat perkembangan.
                </div>
            @endif
        </div>
    </div>
</x-app-layout>