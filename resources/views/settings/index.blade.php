<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tetapan
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Success Message -->
            <div id="successMessage" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline" id="successText">Settings updated successfully</span>
            </div>

            <!-- Error Message -->
            <div id="errorMessage" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline" id="errorText">Failed to update settings</span>
            </div>

            <!-- General Settings -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900">
                            Umum
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            Urus tetapan umum aplikasi anda.
                        </p>
                    </header>

                    <div class="mt-6 space-y-6">
                        <!-- AI Chatbot Toggle -->
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <label for="chatbot_enabled" class="block text-sm font-medium text-gray-700">
                                    Chatbot AI
                                </label>
                                <p class="mt-1 text-sm text-gray-500">
                                    Aktifkan atau nyahaktifkan fungsi chatbot AI.
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="chatbot_enabled" name="chatbot_enabled" 
                                    class="sr-only peer" {{ $user->chatbot_enabled ?? true ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Badge Privacy Settings -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900">
                            Privasi Lencana
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            Kawal lencana yang boleh dilihat pada profil dan kad prestasi anda.
                        </p>
                    </header>

                    <div class="mt-6 space-y-6">
                        <!-- Share Badges Toggle -->
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <label for="share_badges_on_profile" class="block text-sm font-medium text-gray-700">
                                    Kongsi Lencana pada Profil
                                </label>
                                <p class="mt-1 text-sm text-gray-500">
                                    Benarkan orang lain melihat lencana yang anda perolehi.
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="share_badges_on_profile" name="share_badges_on_profile" 
                                    class="sr-only peer" {{ ($user->share_badges_on_profile ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <!-- Badge Selection (shown when sharing is enabled) -->
                        <div id="badgeSelectionContainer" class="{{ ($user->share_badges_on_profile ?? true) ? '' : 'hidden' }}">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Pilih lencana untuk dipaparkan:
                            </label>
                            <div id="badgeListContainer" class="space-y-2 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-4">
                                <!-- Badge checkboxes will be populated by JavaScript -->
                                <p class="text-sm text-gray-500 text-center py-4">Loading badges...</p>
                            </div>
                        </div>

                        <!-- Info message when sharing is disabled -->
                        <div id="badgeSharingDisabled" class="{{ ($user->share_badges_on_profile ?? true) ? 'hidden' : '' }} bg-gray-50 border border-gray-200 rounded-lg p-4 text-center">
                            <i class="fas fa-eye-slash text-gray-400 text-3xl mb-3"></i>
                            <h3 class="font-semibold text-gray-900 mb-1">Perkongsian Lencana Dimatikan</h3>
                            <p class="text-sm text-gray-600">
                                Aktifkan togol di atas untuk memaparkan lencana pencapaian anda kepada pengguna lain di profil anda.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const chatbotToggle = document.getElementById('chatbot_enabled');
            const successMessage = document.getElementById('successMessage');
            const errorMessage = document.getElementById('errorMessage');

            function updateSetting(setting, value) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                
                fetch('{{ route('settings.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        [setting]: value
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 200) {
                        showSuccess(data.message);
                    } else {
                        showError(data.message || 'Failed to update settings');
                    }
                })
                .catch(error => {
                    console.error('Error updating settings:', error);
                    showError('Failed to update settings. Please try again.');
                });
            }

            function showSuccess(message) {
                errorMessage.classList.add('hidden');
                successMessage.classList.remove('hidden');
                document.getElementById('successText').textContent = message;
                setTimeout(() => {
                    successMessage.classList.add('hidden');
                }, 3000);
            }

            function showError(message) {
                successMessage.classList.add('hidden');
                errorMessage.classList.remove('hidden');
                document.getElementById('errorText').textContent = message;
                setTimeout(() => {
                    errorMessage.classList.add('hidden');
                }, 5000);
            }

            chatbotToggle.addEventListener('change', () => {
                updateSetting('chatbot_enabled', chatbotToggle.checked);
                // Hide or show chatbot widget based on setting
                const chatbotWidget = document.getElementById('ketupat-chatbot');
                if (chatbotWidget) {
                    if (chatbotToggle.checked) {
                        chatbotWidget.style.display = 'block';
                    } else {
                        chatbotWidget.style.display = 'none';
                        // Close chatbot window if open
                        const chatbotWindow = document.getElementById('chatbot-window');
                        if (chatbotWindow) {
                            chatbotWindow.classList.add('hidden');
                        }
                    }
                }
            });

            // Badge Privacy Toggle
            const shareBadgesToggle = document.getElementById('share_badges_on_profile');
            const badgeSelectionContainer = document.getElementById('badgeSelectionContainer');
            const badgeSharingDisabled = document.getElementById('badgeSharingDisabled');
            
            shareBadgesToggle.addEventListener('change', () => {
                const isEnabled = shareBadgesToggle.checked;
                updateSetting('share_badges_on_profile', isEnabled);
                
                if (isEnabled) {
                    badgeSelectionContainer.classList.remove('hidden');
                    badgeSharingDisabled.classList.add('hidden');
                } else {
                    badgeSelectionContainer.classList.add('hidden');
                    badgeSharingDisabled.classList.remove('hidden');
                }
            });

            // Load user's earned badges for selection
            function loadUserBadges() {
                fetch('/api/settings/badges', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    credentials: 'include'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 200 && data.badges) {
                        renderBadgeCheckboxes(data.badges, data.visible_badge_codes || []);
                    } else {
                        document.getElementById('badgeListContainer').innerHTML = '<p class="text-sm text-gray-500 text-center py-4">No badges earned yet.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error loading badges:', error);
                    document.getElementById('badgeListContainer').innerHTML = '<p class="text-sm text-red-500 text-center py-4">Error loading badges.</p>';
                });
            }

            function renderBadgeCheckboxes(badges, visibleCodes) {
                const container = document.getElementById('badgeListContainer');
                if (badges.length === 0) {
                    container.innerHTML = '<p class="text-sm text-gray-500 text-center py-4">No badges earned yet.</p>';
                    return;
                }

                container.innerHTML = badges.map(badge => `
                    <label class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded cursor-pointer">
                        <input type="checkbox" class="badge-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                               value="${badge.code}" ${visibleCodes.includes(badge.code) ? 'checked' : ''}>
                        <div class="flex-1 flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-lg" style="background-color: ${badge.color || '#3B82F6'}20; color: ${badge.color || '#3B82F6'};">
                                <i class="${badge.icon || 'fas fa-trophy'}"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-900">${badge.name}</span>
                        </div>
                    </label>
                `).join('');

                // Add event listeners to checkboxes
                container.querySelectorAll('.badge-checkbox').forEach(checkbox => {
                    checkbox.addEventListener('change', () => {
                        const checkedBadges = Array.from(container.querySelectorAll('.badge-checkbox:checked')).map(cb => cb.value);
                        updateSetting('visible_badge_codes', checkedBadges);
                    });
                });
            }

            // Load badges on page load
            loadUserBadges();
        });
    </script>
</x-app-layout>

