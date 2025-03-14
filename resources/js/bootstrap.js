window._ = require('lodash');
window.axios = require('axios');

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: true,
    encrypted: true
});

echo.private('chat.' + receiverId)
    .listen('MessageSent', (event) => {
        console.log('Received message:', event.message);
    });
