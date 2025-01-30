<template>
    <div class="file-card dark:bg-gray-800 dark:border-gray-700">
        <div class="file-content">
            <div class="file-icon">
                <i class="fas fa-file-pdf"></i>
            </div>
            <div class="file-info">
                <h3 class="file-name dark:text-white">
                    {{ file.name || "Unnamed File" }}
                </h3>
                <div class="file-meta">
                    <span>{{ formatFileSize(file.size) }}</span>
                    <span class="date">{{ formatDate(file.created_at) }}</span>
                </div>
                <div class="version-badge">
                    {{ file.versions_count || 0 }} version{{
                        file.versions_count !== 1 ? "s" : ""
                    }}
                </div>
            </div>
        </div>
        <div class="file-actions">
            <button
                @click="downloadFile"
                class="action-btn"
                title="Download file"
            >
                <i class="fas fa-download"></i>
            </button>
            <button
                @click="$emit('show-versions', file)"
                class="action-btn"
                title="Show versions"
            >
                <i class="fas fa-history"></i>
            </button>
        </div>
    </div>
</template>

<script setup>
import { formatDistanceToNow } from "date-fns";
import { useFileStore } from "../stores/fileStore";

const props = defineProps({
    file: {
        type: Object,
        required: true,
    },
});

defineEmits(["show-versions"]);

const fileStore = useFileStore();

const formatDate = (date) => {
    try {
        return formatDistanceToNow(new Date(date), { addSuffix: true });
    } catch (error) {
        console.error("Date formatting error:", error);
        return "Invalid date";
    }
};

const formatFileSize = (bytes) => {
    if (!bytes) return "0 B";
    const k = 1024;
    const sizes = ["B", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(2))} ${sizes[i]}`;
};

const downloadFile = async () => {
    try {
        await fileStore.downloadFile(props.file.id);
    } catch (error) {
        console.error("Download error:", error);
    }
};
</script>

<style scoped>
.file-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.2s;
}

.file-card:hover {
    border-color: #4f46e5;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.file-content {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex: 1;
}

.file-icon {
    font-size: 1.5rem;
    color: #4f46e5;
}

.file-info {
    flex: 1;
}

.file-name {
    margin: 0;
    font-size: 1rem;
    font-weight: 500;
    color: #1f2937;
}

.file-meta {
    display: flex;
    gap: 1rem;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.version-badge {
    display: inline-block;
    background: #e0e7ff;
    color: #4f46e5;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    margin-top: 0.5rem;
}

.file-actions {
    display: flex;
    gap: 0.5rem;
}

.action-btn {
    background: none;
    border: none;
    color: #6b7280;
    padding: 0.5rem;
    cursor: pointer;
    transition: color 0.2s;
    border-radius: 4px;
}

.action-btn:hover {
    color: #4f46e5;
    background: #e0e7ff;
}
</style>
