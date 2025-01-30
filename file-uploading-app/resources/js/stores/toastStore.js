import { defineStore } from "pinia";

export const useToastStore = defineStore("toast", {
    state: () => ({
        message: "",
        type: "error",
        show: false,
        timeout: null,
    }),

    actions: {
        showToast(message, type = "error", duration = 3000) {
            if (this.timeout) {
                clearTimeout(this.timeout);
            }

            this.message = message;
            this.type = type;
            this.show = true;

            this.timeout = setTimeout(() => {
                this.show = false;
                this.message = "";
            }, duration);
        },

        hideToast() {
            this.show = false;
            this.message = "";
        },
    },
});
