<template>
    <footer class="app-footer">
        <p>© {{ currentYear }} File Upload Platform</p>
    </footer>
</template>

<script setup>
import { ref, onMounted } from "vue";

const currentYear = ref(new Date().getFullYear());
</script>

<style scoped>
.app-footer {
    background-color: #2c3e50;
    color: white;
    text-align: center;
    padding: 1rem;
    margin-top: auto;
}

p {
    margin: 0;
    font-size: 0.9rem;
}
</style>
