import axios from "./utils/axios";
import { initializeEcho } from "./utils/echo";
import { initializePusher } from "./utils/pusher";

export async function initializeServices() {
    try {
        // Initialize services in sequence
        await initializePusher();
        const echo = await initializeEcho();

        if (echo) {
            window.Echo = echo;
        } else {
            console.warn("Echo initialization failed");
        }

        console.log("🚀 Application services initialized");
    } catch (error) {
        console.error("Service initialization failed:", error);
        throw error;
    }
}
