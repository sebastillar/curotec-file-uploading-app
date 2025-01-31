<template>
    <div class="file-list-container">
        <h2 class="section-title dark:text-white">File History</h2>
        <div v-if="isLoading" class="loading">Loading files...</div>
        <div v-else-if="error" class="error">
            {{ error }}
        </div>
        <div v-else-if="files.length === 0" class="no-files">
            No files uploaded yet
        </div>
        <div v-else class="files-grid">
            <FileCard
                v-for="file in files"
                :key="file.id"
                :file="file"
                @show-versions="$emit('show-versions', $event)"
            />
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted } from "vue";
import { useFileStore } from "../../stores/fileStore";
import FileCard from "./FileCard.vue";

const fileStore = useFileStore();
const error = computed(() => fileStore.error);
const files = computed(() => fileStore.files);
const isLoading = computed(() => fileStore.loading);

defineEmits(["show-versions"]);

onMounted(async () => {
    console.log("FileList mounted, fetching files...");
    try {
        await fileStore.fetchFiles();
        console.log("Files fetched:", fileStore.files);
    } catch (error) {
        console.error("Error fetching files on mount:", error);
    }
});
</script>

<style scoped>
.file-list-container {
    margin-top: 2rem;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 1rem;
}

.files-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.loading {
    text-align: center;
    padding: 2rem;
    color: #6b7280;
}

.error {
    text-align: center;
    padding: 2rem;
    color: #dc2626;
}

.no-files {
    text-align: center;
    padding: 2rem;
    color: #6b7280;
    font-style: italic;
}
</style>
