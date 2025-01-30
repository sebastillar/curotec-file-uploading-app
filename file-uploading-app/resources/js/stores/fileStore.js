import { defineStore } from "pinia";
import axios from "../utils/axios";

export const useFileStore = defineStore("file", {
    state: () => ({
        files: [],
        selectedFile: null,
        loading: false,
        error: null,
    }),

    actions: {
        async fetchFiles() {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get("/api/files/latest");
                console.log("API Response:", response);
                this.files = response.data.data || [];
                return this.files;
            } catch (error) {
                console.error("Error fetching files:", error);
                this.error = "Failed to load files";
                this.files = [];
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async downloadFile(fileId) {
            try {
                const versionsResponse = await axios.get(
                    `/api/files/${fileId}/versions`
                );
                const versions = versionsResponse.data.data || [];

                if (!versions.length) {
                    throw new Error("No versions available for this file");
                }

                const latestVersion = versions[0];

                const response = await axios.get(
                    `/api/files/versions/${latestVersion.id}/download`,
                    {
                        responseType: "blob",
                    }
                );

                const contentDisposition =
                    response.headers["content-disposition"];
                let filename = "download.pdf";

                if (contentDisposition) {
                    const filenameMatch =
                        contentDisposition.match(/filename="(.+)"/);
                    if (filenameMatch) {
                        filename = filenameMatch[1];
                    }
                }

                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute("download", filename);
                document.body.appendChild(link);
                link.click();

                setTimeout(() => {
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(url);
                }, 100);
            } catch (error) {
                console.error("Download error:", error);
                throw error;
            }
        },

        async fetchFileWithVersions(fileId) {
            try {
                const response = await axios.get(
                    `/api/files/${fileId}/versions`
                );
                const fileData = response.data.data;

                const fileIndex = this.files.findIndex((f) => f.id === fileId);
                if (fileIndex !== -1) {
                    this.files[fileIndex] = {
                        ...this.files[fileIndex],
                        versions: fileData,
                    };
                    return this.files[fileIndex];
                }
                return null;
            } catch (error) {
                console.error("Error fetching versions:", error);
                throw error;
            }
        },

        async addComment({ file_version_id, content }) {
            try {
                const response = await axios.post(
                    `/api/files/versions/${file_version_id}/comments`,
                    { comment: content }
                );

                return response.data;
            } catch (error) {
                console.error("Error adding comment:", error);
                throw error;
            }
        },

        async uploadFile(file) {
            try {
                const formData = new FormData();
                formData.append("file", file);

                const response = await axios.post(
                    "/api/files/upload",
                    formData,
                    {
                        headers: {
                            "Content-Type": "multipart/form-data",
                        },
                        onUploadProgress: (progressEvent) => {
                            const percentCompleted = Math.round(
                                (progressEvent.loaded * 100) /
                                    progressEvent.total
                            );
                            console.log("Upload progress:", percentCompleted);
                        },
                    }
                );

                await this.fetchFiles();

                return response.data;
            } catch (error) {
                console.error("Upload error in store:", error);
                throw error;
            }
        },

        selectFile(file) {
            if (!file?.id) return;
            this.selectedFile = { ...file, versions: file.versions || [] };
        },

        bindFileUploadedEvent() {
            if (window.Echo) {
                window.Echo.channel("files").listen(
                    ".file.uploaded",
                    (event) => {
                        console.log("File uploaded event received:", event);
                        this.handleFileUploaded(event.file);
                    }
                );
            }
        },

        unbindFileUploadedEvent() {
            if (window.Echo) {
                window.Echo.leave("files");
            }
        },

        handleFileUploaded(file) {
            if (!file?.id) return;

            const existingIndex = this.files.findIndex((f) => f.id === file.id);

            if (existingIndex !== -1) {
                this.files[existingIndex] = file;
            } else {
                this.files.unshift(file);
            }
        },

        handleNewComment(comment) {
            if (!comment?.file_version_id || !this.selectedFile?.versions)
                return;

            const version = this.selectedFile.versions.find(
                (v) => v.id === comment.file_version_id
            );

            if (version) {
                if (!version.comments) {
                    version.comments = [];
                }
                version.comments.push(comment);
            }
        },

        reset() {
            this.files = [];
            this.selectedFile = null;
            this.loading = false;
            this.error = null;
        },

        async uploadVersion(fileId, file) {
            try {
                const formData = new FormData();
                formData.append("file", file);

                const response = await axios.post(
                    `/api/files/${fileId}/versions`,
                    formData,
                    {
                        headers: {
                            "Content-Type": "multipart/form-data",
                        },
                        onUploadProgress: (progressEvent) => {
                            const percentCompleted = Math.round(
                                (progressEvent.loaded * 100) /
                                    progressEvent.total
                            );
                            console.log(
                                "Version upload progress:",
                                percentCompleted
                            );
                        },
                    }
                );

                await this.fetchFiles();

                return response.data;
            } catch (error) {
                console.error("Version upload error in store:", error);
                throw error;
            }
        },

        async downloadVersion(versionId) {
            try {
                const response = await axios.get(
                    `/api/files/versions/${versionId}/download`,
                    {
                        responseType: "blob",
                    }
                );

                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute("download", "file-version.pdf");
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(url);
            } catch (error) {
                console.error("Download error:", error);
                throw error;
            }
        },
    },
});
