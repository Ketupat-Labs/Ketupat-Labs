<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                {{ $classroom->name }}
            </h2>
            <span class="text-gray-500">{{ $classroom->subject }}</span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Students List -->
                <div class="md:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-bold text-gray-800">Students</h3>
                                @if($currentUser->role === 'teacher')
                                    <div class="flex space-x-2">
                                        <!-- Add Student Form -->
                                        <form method="POST" action="{{ route('classrooms.students.add', $classroom) }}"
                                            class="flex">
                                            @csrf
                                            <select name="student_id"
                                                class="text-sm border-gray-300 rounded-l-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                <option value="">Select Student</option>
                                                @foreach($availableStudents as $student)
                                                    <option value="{{ $student->id }}">{{ $student->full_name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit"
                                                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-r-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                Add
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Name</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Email</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Enrolled At</th>
                                            @if($currentUser->role === 'teacher')
                                                <th scope="col"
                                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Action</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($classroom->students as $student)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $student->full_name }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $student->email }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $student->pivot->enrolled_at }}
                                                </td>
                                                @if($currentUser->role === 'teacher')
                                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                        <form method="POST"
                                                            action="{{ route('classrooms.students.remove', [$classroom, $student->id]) }}"
                                                            onsubmit="return confirm('Are you sure?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="text-red-600 hover:text-red-900">Remove</button>
                                                        </form>
                                                    </td>
                                                @endif
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">No students
                                                    enrolled yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lessons List (Placeholder) -->
                <div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Lessons</h3>
                            <ul class="space-y-3">
                                @forelse($classroom->lessons as $lesson)
                                    <li class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <span class="text-sm font-medium text-gray-700">{{ $lesson->title }}</span>
                                        <a href="{{ route('lesson.show', $lesson) }}"
                                            class="text-blue-600 text-xs hover:underline">View</a>
                                    </li>
                                @empty
                                    <li class="text-gray-500 text-sm">No lessons assigned.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>