<x-app-layout>
<div class="py-12 bg-gray-50">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="text-center mb-6">
                <h1 class="text-3xl font-extrabold text-[#2454FF] mb-2">CompuPlay: Assignment Center</h1>
                <p class="text-gray-600">{{ $assignmentName }} (HCI 3.2)</p>
            </div>

            <div class="mb-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Submit Practical Work (UC007)</h2>
                
                <div class="current-status p-4 bg-orange-50 border border-[#F26430] rounded-lg mb-4">
                    <p class="font-bold text-[#F26430]">
                        Current Status: <strong>{{ $currentStatus }}</strong>
                        @if($submission && $submission->file_name)
                            <br><span class="text-sm text-gray-700">(Submitted File: {{ $submission->file_name }})</span>
                        @endif
                    </p>
                </div>

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="mb-6">
                    <p class="text-gray-700 mb-2"><strong>Instructions:</strong> Please upload your final design file or compressed code folder.</p>
                    <p class="text-gray-600">Required File Types: {{ $requiredFiles }}</p>
                </div>

                <form method="POST" action="{{ route('submission.submit') }}" enctype="multipart/form-data">
                    @csrf

                    @if (!$isSubmitted)
                        <div class="drop-zone border-2 border-dashed border-blue-500 p-8 text-center bg-blue-50 rounded-lg mb-4 cursor-pointer hover:bg-blue-100 transition-colors" 
                             onclick="document.getElementById('file-input').click()">
                            <p class="text-gray-700 mb-2">Drag & Drop Code File here, or click to browse.</p>
                            <input type="file" 
                                   id="file-input"
                                   name="submission_file" 
                                   required 
                                   accept=".html,.zip,.png,.jpg,.jpeg"
                                   class="hidden"
                                   onchange="document.getElementById('file-name').textContent = this.files[0]?.name || 'No file selected'">
                            <p id="file-name" class="text-sm text-gray-500 mt-2">No file selected</p>
                        </div>
                        
                        <button type="submit" class="w-full bg-[#5FAD56] hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition ease-in-out duration-150">
                            Submit Assignment
                        </button>
                    @else
                        <button type="button" class="w-full bg-gray-400 cursor-not-allowed text-white font-bold py-3 px-6 rounded-lg" disabled>
                            Submission Locked
                        </button>
                    @endif
                </form>
            </div>

            <div class="text-center mt-6">
                <a href="{{ route('lesson.index') }}" class="text-[#2454FF] hover:underline">Back to Lessons</a>
            </div>
        </div>
    </div>
</div>
</x-app-layout>

