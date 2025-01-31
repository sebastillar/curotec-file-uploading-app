<template>
    <!-- Auth Layout -->
    <div v-if="isAuthRoute" class="min-h-screen bg-gray-50">
        <router-view></router-view>
    </div>

    <!-- Main App Layout -->
    <div v-else-if="authStore.isAuthenticated" class="app-container">
        <Header />
        <main class="app-content">
            <router-view></router-view>
        </main>
        <Footer />
    </div>

    <!-- Loading State -->
    <div v-else class="min-h-screen flex items-center justify-center">
        <span class="text-gray-500">Loading...</span>
    </div>
</template>

<script setup>
import { computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "./stores/auth";
import Header from "./components/layouts/Header.vue";
import Footer from "./components/layouts/Footer.vue";

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

// Check if current route is an auth route
const isAuthRoute = computed(() => {
    const authRoutes = ["login", "register"];
    return authRoutes.includes(route.name);
});

// Check authentication status when app loads
onMounted(async () => {
    if (!isAuthRoute.value) {
        const isAuthenticated = await authStore.checkAuth();
        if (!isAuthenticated) {
            router.push({ name: "login" });
        }
    }
});
</script>

<style scoped>
.app-container {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.app-content {
    flex: 1;
    padding: 2rem;
    background-color: #f5f5f5;
}
</style>
