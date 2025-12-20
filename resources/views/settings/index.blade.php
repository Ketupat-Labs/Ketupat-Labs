<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Settings') }}
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
                            {{ __('General') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('Manage your general application settings.') }}
                        </p>
                    </header>

                    <div class="mt-6 space-y-6">
                        <!-- AI Chatbot Toggle -->
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <label for="chatbot_enabled" class="block text-sm font-medium text-gray-700">
                                    {{ __('AI Chatbot') }}
                                </label>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ __('Enable or disable the AI chatbot function.') }}
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

            <!-- Privacy Settings -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900">
                            {{ __('Privacy') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('Control who can interact with you.') }}
                        </p>
                    </header>

                    <div class="mt-6 space-y-6">
                        <!-- Allow Friend Requests Toggle -->
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <label for="allow_friend_requests" class="block text-sm font-medium text-gray-700">
                                    {{ __('Allow Friend Requests') }}
                                </label>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ __('Enable or disable other users from adding you as a friend.') }}
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="allow_friend_requests" name="allow_friend_requests" 
                                    class="sr-only peer" {{ $user->allow_friend_requests ?? true ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const chatbotToggle = document.getElementById('chatbot_enabled');
            const friendRequestsToggle = document.getElementById('allow_friend_requests');
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
                        showSuccess('Settings updated successfully');
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

            friendRequestsToggle.addEventListener('change', () => {
                updateSetting('allow_friend_requests', friendRequestsToggle.checked);
            });
        });
    </script>
</x-app-layout>

