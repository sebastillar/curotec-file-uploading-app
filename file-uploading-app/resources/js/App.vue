<template>
    <div class="app-container">
        <Header />

        <main class="app-content">
            <FileUploadTest />
        </main>

        <Footer />
    </div>
</template>

<script setup>
import Header from "./components/layouts/Header.vue";
import Footer from "./components/layouts/Footer.vue";
import FileUploadTest from "./components/FileUploadTest.vue";
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
