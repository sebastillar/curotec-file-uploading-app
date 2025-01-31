import axios from "axios";
import Echo from "laravel-echo";
import Pusher from "pusher-js";
import { useAuthStore } from "./stores/auth";

// First, create the Echo class
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "pusher",
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
});

export const initializeServices = () => {
    // Initialize Axios
    axios.defaults.withCredentials = true;

    // Add response interceptor
    axios.interceptors.response.use(
        (response) => response,
        (error) => {
            if (error.response?.status === 401) {
                const authStore = useAuthStore();
                authStore.logout();
                window.location.href = "/login";
            }
            return Promise.reject(error);
        }
    );

    return Promise.resolve();
};

export default Echo;
