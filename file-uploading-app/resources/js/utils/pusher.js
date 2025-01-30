import Pusher from "pusher-js";

// Enable Pusher logging
Pusher.logToConsole = true;

window.Pusher = Pusher;

export function initializePusher() {
    const pusher = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
        wsHost:
            import.meta.env.VITE_PUSHER_HOST ??
            `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
        wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
        wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? "https") === "https",
        enabledTransports: ["ws", "wss"],
    });

    // Debug connection
    pusher.connection.bind("connected", () => {
        console.log("🔌 WebSocket Connected!");
    });

    pusher.connection.bind("error", (error) => {
        console.error("🔌 WebSocket Error:", error);
    });

    return pusher;
}
