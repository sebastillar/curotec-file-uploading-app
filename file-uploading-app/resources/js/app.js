import "./bootstrap";
import { createApp } from "vue";
import App from "./App.vue";

console.log("Starting Vue initialization...");

const app = createApp(App);

app.config.errorHandler = (err, vm, info) => {
    console.error("Vue Error:", err);
    console.error("Component:", vm);
    console.error("Info:", info);
};

app.mount("#app");

console.log("Vue app mounted");
