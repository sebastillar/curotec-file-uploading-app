<template>
    <div
        class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8"
    >
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div>
                <h2
                    class="mt-6 text-center text-3xl font-extrabold text-gray-900"
                >
                    Sign in to your account
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Or
                    <router-link
                        to="/register"
                        class="font-medium text-indigo-600 hover:text-indigo-500"
                    >
                        create a new account
                    </router-link>
                </p>
            </div>

            <!-- Form -->
            <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
                <div class="rounded-md shadow-sm -space-y-px">
                    <!-- Email -->
                    <div>
                        <label for="email-address" class="sr-only"
                            >Email address</label
                        >
                        <input
                            id="email-address"
                            v-model="form.email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                            placeholder="Email address"
                            :class="{ 'border-red-500': errors.email }"
                        />
                        <span
                            v-if="errors.email"
                            class="text-red-500 text-sm"
                            >{{ errors.email[0] }}</span
                        >
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <input
                            id="password"
                            v-model="form.password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                            placeholder="Password"
                            :class="{ 'border-red-500': errors.password }"
                        />
                        <span
                            v-if="errors.password"
                            class="text-red-500 text-sm"
                            >{{ errors.password[0] }}</span
                        >
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input
                            id="remember-me"
                            v-model="form.remember"
                            name="remember-me"
                            type="checkbox"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        />
                        <label
                            for="remember-me"
                            class="ml-2 block text-sm text-gray-900"
                        >
                            Remember me
                        </label>
                    </div>

                    <div class="text-sm">
                        <a
                            href="#"
                            class="font-medium text-indigo-600 hover:text-indigo-500"
                        >
                            Forgot your password?
                        </a>
                    </div>
                </div>

                <!-- Error Message -->
                <div v-if="error" class="text-red-500 text-sm text-center">
                    {{ error }}
                </div>

                <!-- Submit Button -->
                <div>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        :class="{ 'opacity-75 cursor-not-allowed': loading }"
                    >
                        <span
                            class="absolute left-0 inset-y-0 flex items-center pl-3"
                        >
                            <i
                                class="fas fa-lock text-indigo-500 group-hover:text-indigo-400"
                            ></i>
                        </span>
                        {{ loading ? "Signing in..." : "Sign in" }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../../stores/auth";

const router = useRouter();
const authStore = useAuthStore();

const loading = ref(false);
const error = ref("");
const errors = ref({});

const form = ref({
    email: "",
    password: "",
    remember: false,
});

const handleSubmit = async () => {
    loading.value = true;
    error.value = "";
    errors.value = {};

    try {
        await authStore.login({
            email: form.value.email,
            password: form.value.password,
            remember: form.value.remember,
        });

        router.push({ name: "home" });
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors;
        } else if (e.response?.status === 401) {
            error.value = "Invalid credentials";
        } else {
            error.value = "An error occurred. Please try again.";
        }
    } finally {
        loading.value = false;
    }
};
</script>
