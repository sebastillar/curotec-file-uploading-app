<template>
    <div class="messages-container p-4">
        <!-- Status Messages -->
        <div class="status-container mb-4">
            <div v-if="error" class="error-message">
                <svg
                    class="w-5 h-5 inline mr-2"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>
                {{ error }}
            </div>

            <div v-if="success" class="success-message">
                <svg
                    class="w-5 h-5 inline mr-2"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 13l4 4L19 7"
                    />
                </svg>
                {{ success }}
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="buttons-container mb-4 flex gap-2">
            <button
                @click="sendTestMessage"
                class="primary-button"
                :disabled="isSending"
            >
                <span v-if="isSending" class="spinner"></span>
                {{ isSending ? "Sending..." : "Send Test Message" }}
            </button>

            <button
                @click="clearMessages"
                class="secondary-button"
                :disabled="messages.length === 0"
            >
                Clear Messages
            </button>
        </div>

        <!-- Messages List -->
        <div v-if="messages.length > 0" class="messages-list">
            <div
                v-for="message in messages"
                :key="message.id"
                class="message-item"
            >
                <div class="message-content">{{ message.message }}</div>
                <div class="message-time">{{ message.time }}</div>
            </div>
        </div>

        <div v-else class="empty-state">
            No messages yet. Send a test message to get started!
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import axios from "axios";

const messages = ref([]);
const isSending = ref(false);
const error = ref(null);
const success = ref(null);

// Create axios instance with base URL and correct configuration
const api = axios.create({
    baseURL: import.meta.env.VITE_APP_URL,
    withCredentials: true, // This is crucial
    headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
    },
});

// Add an interceptor to log requests
api.interceptors.request.use((request) => {
    console.log("🔄 Request:", request);
    return request;
});

// Add an interceptor to log responses
api.interceptors.response.use(
    (response) => {
        console.log("✅ Response:", response);
        return response;
    },
    (error) => {
        console.log("❌ Error:", error);
        return Promise.reject(error);
    }
);

// Get CSRF token on component mount
const getCsrfToken = async () => {
    try {
        console.log(
            "🔍 Starting CSRF token request to:",
            import.meta.env.VITE_APP_URL
        );

        const response = await api.get("/sanctum/csrf-cookie");
        console.log("📥 CSRF Response:", response);

        // Wait for cookie to be set
        await new Promise((resolve) => setTimeout(resolve, 1000));

        // Log all cookies
        console.log("🍪 Current cookies:", document.cookie);

        const token = document.cookie
            .split("; ")
            .find((row) => row.startsWith("XSRF-TOKEN="));

        if (!token) {
            console.error("❌ No XSRF-TOKEN cookie found");
            throw new Error("CSRF token not found");
        }

        const csrfToken = decodeURIComponent(token.split("=")[1]);
        console.log("✅ CSRF token found:", csrfToken);

        // Set the token for future requests
        api.defaults.headers.common["X-XSRF-TOKEN"] = csrfToken;

        return csrfToken;
    } catch (err) {
        console.error("❌ Error getting CSRF token:", {
            message: err.message,
            response: err.response?.data,
            status: err.response?.status,
            headers: err.response?.headers,
            url: err.config?.url,
        });
        throw new Error("Failed to initialize CSRF protection");
    }
};

onMounted(async () => {
    console.log("🔄 Messages component mounted");
    await getCsrfToken();
    subscribeToChannel();
});

onUnmounted(() => {
    unsubscribeFromChannel();
});

const subscribeToChannel = () => {
    window.Echo.channel("public-messages")
        .listen(".TestMessage", (event) => {
            console.log("📥 Received message:", event);
            addMessage(event.message);
        })
        .error((err) => {
            console.error("❌ Channel subscription error:", err);
            error.value = "Failed to connect to message channel";
        });
};

const unsubscribeFromChannel = () => {
    window.Echo.leaveChannel("public-messages");
};

const addMessage = (messageText) => {
    messages.value.unshift({
        id: Date.now(),
        message: messageText,
        time: new Date().toLocaleTimeString(),
    });
};

const sendTestMessage = async () => {
    isSending.value = true;
    error.value = null;
    success.value = null;

    try {
        // Get fresh CSRF token
        const token = await getCsrfToken();

        const response = await api.get("/test-message", {
            headers: {
                "X-CSRF-TOKEN": token,
            },
        });

        success.value = "Message sent successfully!";
        console.log("📤 Message sent:", response.data);
    } catch (err) {
        console.error("❌ Error sending message:", err);
        error.value =
            err.response?.data?.message ||
            err.message ||
            "Failed to send message. Please try again.";
    } finally {
        isSending.value = false;
    }
};

const clearMessages = () => {
    messages.value = [];
};
</script>

<style scoped>
.messages-container {
    max-width: 600px;
    margin: 0 auto;
}

.status-container > div {
    padding: 0.75rem;
    border-radius: 0.375rem;
    margin-bottom: 0.5rem;
}

.error-message {
    color: #dc2626;
    background-color: #fee2e2;
    border: 1px solid #fecaca;
}

.success-message {
    color: #16a34a;
    background-color: #dcfce7;
    border: 1px solid #bbf7d0;
}

.primary-button {
    background-color: #3b82f6;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
}

.primary-button:hover:not(:disabled) {
    background-color: #2563eb;
}

.primary-button:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.secondary-button {
    background-color: #e5e7eb;
    color: #374151;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.2s;
}

.secondary-button:hover:not(:disabled) {
    background-color: #d1d5db;
}

.secondary-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.messages-list {
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    overflow: hidden;
}

.message-item {
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    background-color: white;
}

.message-item:last-child {
    border-bottom: none;
}

.message-content {
    font-size: 0.875rem;
    color: #374151;
}

.message-time {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

.empty-state {
    text-align: center;
    color: #6b7280;
    padding: 2rem;
    background-color: #f9fafb;
    border-radius: 0.5rem;
    border: 1px dashed #e5e7eb;
}

.spinner {
    display: inline-block;
    width: 1rem;
    height: 1rem;
    border: 2px solid #ffffff;
    border-top-color: transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.buttons-container {
    display: flex;
    gap: 0.5rem;
}

.flex {
    display: flex;
}

.mb-4 {
    margin-bottom: 1rem;
}

.p-4 {
    padding: 1rem;
}
</style>
