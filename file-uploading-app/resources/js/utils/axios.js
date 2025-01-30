import axios from "axios";

// Create axios instance with default config
const axiosInstance = axios.create({
    baseURL: import.meta.env.VITE_APP_URL,
    timeout: 30000, // 30 seconds
    withCredentials: true,
    withXSRFToken: true,
    headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
    },
});

// Set CSRF token
const token = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute("content");
if (token) {
    axiosInstance.defaults.headers.common["X-CSRF-TOKEN"] = token;
} else {
    console.error("CSRF token not found");
}

// Debug interceptors
axiosInstance.interceptors.request.use(
    (request) => {
        console.log("Starting Request:", request.url);
        return request;
    },
    (error) => {
        console.error("Request Error:", error);
        return Promise.reject(error);
    }
);

axiosInstance.interceptors.response.use(
    (response) => {
        console.log("Response:", response);
        return response;
    },
    (error) => {
        console.error("Response Error:", error.response);
        return Promise.reject(error);
    }
);

// Make axios instance available globally
window.axios = axiosInstance;

export default axiosInstance;
