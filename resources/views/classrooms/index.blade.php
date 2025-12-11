<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                {{ __('My Classrooms') }}
            </h2>
            @if($currentUser->role === 'teacher')
                <a href="{{ route('classrooms.create') }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    Create Classroom
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($classrooms as $classroom)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $classroom->name }}</h3>
                            <p class="text-gray-600 mb-4">{{ $classroom->subject }}</p>
                            @if($classroom->year)
                                <span class="inline-block bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded mb-4">
                                    Year: {{ $classroom->year }}
                                </span>
                            @endif

                            <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-100">
                                <span class="text-sm text-gray-500">
                                    {{ $classroom->students_count ?? $classroom->students()->count() }} Students
                                </span>
                                <div class="flex space-x-3">
                                    <a href="{{ route('classrooms.show', $classroom) }}"
                                        class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                        View
                                    </a>
                                    @if($currentUser->role === 'teacher')
                                        <form method="POST" action="{{ route('classrooms.destroy', $classroom) }}"
                                            onsubmit="return confirm('Are you sure you want to delete this classroom?');"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 font-medium text-sm">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-1 md:col-span-2 lg:col-span-3">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                            <p class="text-gray-500">No classrooms found.</p>
                            @if($currentUser->role === 'teacher')
                                <a href="{{ route('classrooms.create') }}"
                                    class="text-blue-600 hover:underline mt-2 inline-block">
                                    Create your first classroom
                                </a>
                            @endif
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>