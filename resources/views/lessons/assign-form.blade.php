// resources/views/lessons/assign-form.blade.php

@extends('layouts.app')

@section('content')
<div class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 card">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b-2 border-[#2454FF] pb-2">
                Tugaskan Pelajaran Mandatori
            </h2>

            @include('components.validation-errors') {{-- Assumes you have a component for errors --}}

            <form method="POST" action="{{ route('lessons.assign.store') }}" class="space-y-6">
                @csrf

                {{-- Select Class (M1 Integration) --}}
                <div>
                    <label for="class_id" class="block font-medium text-lg text-[#3E3E3E]">Kelas Sasaran <span class="text-red-600">*</span></label>
                    <select name="class_id" id="class_id" required class="mt-1 block w-full border border-gray-400 rounded-md shadow-sm p-3">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }} ({{ $class->subject }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                {{-- Select Lessons (M3 Integration) --}}
                <div>
                    <label class="block font-medium text-lg text-[#3E3E3E]">Pelajaran untuk Ditugaskan <span class="text-red-600">*</span></label>
                    <div class="space-y-2 border border-gray-300 p-4 rounded-lg h-64 overflow-y-auto bg-gray-50">
                        @forelse ($lessons as $lesson)
                            <div class="flex items-center">
                                <input type="checkbox" name="lesson_ids[]" id="lesson_{{ $lesson->id }}" value="{{ $lesson->id }}" class="rounded text-[#2454FF] shadow-sm focus:border-[#2454FF] focus:ring focus:ring-[#2454FF]/50">
                                <label for="lesson_{{ $lesson->id }}" class="ml-2 text-sm text-gray-700">
                                    {{ $lesson->title }} <span class="text-xs text-gray-500">({{ $lesson->topic }} - {{ $lesson->duration }} mins)</span>
                                </label>
                            </div>
                        @empty
                            <p class="text-gray-500">Tiada pelajaran tersedia untuk ditugaskan. Sila cipta pelajaran terlebih dahulu.</p>
                        @endforelse
                    </div>
                </div>

                <div class="flex items-center justify-start pt-4">
                    <button type="submit" class="bg-[#5FAD56] hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition ease-in-out duration-150">
                        Tugaskan Pelajaran
                    </button>
                    <a href="{{ route('lessons.index') }}" class="ml-4 text-gray-600 hover:text-gray-900 font-medium">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection