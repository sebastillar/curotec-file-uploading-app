<template>
    <div class="file-manager">
        <FileUploader @file-uploaded="handleFileUploaded" />
        <FileList @show-versions="showVersions" />

        <Teleport to="body">
            <FileVersionModal
                v-if="showModal"
                :file="selectedFile"
                @close="closeVersionModal"
                @version-added="handleVersionAdded"
            />
        </Teleport>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import FileList from "./FileList.vue";
import FileUploader from "./FileUploader.vue";
import FileVersionModal from "./FileVersionModal.vue";
import { useFileStore } from "../../stores/fileStore";
import { useToastStore } from "../../stores/toastStore";
import axios from "axios";

const fileStore = useFileStore();
const toastStore = useToastStore();
const selectedFile = ref(null);
const showModal = ref(false);

const fetchFiles = async () => {
    try {
        const response = await axios.get(
            `${import.meta.env.VITE_APP_URL}/api/files`
        );
        return response.data;
    } catch (error) {
        console.error("Error fetching files:", error);
        throw error;
    }
};

onMounted(async () => {
    try {
        await fetchFiles();

        if (window.Echo) {
            if (typeof window.Echo.channel === "function") {
                fileStore.bindFileUploadedEvent();
            } else {
                console.warn("Echo channel is not a function");
            }
        } else {
            console.warn("Echo is not initialized");
        }
    } catch (err) {
        console.error("Error loading initial files:", err);
    }
});

onUnmounted(() => {
    if (window.Echo) {
        fileStore.unbindFileUploadedEvent();
    }
});

const showVersions = async (file) => {
    console.log("Showing versions for file:", file);
    selectedFile.value = file;
    showModal.value = true;

    try {
        const fileWithVersions = await fileStore.fetchFileWithVersions(file.id);
        selectedFile.value = fileWithVersions;
    } catch (error) {
        console.error("Error loading versions:", error);
    }
};

const closeVersionModal = () => {
    showModal.value = false;
    selectedFile.value = null;
};

const handleVersionAdded = async () => {
    await fileStore.fetchFiles();
};

const handleFileUploaded = async () => {
    await fileStore.fetchFiles();
};
</script>
