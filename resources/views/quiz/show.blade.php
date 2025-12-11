<x-app-layout>
<div class="py-12 bg-gray-50">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="text-center mb-6">
                <h1 class="text-3xl font-extrabold text-[#2454FF] mb-2">Interactive Quiz: Interaction Design</h1>
                <p class="text-gray-600">Current Total Points: <strong class="text-[#F26430]">{{ $points }} XP</strong></p>
            </div>

            @if(session('quiz_result'))
                @php $result = session('quiz_result'); @endphp
                <div class="quiz-feedback p-6 border-2 border-[#5FAD56] bg-green-50 rounded-lg mb-6 text-center">
                    <h3 class="text-2xl font-bold {{ $result['percentage'] >= 50 ? 'text-[#5FAD56]' : 'text-[#E92222]' }} mb-3">
                        Quiz Complete!
                    </h3>
                    <p class="text-lg mb-2">You answered <strong>{{ $result['score'] }} out of {{ $result['total'] }}</strong> questions correctly ({{ round($result['percentage']) }}%).</p>
                    <p class="text-xl font-bold text-[#F26430] mb-2">🎉 Points Earned: +{{ $result['points_awarded'] }} XP</p>
                    <p class="text-gray-700">Your Total Score: <strong>{{ $result['total_points'] }} Points</strong></p>
                </div>
            @endif

            @if($hasSubmitted && !session('quiz_result'))
                <div class="quiz-feedback p-6 border-2 border-[#5FAD56] bg-green-50 rounded-lg mb-6 text-center">
                    <h3 class="text-xl font-bold text-[#5FAD56] mb-2">You have already completed this quiz!</h3>
                    <p class="text-gray-700">Score: {{ $lastAttempt->score }}/{{ $lastAttempt->total_questions }}</p>
                    <p class="text-gray-700">Points Earned: {{ $lastAttempt->points_earned }} XP</p>
                </div>
            @elseif(!$hasSubmitted || session('quiz_result'))
                <form method="POST" action="{{ route('quiz.submit') }}">
                    @csrf
                    <input type="hidden" name="lesson_id" value="{{ $lesson }}">
                    
                    @foreach ($quizData as $index => $q)
                        <div class="question border border-gray-300 rounded-lg p-4 mb-4">
                            <h4 class="text-lg font-semibold text-[#2454FF] mb-3">Question {{ $index + 1 }}:</h4>
                            <p class="text-gray-700 mb-3">{{ $q['question'] }}</p>
                            
                            <div class="space-y-2">
                                @foreach ($q['options'] as $option)
                                    <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                        <input type="radio" name="q{{ $index }}" value="{{ $option }}" required class="mr-3">
                                        <span>{{ $option }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <button type="submit" name="submit_quiz" 
                            class="w-full bg-[#5FAD56] hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition ease-in-out duration-150">
                        Submit Quiz (Reinforce Learning)
                    </button>
                </form>
            @endif

            <div class="mt-6 text-center">
                <a href="{{ route('lesson.index') }}" class="text-[#2454FF] hover:underline">Back to Lessons</a>
            </div>
        </div>
    </div>
</div>
</x-app-layout>

