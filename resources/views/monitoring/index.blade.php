<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl leading-tight" style="color: #3E3E3E;">
                    {{ __('Student Progress Monitor') }}
                </h2>
                <p class="text-sm mt-1" style="color: #969696;">Track student engagement and progress.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-gray-800">Lesson Engagement Dashboard</h3>

                        <!-- Filter (Placeholder) -->
                        <div class="flex items-center">
                            <label class="mr-2 text-sm text-gray-600">Filter by Class:</label>
                            <select id="classFilter"
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                                <option value="">All Classes</option>
                                @foreach($classrooms as $classroom)
                                    <option value="{{ $classroom->name }}">{{ $classroom->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Student Name</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Class</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Lesson</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Progress</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Last Active</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($studentProgress as $record)
                                                            <tr>
                                                                <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900">
                                                                    {{ $record['student_name'] }}</td>
                                                                <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $record['class'] }}</td>
                                                                <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $record['lesson_title'] }}
                                                                </td>
                                                                <td class="px-6 py-4 whitespace-nowrap">
                                                                    <div class="flex items-center">
                                                                        <div class="w-24 bg-gray-200 rounded-full h-2.5 mr-2">
                                                                            <div class="bg-blue-600 h-2.5 rounded-full"
                                                                                style="width: {{ $record['progress'] }}%"></div>
                                                                        </div>
                                                                        <span class="text-sm text-gray-500">{{ $record['progress'] }}%</span>
                                                                    </div>
                                                                </td>
                                                                <td class="px-6 py-4 whitespace-nowrap">
                                                                    <span
                                                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                                            {{ $record['status'] === 'Completed' ? 'bg-green-100 text-green-800' :
                                    ($record['status'] === 'In_progress' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                                                        {{ $record['status'] }}
                                                                    </span>
                                                                </td>
                                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                    {{ $record['last_accessed'] }}</td>
                                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                                    @if($record['progress'] < 20)
                                                                        <button onclick="alert('Sending encouragement...')"
                                                                            class="text-yellow-600 hover:text-yellow-900 bg-yellow-100 px-3 py-1 rounded-md">Provide
                                                                            Guidance</button>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No
                                            student progress records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('classFilter').addEventListener('change', function() {
            const selectedClass = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                if (selectedClass === "") {
                    row.style.display = "";
                    return;
                }
                
                const classCell = row.querySelector('td:nth-child(2)');
                if (classCell) {
                    const rowClass = classCell.textContent.trim().toLowerCase();
                    if (rowClass === selectedClass) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                }
            });
        });
    </script>
</x-app-layout>