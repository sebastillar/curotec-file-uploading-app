import { createRouter, createWebHistory } from "vue-router";
import LoginForm from "../components/auth/LoginForm.vue";
import RegisterForm from "../components/auth/RegisterForm.vue";
import FileManager from "../components/files/FileManager.vue";

const routes = [
    {
        path: "/",
        name: "home",
        component: FileManager,
        meta: { requiresAuth: true },
    },
    {
        path: "/login",
        name: "login",
        component: LoginForm,
        meta: { requiresGuest: true },
    },
    {
        path: "/register",
        name: "register",
        component: RegisterForm,
        meta: { requiresGuest: true },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
