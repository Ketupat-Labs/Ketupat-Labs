<!-- Ketupat Chatbot Widget -->
@php
    $currentUser = session('user_id') ? \App\Models\User::find(session('user_id')) : \Illuminate\Support\Facades\Auth::user();
    $chatbotEnabled = $currentUser ? ($currentUser->chatbot_enabled ?? true) : true;
@endphp
@if($chatbotEnabled)
<div id="ketupat-chatbot" class="fixed bottom-6 right-6 z-50">
    <!-- Chatbot Button -->
    <button id="chatbot-toggle"
            class="bg-gradient-to-r from-[#F26430] to-[#FF8C42] text-white rounded-full w-12 h-12 shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center hover:scale-110"
            onclick="toggleChatbot()"
            aria-label="Open Ketupat Chatbot">
        <i class="fas fa-robot text-lg" aria-hidden="true"></i>
    </button>

    <!-- Chatbot Window -->
    <div id="chatbot-window"
         class="hidden absolute bottom-16 right-0 w-80 h-[450px] bg-white rounded-lg shadow-2xl flex flex-col border border-gray-200"
         style="z-index: 9999;">
        <!-- Header -->
        <div class="bg-gradient-to-r from-[#F26430] to-[#FF8C42] text-white p-3 rounded-t-lg flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center">
                    <i class="fas fa-robot text-[#F26430] text-sm"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base">{{ __('Ask Ketupat') }}</h3>
                    <p class="text-xs text-white/90">{{ __('AI Assistant') }}</p>
                </div>
            </div>
            <button onclick="toggleChatbot()"
                    class="text-white hover:text-gray-200 transition-colors"
                    aria-label="Close Chatbot">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Messages Container -->
        <div id="chatbot-messages" class="flex-1 overflow-y-auto p-3 space-y-3 bg-gray-50">
            <div class="flex items-start space-x-2">
                <div class="w-7 h-7 bg-[#F26430] rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-robot text-white text-xs"></i>
                </div>
                <div class="flex-1">
                    <div class="bg-white rounded-lg p-2.5 shadow-sm border border-gray-200">
                        <p class="text-xs text-gray-800">
                            {{ __('Hai! Saya Ketupat, pembantu AI anda. 👋 Anda boleh pilih mana-mana teks di halaman ini dan tanya saya soalan tentangnya!') }}
                        </p>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-1">{{ __('Baru sahaja') }}</p>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-3 border-t border-gray-200 bg-white rounded-b-lg">
            <form id="chatbot-form" onsubmit="sendChatbotMessage(event)" class="flex space-x-2">
                <input type="text"
                       id="chatbot-input"
                       placeholder="{{ __('Taip mesej anda...') }}"
                       class="flex-1 px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F26430] focus:border-transparent"
                       autocomplete="off"
                       style="pointer-events: auto !important; z-index: 1;">
                <button type="submit"
                        id="chatbot-send-btn"
                        class="bg-[#F26430] hover:bg-[#FF8C42] text-white px-3 py-1.5 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        style="pointer-events: auto !important; z-index: 1;">
                    <i class="fas fa-paper-plane text-sm"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Text Highlighting Tooltip -->
<div id="highlight-tooltip"
     class="hidden fixed z-[100] bg-white border border-gray-300 rounded-lg shadow-lg p-2 flex items-center space-x-2"
     style="display: none;">
    <button id="ask-ketupat-btn"
            onclick="askKetupatFromHighlight()"
            class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-3 py-1.5 rounded-md text-sm font-medium hover:shadow-md transition-all flex items-center space-x-2">
        <span>{{ __('Ask Ketupat') }}</span>
    </button>
    <button onclick="clearHighlight()"
            class="text-gray-500 hover:text-gray-700 p-1">
        <i class="fas fa-times"></i>
    </button>
</div>

<!-- Small answer popup for Ask Ketupat (highlight mode) -->
<div id="ask-ketupat-popup"
     class="hidden fixed z-[110] w-[320px] sm:w-[360px] bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden"
     style="display: none;">
    <div class="flex items-center justify-between px-3 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white">
        <div class="flex items-center gap-2">
            <span class="text-sm font-semibold">{{ __('Ask Ketupat') }}</span>
        </div>
        <button type="button" onclick="closeAskKetupatPopup()" class="text-white/90 hover:text-white">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div id="ask-ketupat-popup-body" class="p-3">
        <div id="ask-ketupat-popup-loading" class="text-sm text-gray-600">
            <i class="fas fa-spinner fa-spin mr-2"></i>{{ __('Generating...') }}
        </div>
        <div id="ask-ketupat-popup-error" class="hidden text-sm text-red-600"></div>
        <div id="ask-ketupat-popup-answer" class="hidden text-sm text-gray-800 whitespace-pre-wrap leading-relaxed"></div>
        <div class="mt-3 flex justify-end">
            <button type="button" onclick="closeAskKetupatPopup()" class="px-3 py-1.5 text-sm rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">
                {{ __('Close') }}
            </button>
        </div>
    </div>
</div>

<style>
    #chatbot-messages::-webkit-scrollbar {
        width: 6px;
    }

    #chatbot-messages::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    #chatbot-messages::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }

    #chatbot-messages::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .chatbot-message {
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .typing-indicator {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .typing-indicator span {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #9CA3AF;
        animation: typing 1.4s infinite;
    }

    .typing-indicator span:nth-child(2) {
        animation-delay: 0.2s;
    }

    .typing-indicator span:nth-child(3) {
        animation-delay: 0.4s;
    }

    @keyframes typing {
        0%, 60%, 100% {
            transform: translateY(0);
            opacity: 0.7;
        }
        30% {
            transform: translateY(-10px);
            opacity: 1;
        }
    }

    /* Highlight selection style */
    ::selection {
        background-color: rgba(242, 100, 48, 0.3);
    }
</style>

<script>
    let isChatbotOpen = false;
    let selectedText = '';
    let selectedTextContext = '';

    function toggleChatbot() {
        const window = document.getElementById('chatbot-window');
        const toggle = document.getElementById('chatbot-toggle');
        const input = document.getElementById('chatbot-input');

        isChatbotOpen = !isChatbotOpen;

        if (isChatbotOpen) {
            window.classList.remove('hidden');
            // Ensure input is enabled when opening
            input.disabled = false;
            document.getElementById('chatbot-send-btn').disabled = false;
            setTimeout(() => input.focus(), 100);
        } else {
            window.classList.add('hidden');
        }
    }

    // Ensure input is enabled on page load
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('chatbot-input');
        const sendBtn = document.getElementById('chatbot-send-btn');

        if (input) {
            input.disabled = false;
            input.readOnly = false;

            // Add click handler to ensure focus
            input.addEventListener('click', function() {
                console.log('Input clicked');
                this.disabled = false;
                this.readOnly = false;
                this.focus();
            });

            // Add test to see if input is working
            input.addEventListener('keydown', function(e) {
                console.log('Key pressed:', e.key);
            });
        }

        if (sendBtn) sendBtn.disabled = false;
    });

    async function sendChatbotMessage(event) {
        event.preventDefault();

        const input = document.getElementById('chatbot-input');
        const message = input.value.trim();

        if (!message) return;

        // Add user message to chat
        addMessage(message, 'user');
        input.value = '';
        input.disabled = true;
        document.getElementById('chatbot-send-btn').disabled = true;

        // Show typing indicator
        const typingId = showTypingIndicator();

        try {
            const response = await fetch('/api/chatbot/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    message: message,
                    context: selectedTextContext || null
                })
            });

            const data = await response.json();

            // Remove typing indicator
            removeTypingIndicator(typingId);

            if (data.status === 200 && data.data && data.data.reply) {
                addMessage(data.data.reply, 'bot');
                // Clear context after using it
                selectedTextContext = '';
            } else {
                const msg = (data && (data.message || data.error)) ? (data.message || data.error) : '{{ __('Maaf, saya menghadapi ralat. Sila cuba lagi.') }}';
                addMessage(msg, 'bot', true);
            }
        } catch (error) {
            console.error('Chatbot error:', error);
            removeTypingIndicator(typingId);
            addMessage('{{ __('Maaf, saya menghadapi masalah menyambung. Sila semak sambungan internet anda dan cuba lagi.') }}', 'bot', true);
        } finally {
            // Always re-enable input, even if there was an error
            const input = document.getElementById('chatbot-input');
            const sendBtn = document.getElementById('chatbot-send-btn');
            if (input) {
                input.disabled = false;
                input.focus();
            }
            if (sendBtn) {
                sendBtn.disabled = false;
            }
        }
    }

    function addMessage(text, sender, isError = false) {
        const messagesContainer = document.getElementById('chatbot-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `flex items-start space-x-2 chatbot-message ${sender === 'user' ? 'flex-row-reverse space-x-reverse' : ''}`;

        const time = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

        if (sender === 'user') {
            messageDiv.innerHTML = `
                <div class="flex-1 flex justify-end">
                    <div class="bg-[#F26430] text-white rounded-lg p-3 shadow-sm max-w-[80%]">
                        <p class="text-sm">${escapeHtml(text)}</p>
                    </div>
                </div>
            `;
        } else {
            const errorClass = isError ? 'border-red-300 bg-red-50' : '';
            messageDiv.innerHTML = `
                <div class="w-8 h-8 bg-[#F26430] rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-robot text-white text-xs"></i>
                </div>
                <div class="flex-1">
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200 ${errorClass}">
                        <p class="text-sm text-gray-800">${escapeHtml(text)}</p>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-1">${time}</p>
                </div>
            `;
        }

        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function showTypingIndicator() {
        const messagesContainer = document.getElementById('chatbot-messages');
        const typingDiv = document.createElement('div');
        typingDiv.id = 'typing-indicator';
        typingDiv.className = 'flex items-start space-x-2 chatbot-message';
        typingDiv.innerHTML = `
            <div class="w-8 h-8 bg-[#F26430] rounded-full flex items-center justify-center flex-shrink-0">
                <i class="fas fa-robot text-white text-xs"></i>
            </div>
            <div class="flex-1">
                <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200">
                    <div class="typing-indicator">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        `;
        messagesContainer.appendChild(typingDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        return 'typing-indicator';
    }

    function removeTypingIndicator(id) {
        const indicator = document.getElementById(id);
        if (indicator) {
            indicator.remove();
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Text highlighting functionality
    function handleTextSelection(e) {
        // Don't interfere with inputs or textareas
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) {
            return;
        }

        const selection = window.getSelection();
        const selectedText = selection.toString().trim();

        if (selectedText.length > 0) {
            const range = selection.getRangeAt(0);
            const rect = range.getBoundingClientRect();

            // Store selected text and context
            window.selectedText = selectedText;
            window.selectedTextContext = selectedText;

            // Show tooltip near selection
            const tooltip = document.getElementById('highlight-tooltip');
            tooltip.style.display = 'flex';
            tooltip.style.left = (rect.left + window.scrollX + (rect.width / 2) - 100) + 'px';
            tooltip.style.top = (rect.top + window.scrollY - 50) + 'px';
        } else {
            // Only clear if the tooltip was actually visible to avoid killing cursor in inputs
            const tooltip = document.getElementById('highlight-tooltip');
            if (tooltip && tooltip.style.display !== 'none') {
                clearHighlight();
            }
        }
    }

    function askKetupatFromHighlight() {
        const text = window.selectedText || '';
        if (text) {
            askKetupatInSmallWindow(text);
        }
    }

    function positionAskKetupatPopupNearTooltip() {
        const popup = document.getElementById('ask-ketupat-popup');
        const tooltip = document.getElementById('highlight-tooltip');
        if (!popup || !tooltip) return;

        const rect = tooltip.getBoundingClientRect();
        const popupWidth = popup.offsetWidth || 360;

        let left = rect.left + window.scrollX + (rect.width / 2) - (popupWidth / 2);
        let top = rect.top + window.scrollY + rect.height + 10;

        // keep in viewport
        const minLeft = window.scrollX + 10;
        const maxLeft = window.scrollX + window.innerWidth - popupWidth - 10;
        left = Math.max(minLeft, Math.min(left, maxLeft));

        // if too low, show above tooltip
        const approxHeight = 240;
        if (top + approxHeight > window.scrollY + window.innerHeight - 10) {
            top = rect.top + window.scrollY - approxHeight - 10;
        }

        popup.style.left = left + 'px';
        popup.style.top = top + 'px';
    }

    function openAskKetupatPopup() {
        const popup = document.getElementById('ask-ketupat-popup');
        if (!popup) return;
        popup.style.display = 'block';
    }

    function closeAskKetupatPopup() {
        const popup = document.getElementById('ask-ketupat-popup');
        if (!popup) return;
        popup.style.display = 'none';
        document.getElementById('ask-ketupat-popup-answer')?.classList.add('hidden');
        document.getElementById('ask-ketupat-popup-error')?.classList.add('hidden');
        document.getElementById('ask-ketupat-popup-loading')?.classList.remove('hidden');
    }

    async function askKetupatInSmallWindow(text) {
        const loading = document.getElementById('ask-ketupat-popup-loading');
        const answerEl = document.getElementById('ask-ketupat-popup-answer');
        const errorEl = document.getElementById('ask-ketupat-popup-error');

        if (!loading || !answerEl || !errorEl) return;

        // reset UI
        answerEl.classList.add('hidden');
        errorEl.classList.add('hidden');
        loading.classList.remove('hidden');
        answerEl.textContent = '';
        errorEl.textContent = '';

        // show popup
        openAskKetupatPopup();
        positionAskKetupatPopupNearTooltip();

        try {
            const response = await fetch('/api/chatbot/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    message: `{{ __('Explain this') }}: ${text.substring(0, 1200)}`,
                    context: text
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error((data && (data.message || data.error)) ? (data.message || data.error) : 'Request failed');
            }

            if (data.status === 200 && data.data && data.data.reply) {
                loading.classList.add('hidden');
                answerEl.textContent = data.data.reply;
                answerEl.classList.remove('hidden');
                // clear highlight so user can select new text quickly
                clearHighlight();
            } else {
                throw new Error(data.message || '{{ __('Maaf, saya tidak dapat menjana jawapan. Sila cuba lagi.') }}');
            }
        } catch (e) {
            loading.classList.add('hidden');
            errorEl.textContent = e.message || '{{ __('Maaf, saya menghadapi ralat. Sila cuba lagi.') }}';
            errorEl.classList.remove('hidden');
        }
    }

    function clearHighlight() {
        window.getSelection().removeAllRanges();
        const tooltip = document.getElementById('highlight-tooltip');
        tooltip.style.display = 'none';
        window.selectedText = '';
        window.selectedTextContext = '';
    }

    // Listen for text selection
    document.addEventListener('mouseup', handleTextSelection);
    document.addEventListener('keyup', function(e) {
        if (e.key === 'Escape') {
            clearHighlight();
        }
    });

    // Close chatbot when clicking outside
    document.addEventListener('click', function(event) {
        const chatbot = document.getElementById('ketupat-chatbot');
        const window = document.getElementById('chatbot-window');
        const toggle = document.getElementById('chatbot-toggle');
        const tooltip = document.getElementById('highlight-tooltip');

        // Don't close if clicking on tooltip
        if (tooltip && tooltip.contains(event.target)) {
            return;
        }

        // Don't close small popup if clicking inside it
        const askPopup = document.getElementById('ask-ketupat-popup');
        if (askPopup && askPopup.contains(event.target)) {
            return;
        }

        if (isChatbotOpen &&
            !chatbot.contains(event.target) &&
            !window.contains(event.target) &&
            !toggle.contains(event.target)) {
            toggleChatbot();
        }
    });

    // Handle Enter key
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('chatbot-input');
        if (input) {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    const form = document.getElementById('chatbot-form');
                    if (form) {
                        sendChatbotMessage(e);
                    }
                }
            });
        }
    });

    // Reposition popup on resize/scroll
    window.addEventListener('resize', function() {
        const popup = document.getElementById('ask-ketupat-popup');
        if (popup && popup.style.display !== 'none') {
            positionAskKetupatPopupNearTooltip();
        }
    });
    window.addEventListener('scroll', function() {
        const popup = document.getElementById('ask-ketupat-popup');
        if (popup && popup.style.display !== 'none') {
            positionAskKetupatPopupNearTooltip();
        }
    }, { passive: true });
</script>
@endif

