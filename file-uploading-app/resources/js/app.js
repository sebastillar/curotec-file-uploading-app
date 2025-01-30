import "./bootstrap";
import { initializeServices } from "./bootstrap";
import { createApp } from "vue";
import { createPinia } from "pinia"; // This is the correct import
import router from "./router/index.js";
import App from "./App.vue";
import { useAuthStore } from "./stores/auth";
import FileList from "./components/files/FileList.vue"; // Import FileList component

const app = createApp(App);
const pinia = createPinia(); // Create pinia instance
app.use(pinia); // Use pinia plugin
app.use(router);

app.component("FileList", FileList); // Register FileList component

// Add navigation guard to router
router.beforeEach((to, from, next) => {
    const authStore = useAuthStore(pinia); // Pass pinia instance

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next({ name: "login" });
    } else if (to.meta.requiresGuest && authStore.isAuthenticated) {
        next({ name: "home" });
    } else {
        next();
    }
});

// Initialize all services and auth state before mounting
Promise.all([
    initializeServices(),
    useAuthStore(pinia).init(), // Pass pinia instance
])
    .then(() => {
        app.mount("#app");
    })
    .catch((error) => {
        console.error("Failed to initialize application:", error);
    });
