<script>
import { ref } from "vue";
import { useFileStore } from "../stores/fileStore";

export default {
    setup() {
        const fileStore = useFileStore();
        const fileInput = ref(null);
        const uploadingFiles = ref([]);

        const handleFileSelect = async (event) => {
            const files = event.target.files;
            if (!files.length) return;

            uploadingFiles.value = [...files];

            try {
                for (const file of files) {
                    console.log("📤 Starting upload:", file.name);
                    await fileStore.uploadFile(file);
                }

                if (fileInput.value) {
                    fileInput.value.value = "";
                }
                uploadingFiles.value = [];
            } catch (error) {
                console.error("Upload failed:", error);
            }
        };

        return {
            fileInput,
            uploadingFiles,
            handleFileSelect,
            getUploadProgress: fileStore.getUploadProgress,
        };
    },
};
</script>

<template>
    <div class="file-upload">
        <input
            ref="fileInput"
            type="file"
            @change="handleFileSelect"
            multiple
            class="hidden"
        />

        <button
            @click="fileInput.click()"
            class="upload-button"
            :disabled="uploadingFiles.length > 0"
        >
            {{ uploadingFiles.length ? "Uploading..." : "Upload Files" }}
        </button>

        <div v-if="uploadingFiles.length" class="upload-progress">
            <div
                v-for="file in uploadingFiles"
                :key="file.name"
                class="progress-item"
            >
                <div class="file-info">
                    <span class="file-name">{{ file.name }}</span>
                    <span class="progress-text">
                        {{ getUploadProgress(file.name) }}%
                    </span>
                </div>
                <div class="progress-bar">
                    <div
                        class="progress-fill"
                        :style="{ width: `${getUploadProgress(file.name)}%` }"
                    ></div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.file-upload {
    padding: 1rem;
}

.upload-button {
    padding: 0.5rem 1rem;
    background-color: #4f46e5;
    color: white;
    border-radius: 0.375rem;
    font-weight: 500;
}

.upload-button:disabled {
    background-color: #6b7280;
    cursor: not-allowed;
}

.upload-progress {
    margin-top: 1rem;
    space-y-2;
}

.progress-item {
    margin-bottom: 0.5rem;
}

.file-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.25rem;
}

.file-name {
    font-size: 0.875rem;
    color: #374151;
}

.progress-text {
    font-size: 0.875rem;
    color: #6b7280;
}

.progress-bar {
    width: 100%;
    height: 0.5rem;
    background-color: #e5e7eb;
    border-radius: 0.25rem;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background-color: #4f46e5;
    transition: width 0.2s ease-in-out;
}
</style>
