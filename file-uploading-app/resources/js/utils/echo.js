import Echo from "laravel-echo";
import { initializePusher } from "./pusher";

let echoInstance = null;

export function initializeEcho() {
    if (echoInstance) return echoInstance;

    echoInstance = new Echo({
        broadcaster: "pusher",
        key: import.meta.env.VITE_PUSHER_APP_KEY,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
        wsHost:
            import.meta.env.VITE_PUSHER_HOST ??
            `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
        wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
        wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? "https") === "https",
        enabledTransports: ["ws", "wss"],
        // Add these options for authentication
        authEndpoint: "/broadcasting/auth",
        auth: {
            headers: {
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content"),
                Accept: "application/json",
            },
        },
    });

    return echoInstance;
}
