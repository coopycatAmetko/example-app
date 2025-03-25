import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common = {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json'
};

window.appConfig = {
    apiUrl: '/api'
};

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: '7c0809d6fcfd2fcf9ad0',
    cluster: 'eu', 
    forceTLS: false,
    encrypted: false,
    disableStats: true, 
    enabledTransports: ['ws'] 
});

window.Echo.channel('posts.1').listen('.new-comment', (e) => {
    console.log(1);
});