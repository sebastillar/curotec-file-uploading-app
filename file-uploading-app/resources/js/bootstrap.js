import axios from "./utils/axios";
import { initializeEcho } from "./utils/echo";
import { initializePusher } from "./utils/pusher";

// Initialize core services
initializePusher();

// Initialize Echo and assign to window
const echo = initializeEcho();
if (echo) {
    window.Echo = echo;
} else {
    console.warn("Echo initialization failed");
}

console.log("🚀 Application services initialized");
