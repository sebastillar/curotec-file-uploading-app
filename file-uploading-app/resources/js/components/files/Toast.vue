<template>
    <div v-if="show" :class="['toast-notification', `toast-${type}`]">
        <div class="toast-content">
            <i :class="icon"></i>
            <span>{{ message }}</span>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    show: Boolean,
    message: String,
    type: {
        type: String,
        default: "error",
    },
});

const icon = computed(() => {
    switch (props.type) {
        case "success":
            return "fas fa-check-circle";
        case "error":
            return "fas fa-exclamation-circle";
        default:
            return "fas fa-info-circle";
    }
});
</script>

<style scoped>
.toast-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 16px 24px;
    border-radius: 8px;
    z-index: 1000;
    animation: slideIn 0.3s ease-out;
    max-width: 400px;
}

.toast-error {
    background-color: #fee2e2;
    border: 1px solid #ef4444;
    color: #991b1b;
}

.toast-success {
    background-color: #dcfce7;
    border: 1px solid #22c55e;
    color: #166534;
}

.toast-content {
    display: flex;
    align-items: center;
    gap: 8px;
}

.toast-content i {
    font-size: 1.25rem;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>
