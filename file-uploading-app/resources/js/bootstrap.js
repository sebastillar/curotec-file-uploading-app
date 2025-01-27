import axios from "axios";
window.axios = axios;

axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;
axios.defaults.headers.common["X-CSRF-TOKEN"] = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute("content");
axios.defaults.timeout = 30000; // 30 seconds timeout

// Set the base URL to your ngrok URL
axios.defaults.baseURL = import.meta.env.VITE_APP_URL;

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "pusher",
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
});

// Enable Pusher logging
Pusher.logToConsole = true;

// Debug connection
window.Echo.connector.pusher.connection.bind("connected", () => {
    console.log("🔌 Connected to Pusher");
});

// Set up channel listener
window.Echo.channel("public-messages")
    .listen(".TestMessage", (data) => {
        console.log("🎯 Received broadcast:", data);
        const message = data.text || data.message || data;
        console.log("📨 Processing message:", message);
        window.dispatchEvent(
            new CustomEvent("pusher-message", {
                detail: message,
            })
        );
    })
    .subscribed(() => {
        console.log("✅ Subscribed to public-messages");
    });

// Debug all events
window.Echo.connector.pusher.bind_global((eventName, data) => {
    console.log("🌐 Global event:", { eventName, data });
});
