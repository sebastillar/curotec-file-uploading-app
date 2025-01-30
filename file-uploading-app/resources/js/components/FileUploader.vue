<template>
    <div>
        <div
            class="uploader-zone"
            @drop.prevent="handleDrop"
            @dragover.prevent
            @click="$refs.fileInput.click()"
        >
            <input
                type="file"
                ref="fileInput"
                @change="handleFileSelect"
                class="hidden"
                accept=".pdf"
            />
            <div class="upload-content">
                <i class="fas fa-cloud-upload-alt"></i>
                <p>
                    {{
                        props.parentFileId
                            ? "Drop new version here or click to browse"
                            : "Drop files here or click to browse"
                    }}
                </p>
                <p class="text-sm">Supported files: PDF</p>
            </div>
        </div>

        <div v-if="uploadingFiles.length > 0" class="upload-progress">
            <div
                v-for="file in uploadingFiles"
                :key="file.name"
                class="progress-item"
            >
                <div class="progress-info">
                    <span class="file-name">{{ file.name }}</span>
                    <span class="progress-percentage"
                        >{{ file.progress }}%</span
                    >
                </div>
                <div class="progress-bar">
                    <div
                        class="progress-fill"
                        :style="{ width: file.progress + '%' }"
                    ></div>
                </div>
            </div>
        </div>
        <Toast
            :show="toastStore.show"
            :message="toastStore.message"
            :type="toastStore.type"
        />
    </div>
</template>

<script setup>
import { ref } from "vue";
import { useFileStore } from "../stores/fileStore";
import { useToastStore } from "../stores/toastStore";
import { fireConfetti } from "../utils/confetti";
import Toast from "./Toast.vue";

const props = defineProps({
    parentFileId: {
        type: Number,
        required: false,
        default: null,
    },
});

const emit = defineEmits(["file-uploaded", "version-uploaded"]);
const fileStore = useFileStore();
const toastStore = useToastStore();
const fileInput = ref(null);
const uploadingFiles = ref([]);

const validateFile = (file) => {
    // Check file type
    if (!file.type.includes("pdf")) {
        toastStore.showToast("Only PDF files are allowed", "error");
        return false;
    }

    // Check file size (e.g., 10MB limit)
    const maxSize = 10 * 1024 * 1024; // 10MB in bytes
    if (file.size > maxSize) {
        toastStore.showToast("File size must be less than 10MB", "error");
        return false;
    }

    return true;
};

const handleUpload = async (files) => {
    try {
        const file = files[0];
        console.log("Starting upload for file:", file);

        if (!validateFile(file)) {
            console.log("File validation failed");
            return;
        }

        if (props.parentFileId) {
            console.log("Uploading new version for file:", props.parentFileId);
            await fileStore.uploadVersion(props.parentFileId, file);
            emit("version-uploaded");
            toastStore.showToast("Version uploaded successfully", "success");
        } else {
            console.log("Uploading new file");
            await fileStore.uploadFile(file);
            emit("file-uploaded");
            toastStore.showToast("File uploaded successfully", "success");
            fireConfetti();
        }
    } catch (error) {
        console.error("Upload error in component:", error);
        console.error("Error details:", error.response?.data);
        toastStore.showToast(
            error.response?.data?.message || "Error uploading file",
            "error"
        );
    }
};

const handleDrop = (e) => {
    const files = e.dataTransfer.files;
    if (files.length) {
        handleUpload(files);
    }
};

const handleFileSelect = (e) => {
    const files = e.target.files;
    if (files.length) {
        handleUpload(files);
    }
};
</script>

<style scoped>
.uploader-zone {
    border: 2px dashed #e5e7eb;
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.uploader-zone:hover {
    border-color: #6366f1;
    background: #f9fafb;
}

.upload-content {
    color: #6b7280;
}

.upload-content i {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.hidden {
    display: none;
}

.text-sm {
    font-size: 0.875rem;
    color: #9ca3af;
}

.upload-progress {
    margin-top: 1rem;
}

.progress-item {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 1rem;
    margin-bottom: 0.5rem;
}

.progress-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.file-name {
    color: #1f2937;
    font-weight: 500;
}

.progress-percentage {
    color: #6b7280;
}

.progress-bar {
    background: #e5e7eb;
    border-radius: 9999px;
    height: 0.5rem;
    overflow: hidden;
}

.progress-fill {
    background: #4f46e5;
    height: 100%;
    transition: width 0.2s;
}
</style>
