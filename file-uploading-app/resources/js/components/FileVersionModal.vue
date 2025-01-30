<template>
    <div class="modal-overlay" @click.self="$emit('close')">
        <div class="modal-content">
            <div class="modal-header">
                <h3>File Versions - {{ file?.name }}</h3>
                <button @click="$emit('close')" class="close-btn">×</button>
            </div>

            <!-- Add Version Upload Section -->
            <div class="version-upload">
                <div class="version-upload-header">
                    <h4>Upload New Version</h4>
                </div>
                <div class="version-upload-content">
                    <FileUploader
                        :parent-file-id="file.id"
                        @version-uploaded="handleVersionUploaded"
                    />
                </div>
            </div>

            <div class="version-list">
                <div
                    v-for="version in versions"
                    :key="`${version.id}-${version.comments?.length}`"
                    class="version-item"
                >
                    <div class="version-header">
                        <span class="version-number"
                            >Version {{ version.version_number || 1 }}</span
                        >
                        <span class="version-date">{{
                            formatDate(version.created_at)
                        }}</span>
                    </div>

                    <button
                        @click="downloadVersion(version)"
                        class="download-btn"
                    >
                        <i class="fas fa-download"></i> Download
                    </button>

                    <div class="comments-section">
                        <h4>Comments</h4>

                        <div
                            v-if="version.comments?.length"
                            class="comments-list"
                        >
                            <div
                                v-for="comment in version.comments"
                                :key="comment.id"
                                class="comment"
                            >
                                <p class="comment-text">
                                    {{ comment.content || comment.comment }}
                                </p>
                                <span class="comment-date">{{
                                    formatDate(comment.created_at)
                                }}</span>
                            </div>
                        </div>
                        <div v-else class="no-comments">No comments yet</div>

                        <div class="comment-form">
                            <textarea
                                v-model="commentText"
                                placeholder="Add a comment..."
                                class="comment-input"
                            ></textarea>
                            <button
                                @click="addComment(version)"
                                :disabled="!commentText.trim() || isSubmitting"
                                class="add-comment-btn"
                            >
                                {{ isSubmitting ? "Adding..." : "Add Comment" }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from "vue";
import { formatDistanceToNow } from "date-fns";
import { useFileStore } from "../stores/fileStore";
import FileUploader from "./FileUploader.vue";

const props = defineProps({
    file: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["close", "version-added"]);
const fileStore = useFileStore();
const commentText = ref("");
const isSubmitting = ref(false);
const localVersions = ref(props.file?.versions || []);

const versions = computed(() => localVersions.value);

watch(
    () => props.file?.versions,
    (newVersions) => {
        if (newVersions) {
            localVersions.value = newVersions;
        }
    },
    { deep: true }
);

onMounted(() => {
    window.Echo.channel("file-comments").listen(
        "NewVersionComment",
        async (e) => {
            console.log("New comment received:", e);
            if (props.file?.id) {
                const updatedFile = await fileStore.fetchFileWithVersions(
                    props.file.id
                );
                localVersions.value = updatedFile.versions;

                await nextTick(() => {
                    console.log("DOM updated with new comment");
                });
            }
        }
    );

    window.Echo.channel(`file-versions.${props.file.id}`).listen(
        "NewVersionUploaded",
        async (e) => {
            console.log("New version received:", e);
            if (props.file?.id) {
                const updatedFile = await fileStore.fetchFileWithVersions(
                    props.file.id
                );
                localVersions.value = updatedFile.versions;
            }
        }
    );
});

onUnmounted(() => {
    window.Echo.leave("file-comments");
    window.Echo.leave(`file-versions.${props.file.id}`);
});

const formatDate = (date) => {
    try {
        return formatDistanceToNow(new Date(date), { addSuffix: true });
    } catch (error) {
        return "Invalid date";
    }
};

const addComment = async (version) => {
    if (!commentText.value.trim() || isSubmitting.value) return;

    isSubmitting.value = true;
    try {
        // Add the comment
        await fileStore.addComment({
            file_version_id: version.id,
            content: commentText.value.trim(),
        });

        // Clear the input
        commentText.value = "";

        // Immediately fetch updated versions
        if (props.file?.id) {
            const updatedFile = await fileStore.fetchFileWithVersions(
                props.file.id
            );
            // Update the parent component
            emit("version-added", updatedFile);
        }
    } catch (error) {
        console.error("Add comment error:", error);
    } finally {
        isSubmitting.value = false;
    }
};

const downloadVersion = async (version) => {
    try {
        await fileStore.downloadVersion(version.id);
    } catch (error) {
        console.error("Download error:", error);
    }
};

const handleVersionUploaded = async () => {
    if (props.file?.id) {
        const updatedFile = await fileStore.fetchFileWithVersions(
            props.file.id
        );
        localVersions.value = updatedFile.versions;
        emit("version-added");
    }
};
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    max-height: 80vh;
    overflow-y: auto;
    padding: 1.5rem;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.modal-header h3 {
    margin: 0;
    color: #1f2937;
    font-size: 1.25rem;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #6b7280;
    cursor: pointer;
    padding: 0.5rem;
}

.version-item {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.version-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.version-number {
    font-weight: 500;
    color: #1f2937;
}

.version-date {
    color: #6b7280;
    font-size: 0.875rem;
}

.download-btn {
    background: #4f46e5;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.comments-section {
    border-top: 1px solid #e5e7eb;
    padding-top: 1rem;
    margin-top: 1rem;
}

.comments-section h4 {
    color: #1f2937;
    margin: 0 0 1rem 0;
}

.comment {
    background: #f3f4f6;
    border-radius: 4px;
    padding: 0.75rem;
    margin-bottom: 0.75rem;
}

.comment-text {
    margin: 0;
    color: #1f2937 !important;
    font-size: 0.875rem;
}

.comment-date {
    display: block;
    color: #6b7280;
    font-size: 0.75rem;
    margin-top: 0.25rem;
}

.no-comments {
    text-align: center;
    color: #6b7280;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 4px;
    font-style: italic;
}

.comment-form {
    margin-top: 1rem;
}

.comment-input {
    width: 100%;
    min-height: 80px;
    padding: 0.75rem;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    margin-bottom: 0.5rem;
    resize: vertical;
    color: #1f2937 !important;
    background: white;
}

.add-comment-btn {
    background: #4f46e5;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.875rem;
}

.add-comment-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.comments-list {
    margin-bottom: 1rem;
}

.version-upload {
    margin-bottom: 2rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.version-upload-header {
    margin-bottom: 1rem;
}

.version-upload-header h4 {
    margin: 0;
    color: #1f2937;
    font-size: 1rem;
}

.version-upload-content {
    background: white;
    border-radius: 6px;
}
</style>
