<template>
    <header class="bg-gray-800 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo/Brand -->
                <div class="flex-shrink-0">
                    <router-link to="/" class="text-xl font-bold">
                        File Upload Platform
                    </router-link>
                </div>

                <!-- Navigation -->
                <nav class="flex items-center space-x-4">
                    <!-- Show these items when user is authenticated -->
                    <template v-if="authStore.isAuthenticated">
                        <span class="text-gray-300">
                            Welcome, {{ authStore.user?.name || "User" }}
                        </span>
                        <button
                            @click="handleLogout"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            :disabled="loading"
                        >
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            {{ loading ? "Logging out..." : "Logout" }}
                        </button>
                    </template>

                    <!-- Show these items when user is not authenticated -->
                    <template v-else>
                        <router-link
                            to="/login"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Login
                        </router-link>
                        <router-link
                            to="/register"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                        >
                            <i class="fas fa-user-plus mr-2"></i>
                            Register
                        </router-link>
                    </template>
                </nav>
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../../stores/auth";

const router = useRouter();
const authStore = useAuthStore();
const loading = ref(false);

const handleLogout = async () => {
    try {
        loading.value = true;
        await authStore.logout();
        router.push("/login");
    } catch (error) {
        console.error("Logout error:", error);
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
/* Add any additional styling here */
.router-link-active {
    @apply bg-opacity-80;
}
</style>
