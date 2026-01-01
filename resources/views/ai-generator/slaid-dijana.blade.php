<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Slaid Dijana
            </h2>
            <a href="{{ route('ai-generator.slides') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Jana Slaid Baru
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('info'))
                <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-blue-800">{{ session('info') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h1 class="text-2xl font-bold text-gray-900 mb-2">Senarai Slaid Dijana</h1>
                        <p class="text-gray-600">Pilih slaid untuk melihat atau eksport</p>
                    </div>

                    @if(isset($slideSets) && !empty($slideSets))
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($slideSets as $set)
                                <a href="{{ route('ai-generator.slaid-dijana.view', $set['id']) }}" 
                                   class="block bg-gradient-to-br from-blue-50 to-purple-50 rounded-lg p-6 border-2 border-blue-200 hover:border-blue-400 hover:shadow-lg transition-all duration-200">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex-1">
                                            <h3 class="text-lg font-bold text-gray-900 mb-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                {{ $set['topic'] ?? 'Tanpa Tajuk' }}
                                            </h3>
                                            <p class="text-sm text-gray-500">
                                                @php
                                                    try {
                                                        $date = isset($set['created_at']) && $set['created_at'] ? \Carbon\Carbon::parse($set['created_at']) : now();
                                                        echo $date->format('d M Y, H:i');
                                                    } catch (\Exception $e) {
                                                        echo now()->format('d M Y, H:i');
                                                    }
                                                @endphp
                                            </p>
                                        </div>
                                        @if(isset($set['status']) && $set['status'] === 'generating')
                                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                                        @else
                                            <i class="fas fa-file-slides text-blue-600 text-2xl"></i>
                                        @endif
                                    </div>
                                    
                                    <div class="mt-4 pt-4 border-t border-blue-200">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">
                                                <i class="fas fa-sliders-h mr-1"></i>
                                                {{ $set['slide_count'] ?? count($set['slides'] ?? []) }} slaid
                                            </span>
                                            @if(isset($set['status']) && $set['status'] === 'generating')
                                                <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full">
                                                    Sedang menjana
                                                </span>
                                            @else
                                                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">
                                                    <i class="fas fa-check-circle mr-1"></i>Siap
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <i class="fas fa-file-slides text-gray-400 text-6xl mb-4"></i>
                            <p class="text-gray-600 text-lg mb-2">Tiada slaid yang dijana lagi.</p>
                            <p class="text-gray-500 text-sm mb-6">Mulakan dengan menjana slaid baru.</p>
                            <a href="{{ route('ai-generator.slides') }}" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-plus mr-2"></i>
                                Jana Slaid Baru
                            </a>
                        </div>
                    @endif

                    <!-- Check for generating status and poll -->
                    @if(isset($status) && $status === 'generating')
                        <div id="generating-indicator" class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-center">
                                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600 mr-3"></div>
                                <p class="text-sm text-blue-800">Slaid sedang dijana. Halaman akan dikemaskini secara automatik apabila siap.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        // Poll for generation status if generating
        @if(isset($status) && $status === 'generating')
        let pollCount = 0;
        const maxPolls = 300; // 10 minutes max (poll every 2 seconds)
        
        async function checkGenerationStatus() {
            try {
                const response = await fetch('{{ route("ai-generator.check-status") }}', {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    credentials: 'include'
                });
                
                const data = await response.json();
                
                if (data.status === 'completed') {
                    // Reload page to show new slide set
                    window.location.reload();
                } else if (data.status === 'error') {
                    document.getElementById('generating-indicator').innerHTML = `
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>
                            <p class="text-sm text-red-800">Ralat semasa menjana slaid: ${data.message || 'Ralat tidak diketahui'}</p>
                        </div>
                    `;
                } else if (data.status === 'generating' || data.status === 'none') {
                    pollCount++;
                    if (pollCount < maxPolls) {
                        setTimeout(checkGenerationStatus, 2000); // Poll every 2 seconds
                    }
                }
            } catch (error) {
                console.error('Error checking status:', error);
                setTimeout(checkGenerationStatus, 5000); // Retry after 5 seconds on error
            }
        }
        
        // Start polling when page loads
        setTimeout(checkGenerationStatus, 2000); // Start after 2 seconds
        @endif
    </script>
</x-app-layout>
