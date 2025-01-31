import { defineStore } from "pinia";
import axios from "../utils/axios";

export const useAuthStore = defineStore("auth", {
    state: () => ({
        user: null,
        token: localStorage.getItem("token") || null,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        getUser: (state) => state.user,
    },

    actions: {
        setAuthHeader() {
            if (this.token) {
                axios.defaults.headers.common[
                    "Authorization"
                ] = `Bearer ${this.token}`;
            } else {
                delete axios.defaults.headers.common["Authorization"];
            }
        },

        async login(credentials) {
            try {
                const response = await axios.post("/api/login", credentials);
                this.token = response.data.token;
                localStorage.setItem("token", this.token);
                this.setAuthHeader();
                await this.fetchUser();
                return response;
            } catch (error) {
                throw error;
            }
        },

        async fetchUser() {
            const response = await axios.get("/user");
            this.user = response.data;
        },

        async logout() {
            try {
                if (this.token) {
                    await axios.post("/api/logout");
                }
            } catch (error) {
                console.error("Logout error:", error);
            } finally {
                this.token = null;
                this.user = null;
                localStorage.removeItem("token");
                this.setAuthHeader();
            }
        },

        async checkAuth() {
            if (!this.token) {
                return false;
            }

            try {
                this.setAuthHeader();
                const response = await axios.get("/api/user");
                this.user = response.data;
                return true;
            } catch (error) {
                this.token = null;
                this.user = null;
                localStorage.removeItem("token");
                this.setAuthHeader();
                return false;
            }
        },

        async init() {
            if (this.token) {
                return this.fetchUser();
            }
        },
    },
});
