import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const pusherAppKey = import.meta.env.VITE_PUSHER_APP_KEY;
const pusherCluster = import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1';

// Only initialize Echo if the app key is available
if (pusherAppKey) {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: pusherAppKey,
        cluster: pusherCluster,
        forceTLS: true,
        encrypted: true,
    });
} else {
    console.warn('VITE_PUSHER_APP_KEY is not set. Echo will not be initialized.');
    // Set Echo to a no-op object to prevent errors
    window.Echo = {
        private: () => ({ listen: () => {}, leave: () => {} }),
        channel: () => ({ listen: () => {}, leave: () => {} }),
    };
}
