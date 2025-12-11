<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Assignments') }}
                </h2>
            </div>
            @if($user->role === 'teacher')
            <a href="{{ route('assignments.create') }}"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors shadow-sm flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                {{ __('Create Assignment') }}
            </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($assignments->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 text-center">
                        <p class="text-gray-500">{{ __('No assignments found.') }}</p>
                        @if($user->role === 'teacher')
                            <a href="{{ route('assignments.create') }}" class="text-blue-600 hover:text-blue-800 mt-4 inline-block">
                                {{ __('Create your first assignment') }}
                            </a>
                        @endif
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($assignments as $assignment)
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                {{ $assignment->lesson->title ?? 'N/A' }}
                                            </h3>
                                            <p class="text-sm text-gray-600 mt-1">
                                                <span class="font-medium">Class:</span> {{ $assignment->classroom->name ?? 'N/A' }}
                                                @if($assignment->classroom->subject)
                                                    <span class="text-gray-400">•</span> {{ $assignment->classroom->subject }}
                                                @endif
                                            </p>
                                            @if($assignment->type)
                                                <span class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded
                                                    {{ $assignment->type === 'Mandatory' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                                                    {{ $assignment->type }}
                                                </span>
                                            @endif
                                            @if($assignment->assigned_at)
                                                <p class="text-xs text-gray-500 mt-2">
                                                    <span class="font-medium">Assigned:</span> {{ $assignment->assigned_at->format('M d, Y H:i') }}
                                                </p>
                                            @endif
                                            @if($assignment->due_date)
                                                <p class="text-xs text-gray-500 mt-1">
                                                    <span class="font-medium">Due:</span> {{ \Carbon\Carbon::parse($assignment->due_date)->format('M d, Y') }}
                                                </p>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <a href="{{ route('assignments.show', $assignment->id) }}"
                                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                View →
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

