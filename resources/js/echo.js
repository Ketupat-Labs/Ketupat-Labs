import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

if (import.meta.env.VITE_PUSHER_APP_KEY) {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_PUSHER_APP_KEY,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
        wsHost: import.meta.env.VITE_PUSHER_HOST ?? `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1'}.pusher.com`,
        wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
        wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'include', // Important: Include cookies for session authentication
        },
    });
    
    console.log('Laravel Echo initialized with Pusher', {
        key: import.meta.env.VITE_PUSHER_APP_KEY?.substring(0, 10) + '...',
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    });
} else {
    console.warn('VITE_PUSHER_APP_KEY is not set. Echo will not be initialized.');
    // Set Echo to a no-op object to prevent errors
    window.Echo = {
        private: () => ({ listen: () => { }, leave: () => { } }),
        channel: () => ({ listen: () => { }, leave: () => { } }),
    };
}


