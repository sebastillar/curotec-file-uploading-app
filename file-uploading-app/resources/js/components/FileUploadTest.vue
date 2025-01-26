<template>
    <div class="file-test">
        <h3>File Upload Component</h3>
        <p>Status: {{ status }}</p>
        <button @click="changeStatus" class="test-button">Test Button</button>
    </div>
</template>

<script setup>
import { ref } from "vue";

const status = ref("Ready");

const changeStatus = () => {
    status.value = "Button clicked at " + new Date().toLocaleTimeString();
};
</script>

<style scoped>
.file-test {
    border: 1px solid #ccc;
    padding: 20px;
    margin-top: 20px;
    text-align: center;
}

.test-button {
    padding: 10px 20px;
    background-color: #42b983;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-top: 10px;
}

.test-button:hover {
    background-color: #3aa876;
}
</style>
