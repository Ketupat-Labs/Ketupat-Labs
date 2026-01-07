<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Penjana AI') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="text-center mb-8">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ __('Penjana Kandungan AI') }}</h1>
                        <p class="text-gray-600">{{ __('Jana slaid pendidikan dan kuiz menggunakan AI') }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Generate Slides Card -->
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow">
                            <div class="flex items-center mb-4">
                                <div class="bg-blue-500 rounded-lg p-3 mr-4">
                                    <i class="fas fa-chalkboard-user text-white text-2xl"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900">{{ __('Jana Slaid') }}</h3>
                            </div>
                            <p class="text-gray-600 mb-4">{{ __('Cipta slaid persembahan pendidikan mengenai sebarang topik menggunakan AI.') }}</p>
                            <a href="{{ route('ai-generator.slides') }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-magic mr-2"></i>
                                {{ __('Jana Slaid') }}
                            </a>
                        </div>

                        <!-- Generate Quiz Card -->
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow">
                            <div class="flex items-center mb-4">
                                <div class="bg-green-500 rounded-lg p-3 mr-4">
                                    <i class="fas fa-question-circle text-white text-2xl"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900">{{ __('Jana Kuiz') }}</h3>
                            </div>
                            <p class="text-gray-600 mb-4">{{ __('Cipta soalan kuiz dengan pilihan jawapan objektif atau benar/palsu.') }}</p>
                            <a href="{{ route('ai-generator.quiz') }}" 
                               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-magic mr-2"></i>
                                {{ __('Jana Kuiz') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

