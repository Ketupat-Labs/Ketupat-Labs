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
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 mb-2">Senarai Slaid Dijana</h1>
                            <p class="text-gray-600">Pilih slaid untuk melihat atau eksport</p>
                        </div>
                        <!-- Bulk Delete Button - hidden by default -->
                        <div id="bulk-delete-controls" class="hidden">
                            <button onclick="deleteSelected()"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors shadow-md hover:shadow-lg">
                                <i class="fas fa-trash-alt mr-2"></i>
                                Padam Dipilih (<span id="selected-count">0</span>)
                            </button>
                        </div>
                    </div>

                    @if(isset($slideSets) && !empty($slideSets))
                        <!-- Selection Mode Toggle -->
                        <div class="mb-4 flex items-center justify-between">
                            <button onclick="toggleSelectionMode()"
                                    id="selection-mode-btn"
                                    class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors border border-gray-300">
                                <i class="fas fa-check-square mr-2"></i>
                                Pilih Slaid
                            </button>
                            <button onclick="selectAll()"
                                    id="select-all-btn"
                                    class="hidden inline-flex items-center px-3 py-2 text-sm bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors">
                                <i class="fas fa-check-double mr-2"></i>
                                Pilih Semua
                            </button>
                        </div>
                        <!-- Auto-refresh indicator for generating slides -->
                        <div id="auto-refresh-indicator" class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-purple-50 border-2 border-blue-300 rounded-lg shadow-md hidden">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="relative mr-4">
                                        <div class="animate-spin rounded-full h-8 w-8 border-4 border-blue-200 border-t-blue-600"></div>
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <div class="h-2 w-2 bg-blue-600 rounded-full animate-pulse"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-blue-900">🎨 Sedang Menjana Slaid...</p>
                                        <p class="text-xs text-blue-700 mt-1">AI sedang mencipta kandungan. Halaman akan dikemaskini secara automatik.</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span id="poll-counter" class="text-xs text-blue-600 font-mono bg-white px-2 py-1 rounded"></span>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="w-full bg-blue-200 rounded-full h-1.5">
                                    <div id="progress-bar" class="bg-blue-600 h-1.5 rounded-full transition-all duration-500" style="width: 0%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($slideSets as $set)
                                <div class="relative bg-gradient-to-br from-blue-50 to-purple-50 rounded-lg border-2 border-blue-200 hover:border-blue-400 hover:shadow-lg transition-all duration-200 group {{ isset($set['status']) && $set['status'] === 'generating' ? 'slide-generating' : '' }}"
                                     data-slide-id="{{ $set['id'] }}">

                                    <!-- Checkbox for bulk selection - hidden by default -->
                                    <div class="absolute top-3 left-3 z-20 selection-checkbox hidden">
                                        <input type="checkbox"
                                               class="slide-checkbox w-5 h-5 text-blue-600 bg-white border-2 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 cursor-pointer"
                                               data-slide-id="{{ $set['id'] }}"
                                               onchange="updateSelectedCount()">
                                    </div>

                                    <a href="{{ route('ai-generator.slaid-dijana.view', $set['id']) }}" class="block p-6">
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex-1 pr-4">
                                                <h3 class="text-lg font-bold text-gray-900 mb-1 line-clamp-2">
                                                    {{ $set['topic'] ?? 'Tanpa Tajuk' }}
                                                </h3>
                                                <p class="text-sm text-gray-500 flex items-center">
                                                    <i class="fas fa-clock mr-1.5 text-xs"></i>
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
                                                <div class="relative flex-shrink-0">
                                                    <div class="animate-spin rounded-full h-10 w-10 border-3 border-blue-200 border-t-blue-600"></div>
                                                    <div class="absolute inset-0 flex items-center justify-center">
                                                        <div class="h-2 w-2 bg-blue-600 rounded-full animate-pulse"></div>
                                                    </div>
                                                </div>
                                            @else
                                                <i class="fas fa-file-powerpoint text-blue-600 text-3xl flex-shrink-0"></i>
                                            @endif
                                        </div>

                                        <div class="mt-4 pt-4 border-t border-blue-200">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm text-gray-600 font-medium flex items-center">
                                                    <i class="fas fa-sliders-h mr-2"></i>
                                                    {{ $set['slide_count'] ?? count($set['slides'] ?? []) }} slaid
                                                </span>
                                                @if(isset($set['status']) && $set['status'] === 'generating')
                                                    <span class="text-xs bg-yellow-100 text-yellow-800 px-3 py-1.5 rounded-full font-semibold animate-pulse flex items-center">
                                                        <i class="fas fa-spinner fa-spin mr-1.5"></i>Sedang menjana
                                                    </span>
                                                @else
                                                    <span class="text-xs bg-green-100 text-green-800 px-3 py-1.5 rounded-full font-semibold flex items-center">
                                                        <i class="fas fa-check-circle mr-1.5"></i>Siap
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-16 bg-gradient-to-br from-gray-50 to-blue-50 rounded-lg border-2 border-dashed border-gray-300">
                            <div class="mb-4">
                                <i class="fas fa-file-powerpoint text-gray-400 text-6xl mb-2"></i>
                                <div class="inline-block animate-bounce">
                                    <i class="fas fa-arrow-down text-blue-500 text-2xl"></i>
                                </div>
                            </div>
                            <p class="text-gray-700 text-xl font-semibold mb-2">Tiada slaid yang dijana lagi.</p>
                            <p class="text-gray-500 text-sm mb-6">Mulakan dengan menjana slaid baru menggunakan AI.</p>
                            <a href="{{ route('ai-generator.slides') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                <i class="fas fa-magic mr-2"></i>
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
        // Request notification permission on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Request notification permission
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }

            const generatingSlides = document.querySelectorAll('.slide-generating');
            if (generatingSlides.length > 0) {
                const indicator = document.getElementById('auto-refresh-indicator');
                if (indicator) {
                    indicator.classList.remove('hidden');
                }
            }
        });

        // Function to show browser notification
        function showNotification(title, body, icon = '✅') {
            if ('Notification' in window && Notification.permission === 'granted') {
                const notification = new Notification(title, {
                    body: body,
                    icon: '/favicon.ico', // You can use your app logo
                    badge: '/favicon.ico',
                    tag: 'slide-generation',
                    requireInteraction: false,
                    silent: false
                });

                // Auto-close after 5 seconds
                setTimeout(() => notification.close(), 5000);

                // Focus window when notification clicked
                notification.onclick = function() {
                    window.focus();
                    this.close();
                };
            }
        }

        // Poll for generation status if generating
        @if(isset($status) && $status === 'generating')
        let pollCount = 0;
        const maxPolls = 300; // 10 minutes max (poll every 2 seconds)
        const progressBar = document.getElementById('progress-bar');
        const pollCounter = document.getElementById('poll-counter');
        const autoRefreshIndicator = document.getElementById('auto-refresh-indicator');

        // Show the auto-refresh indicator
        if (autoRefreshIndicator) {
            autoRefreshIndicator.classList.remove('hidden');
        }

        async function checkGenerationStatus() {
            try {
                pollCount++;

                // Update progress bar (smooth animation)
                const progress = Math.min((pollCount / maxPolls) * 100, 95); // Max 95% until complete
                if (progressBar) {
                    progressBar.style.width = progress + '%';
                }

                // Update counter
                if (pollCounter) {
                    const elapsed = Math.floor(pollCount * 2); // seconds
                    pollCounter.textContent = elapsed + 's';
                }

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
                    // Complete progress bar
                    if (progressBar) {
                        progressBar.style.width = '100%';
                    }

                    // Show congratulations message on page
                    if (autoRefreshIndicator) {
                        autoRefreshIndicator.innerHTML = `
                            <div class="flex items-center justify-center p-8 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border-2 border-green-400 shadow-2xl">
                                <div class="flex items-center text-green-800">
                                    <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-full p-4 mr-5 animate-bounce shadow-lg">
                                        <i class="fas fa-check-circle text-white text-4xl"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-2xl text-green-900">🎉 Tahniah! Fail Berjaya Dijana!</p>
                                        <p class="text-base mt-2 text-green-700">Slaid anda telah siap dan boleh dimuat turun. Memuatkan semula halaman...</p>
                                    </div>
                                </div>
                            </div>
                        `;
                    }

                    // Show toast notification
                    showToast('🎉 Tahniah!', 'Fail slaid berjaya dijana dan boleh dimuat turun sekarang.', 'success');

                    // Play success sound
                    playNotificationSound('success');

                    // Reload page after brief delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                } else if (data.status === 'error') {
                    // No browser notification or sound - just visual feedback on page

                    if (autoRefreshIndicator) {
                        autoRefreshIndicator.innerHTML = `
                            <div class="flex items-center p-4 bg-red-50 rounded-lg border-2 border-red-300">
                                <i class="fas fa-exclamation-triangle text-red-500 text-2xl mr-3"></i>
                                <div>
                                    <p class="text-sm font-semibold text-red-900">Ralat semasa menjana slaid</p>
                                    <p class="text-xs text-red-700 mt-1">${data.message || 'Ralat tidak diketahui'}</p>
                                </div>
                            </div>
                        `;
                    }
                    if (document.getElementById('generating-indicator')) {
                        document.getElementById('generating-indicator').innerHTML = `
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>
                                <p class="text-sm text-red-800">Ralat: ${data.message || 'Ralat tidak diketahui'}</p>
                            </div>
                        `;
                    }
                } else if (data.status === 'generating' || data.status === 'none') {
                    if (pollCount < maxPolls) {
                        setTimeout(checkGenerationStatus, 2000); // Poll every 2 seconds
                    } else {
                        // Timeout - still reload to show current state
                        window.location.reload();
                    }
                }
            } catch (error) {
                console.error('Error checking status:', error);
                if (pollCount < maxPolls) {
                    setTimeout(checkGenerationStatus, 5000); // Retry after 5 seconds on error
                }
            }
        }

        // Start polling when page loads
        setTimeout(checkGenerationStatus, 2000); // Start after 2 seconds
        @endif

        // Delete slide set function
        async function deleteSlideSet(slideId, event) {
            event.preventDefault();
            event.stopPropagation();

            // Create custom confirmation modal
            const confirmed = await showConfirmDialog(
                'Padam Slaid?',
                'Adakah anda pasti untuk memadam set slaid ini? Tindakan ini tidak boleh dibatalkan.',
                'Padam',
                'Batal'
            );

            if (!confirmed) {
                return;
            }

            // Show loading state
            const card = document.querySelector(`[data-slide-id="${slideId}"]`);
            if (card) {
                card.style.opacity = '0.5';
                card.style.pointerEvents = 'none';
            }

            try {
                const response = await fetch(`/ai-generator/slides/${slideId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    credentials: 'include'
                });

                const data = await response.json();

                if (data.success) {
                    // Show success toast
                    showToast('✓ Berjaya Dipadam', 'Slaid telah dipadam dengan jayanya.', 'success');

                    // Remove the card from DOM with animation
                    if (card) {
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.9)';
                        setTimeout(() => {
                            card.remove();
                            // Reload if no more slides
                            const remainingSlides = document.querySelectorAll('[data-slide-id]').length;
                            if (remainingSlides === 0) {
                                window.location.reload();
                            }
                        }, 300);
                    }
                } else {
                    // Restore card state
                    if (card) {
                        card.style.opacity = '1';
                        card.style.pointerEvents = 'auto';
                    }
                    showToast('❌ Ralat', data.message || 'Gagal memadam slaid', 'error');
                }
            } catch (error) {
                console.error('Error deleting slide:', error);
                // Restore card state
                if (card) {
                    card.style.opacity = '1';
                    card.style.pointerEvents = 'auto';
                }
                showToast('❌ Ralat', 'Gagal memadam slaid. Sila cuba lagi.', 'error');
            }
        }

        // Custom confirmation dialog
        function showConfirmDialog(title, message, confirmText, cancelText) {
            return new Promise((resolve) => {
                // Create modal overlay
                const overlay = document.createElement('div');
                overlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 animate-fade-in';

                // Create modal
                const modal = document.createElement('div');
                modal.className = 'bg-white rounded-lg shadow-2xl p-6 max-w-md mx-4 transform transition-all duration-300 scale-95';

                modal.innerHTML = `
                    <div class="flex items-start mb-4">
                        <div class="flex-shrink-0 bg-red-100 rounded-full p-3 mr-4">
                            <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">${title}</h3>
                            <p class="text-gray-600">${message}</p>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button id="cancel-btn" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold transition-colors">
                            ${cancelText}
                        </button>
                        <button id="confirm-btn" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold transition-colors">
                            <i class="fas fa-trash-alt mr-2"></i>${confirmText}
                        </button>
                    </div>
                `;

                overlay.appendChild(modal);
                document.body.appendChild(overlay);

                // Animate in
                setTimeout(() => {
                    modal.style.transform = 'scale(1)';
                }, 10);

                // Handle confirm
                modal.querySelector('#confirm-btn').onclick = () => {
                    overlay.style.opacity = '0';
                    setTimeout(() => overlay.remove(), 200);
                    resolve(true);
                };

                // Handle cancel
                const cancelHandler = () => {
                    overlay.style.opacity = '0';
                    setTimeout(() => overlay.remove(), 200);
                    resolve(false);
                };

                modal.querySelector('#cancel-btn').onclick = cancelHandler;
                overlay.onclick = (e) => {
                    if (e.target === overlay) cancelHandler();
                };
            });
        }

        // Notification functions
        function showNotification(title, message, type = 'info') {
            // Request permission for browser notifications
            if ('Notification' in window && Notification.permission === 'granted') {
                // Show browser notification
                const notification = new Notification(title, {
                    body: message,
                    icon: type === 'success' ? '/assets/images/LOGOCompuPlay.png' : '/assets/images/LOGOCompuPlay.png',
                    badge: '/assets/images/LOGOCompuPlay.png',
                    tag: 'slide-generation',
                    requireInteraction: false,
                    silent: false
                });

                notification.onclick = function() {
                    window.focus();
                    notification.close();
                };

                // Auto close after 5 seconds
                setTimeout(() => notification.close(), 5000);
            } else if ('Notification' in window && Notification.permission !== 'denied') {
                // Request permission
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        showNotification(title, message, type);
                    }
                });
            }

            // Also show in-app toast notification
            showToast(title, message, type);
        }

        function showToast(title, message, type = 'info') {
            // Create toast element
            const toast = document.createElement('div');
            toast.className = `fixed top-20 right-4 max-w-md transform transition-all duration-500 ease-out z-50 ${
                type === 'success' ? 'bg-green-500' :
                type === 'error' ? 'bg-red-500' :
                'bg-blue-500'
            } text-white rounded-lg shadow-2xl p-4 flex items-start space-x-3`;

            const icon = type === 'success' ? 'fa-check-circle' :
                        type === 'error' ? 'fa-exclamation-circle' :
                        'fa-info-circle';

            toast.innerHTML = `
                <div class="flex-shrink-0">
                    <i class="fas ${icon} text-2xl"></i>
                </div>
                <div class="flex-1">
                    <p class="font-bold text-lg">${title}</p>
                    <p class="text-sm mt-1 opacity-90">${message}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="flex-shrink-0 text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            `;

            document.body.appendChild(toast);

            // Slide in animation
            setTimeout(() => {
                toast.style.transform = 'translateX(0)';
            }, 10);

            // Auto remove after 5 seconds
            setTimeout(() => {
                toast.style.transform = 'translateX(150%)';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 500);
            }, 5000);
        }

        function playNotificationSound(type = 'success') {
            // Create and play notification sound
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            if (type === 'success') {
                // Success sound: two ascending tones
                oscillator.frequency.value = 523.25; // C5
                gainNode.gain.value = 0.3;
                oscillator.start(audioContext.currentTime);
                oscillator.frequency.setValueAtTime(659.25, audioContext.currentTime + 0.1); // E5
                oscillator.stop(audioContext.currentTime + 0.2);
            } else if (type === 'error') {
                // Error sound: descending tone
                oscillator.frequency.value = 400;
                gainNode.gain.value = 0.3;
                oscillator.start(audioContext.currentTime);
                oscillator.frequency.setValueAtTime(300, audioContext.currentTime + 0.1);
                oscillator.stop(audioContext.currentTime + 0.2);
            }
        }

        // Request notification permission on page load
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }

        // Bulk Delete Functions
        let selectionMode = false;

        function toggleSelectionMode() {
            selectionMode = !selectionMode;
            const checkboxes = document.querySelectorAll('.selection-checkbox');
            const selectAllBtn = document.getElementById('select-all-btn');
            const selectionModeBtn = document.getElementById('selection-mode-btn');

            checkboxes.forEach(cb => {
                if (selectionMode) {
                    cb.classList.remove('hidden');
                } else {
                    cb.classList.add('hidden');
                    cb.querySelector('input').checked = false;
                }
            });

            if (selectionMode) {
                selectAllBtn.classList.remove('hidden');
                selectionModeBtn.innerHTML = '<i class="fas fa-times mr-2"></i>Batal Pilihan';
                selectionModeBtn.classList.add('bg-red-100', 'text-red-700');
                selectionModeBtn.classList.remove('bg-gray-100', 'text-gray-700');
            } else {
                selectAllBtn.classList.add('hidden');
                selectionModeBtn.innerHTML = '<i class="fas fa-check-square mr-2"></i>Pilih Slaid';
                selectionModeBtn.classList.remove('bg-red-100', 'text-red-700');
                selectionModeBtn.classList.add('bg-gray-100', 'text-gray-700');
            }

            updateSelectedCount();
        }

        function selectAll() {
            const checkboxes = document.querySelectorAll('.slide-checkbox');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);

            checkboxes.forEach(cb => {
                cb.checked = !allChecked;
            });

            updateSelectedCount();
        }

        function updateSelectedCount() {
            const checkboxes = document.querySelectorAll('.slide-checkbox:checked');
            const count = checkboxes.length;
            const bulkDeleteControls = document.getElementById('bulk-delete-controls');
            const selectedCountSpan = document.getElementById('selected-count');

            if (count > 0) {
                bulkDeleteControls.classList.remove('hidden');
                selectedCountSpan.textContent = count;
            } else {
                bulkDeleteControls.classList.add('hidden');
            }
        }

        async function deleteSelected() {
            const checkboxes = document.querySelectorAll('.slide-checkbox:checked');
            const selectedIds = Array.from(checkboxes).map(cb => cb.getAttribute('data-slide-id'));

            if (selectedIds.length === 0) {
                showToast('⚠️ Tiada Pilihan', 'Sila pilih sekurang-kurangnya satu slaid untuk dipadam.', 'error');
                return;
            }

            const confirmed = await showConfirmDialog(
                'Padam Slaid Dipilih?',
                `Adakah anda pasti untuk memadam ${selectedIds.length} slaid yang dipilih? Tindakan ini tidak boleh dibatalkan.`,
                'Padam Semua',
                'Batal'
            );

            if (!confirmed) {
                return;
            }

            // Show progress
            let deleted = 0;
            const total = selectedIds.length;

            for (const slideId of selectedIds) {
                try {
                    const response = await fetch(`/ai-generator/slides/${slideId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        credentials: 'include'
                    });

                    const data = await response.json();

                    if (data.success) {
                        deleted++;
                        // Remove card with animation
                        const card = document.querySelector(`[data-slide-id="${slideId}"]`);
                        if (card) {
                            card.style.opacity = '0';
                            card.style.transform = 'scale(0.9)';
                            setTimeout(() => card.remove(), 300);
                        }
                    }
                } catch (error) {
                    console.error('Error deleting slide:', slideId, error);
                }
            }

            // Show result
            if (deleted === total) {
                showToast('✓ Berjaya Dipadam', `${deleted} slaid telah dipadam dengan jayanya.`, 'success');
            } else {
                showToast('⚠️ Sebahagian Berjaya', `${deleted} daripada ${total} slaid berjaya dipadam.`, 'error');
            }

            // Reset selection mode after delay
            setTimeout(() => {
                const remainingSlides = document.querySelectorAll('[data-slide-id]').length;
                if (remainingSlides === 0) {
                    window.location.reload();
                } else {
                    toggleSelectionMode();
                    updateSelectedCount();
                }
            }, 500);
        }
    </script>
</x-app-layout>
