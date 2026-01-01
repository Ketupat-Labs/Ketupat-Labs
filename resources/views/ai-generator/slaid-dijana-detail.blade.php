<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('ai-generator.slaid-dijana') }}" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Senarai
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Slaid Dijana
                </h2>
            </div>
            <div class="flex items-center space-x-3">
                <select id="export-format" class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="pptx">PPTX</option>
                    <option value="pdf">PDF</option>
                    <option value="docx">DOCX</option>
                    <option value="txt">TXT</option>
                </select>
                <button onclick="exportSlides()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-download mr-2"></i>
                    Eksport
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $topic }}</h1>
                        @if(!empty($slides))
                            <p class="text-gray-600">Jumlah Slaid: {{ count($slides) }}</p>
                        @endif
                    </div>

                    <!-- Generating Status -->
                    @if(isset($status) && $status === 'generating')
                        <div id="generating-status" class="text-center py-12">
                            <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600 mb-4"></div>
                            <p class="text-gray-700 text-xl font-semibold mb-2">Sedang menjana slaid...</p>
                            <p class="text-gray-500 text-sm">Sila tunggu sebentar. Halaman ini akan dimuatkan secara automatik apabila siap.</p>
                            <p class="text-gray-400 text-xs mt-4">Anda boleh menutup halaman ini dan kembali kemudian.</p>
                        </div>
                    @endif

                    <!-- Slides Container -->
                    <div id="slides-container-wrapper" class="{{ (isset($status) && $status === 'generating') ? 'hidden' : '' }}">
                        @if(!empty($slides))
                            <div id="slides-container" class="space-y-6">
                                @foreach($slides as $index => $slide)
                                <div class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-lg p-6 border-2 border-blue-200 shadow-md">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex-1">
                                            <h3 class="text-xl font-bold text-gray-900 mb-2">
                                                Slaid {{ $index + 1 }}: {{ $slide['title'] ?? 'Tanpa Tajuk' }}
                                            </h3>
                                        </div>
                                        <span class="text-xs text-gray-600 bg-white px-3 py-1 rounded-full border border-blue-300 font-semibold">
                                            #{{ $index + 1 }}
                                        </span>
                                    </div>

                                    @if(isset($slide['image_path']) && $slide['image_path'])
                                        <div class="mb-4 p-3 bg-green-50 rounded-lg border border-green-300">
                                            <div class="flex items-center">
                                                <i class="fas fa-image text-green-600 mr-2"></i>
                                                <span class="text-sm text-gray-700 font-medium">Imej akan dimasukkan dalam eksport PPTX</span>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="mb-4">
                                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Kandungan:</h4>
                                        <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                                            @if(is_array($slide['content'] ?? []))
                                                @foreach($slide['content'] as $point)
                                                    <li class="text-gray-800">{{ $point }}</li>
                                                @endforeach
                                            @else
                                                <li class="text-gray-800">{{ $slide['content'] ?? 'Tiada kandungan' }}</li>
                                            @endif
                                        </ul>
                                    </div>

                                    @if(!empty($slide['summary']))
                                        <div class="mt-4 p-3 bg-white rounded-lg border border-blue-200">
                                            <p class="text-sm text-gray-600 italic">
                                                <strong>Ringkasan:</strong> {{ $slide['summary'] }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        @elseif(!isset($status) || $status !== 'generating')
                            <div class="text-center py-12">
                                <i class="fas fa-file-slides text-gray-400 text-6xl mb-4"></i>
                                <p class="text-gray-600 text-lg">Tiada slaid dalam set ini.</p>
                                <a href="{{ route('ai-generator.slaid-dijana') }}" class="inline-block mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    Kembali ke Senarai
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Poll for generation status if generating
        @if(isset($status) && $status === 'generating' && $id === 'generating')
        let pollCount = 0;
        const maxPolls = 300; // 5 minutes max (poll every 2 seconds = 10 minutes total)
        
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
                
                if (data.status === 'completed' && data.set_id) {
                    // Redirect to the completed slide set
                    window.location.href = '{{ route("ai-generator.slaid-dijana.view", ":id") }}'.replace(':id', data.set_id);
                } else if (data.status === 'completed') {
                    // Fallback: reload page
                    window.location.reload();
                } else if (data.status === 'error') {
                    document.getElementById('generating-status').innerHTML = `
                        <div class="text-center py-12">
                            <i class="fas fa-exclamation-triangle text-red-500 text-6xl mb-4"></i>
                            <p class="text-red-600 text-lg font-semibold mb-2">Ralat semasa menjana slaid</p>
                            <p class="text-gray-600 text-sm mb-4">${data.message || 'Ralat tidak diketahui'}</p>
                            <a href="{{ route('ai-generator.slides') }}" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                Cuba Lagi
                            </a>
                        </div>
                    `;
                } else if (data.status === 'generating' || data.status === 'none') {
                    pollCount++;
                    if (pollCount < maxPolls) {
                        setTimeout(checkGenerationStatus, 2000); // Poll every 2 seconds
                    } else {
                        document.getElementById('generating-status').innerHTML = `
                            <div class="text-center py-12">
                                <i class="fas fa-clock text-orange-500 text-6xl mb-4"></i>
                                <p class="text-orange-600 text-lg font-semibold mb-2">Masa menunggu tamat</p>
                                <p class="text-gray-600 text-sm mb-4">Penjanaan slaid mengambil masa yang lama. Sila muat semula halaman atau cuba lagi kemudian.</p>
                                <button onclick="window.location.reload()" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    Muat Semula
                                </button>
                            </div>
                        `;
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

        async function exportSlides() {
            const slides = @json($slides);
            const topic = @json($topic);
            const format = document.getElementById('export-format').value;

            try {
                const response = await fetch('/api/ai-generator/slides/export', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    credentials: 'include',
                    body: JSON.stringify({ slides, topic, format })
                });

                if (!response.ok) {
                    let msg = 'Gagal mengeksport slaid';
                    try {
                        const err = await response.json();
                        msg = err.message || msg;
                    } catch (e) {
                        // ignore
                    }
                    alert(msg);
                    return;
                }

                const blob = await response.blob();
                const contentDisposition = response.headers.get('Content-Disposition') || '';
                const fileNameMatch = contentDisposition.match(/filename="?([^";]+)"?/i);
                const fileName = fileNameMatch ? fileNameMatch[1] : `slides.${format}`;

                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = fileName;
                document.body.appendChild(link);
                link.click();
                link.remove();
                URL.revokeObjectURL(url);
            } catch (error) {
                console.error('Export error:', error);
                alert('Ralat berlaku semasa mengeksport slaid');
            }
        }
    </script>
</x-app-layout>

