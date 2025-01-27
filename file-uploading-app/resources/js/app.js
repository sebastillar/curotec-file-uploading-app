import "./bootstrap";
import { createApp, h } from "vue";
import { createPinia } from "pinia";
import App from "./App.vue";
// Create and mount app
const app = createApp(App);
app.use(createPinia());
app.mount("#app");
