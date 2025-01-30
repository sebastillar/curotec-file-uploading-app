import { initializeServices } from "./bootstrap";
import { createApp } from "vue";
import { createPinia } from "pinia";
import App from "./App.vue";

// Initialize all services before creating Vue app
initializeServices()
    .then(() => {
        const app = createApp(App);
        app.use(createPinia());
        app.mount("#app");
    })
    .catch((error) => {
        console.error("Failed to initialize application:", error);
    });
