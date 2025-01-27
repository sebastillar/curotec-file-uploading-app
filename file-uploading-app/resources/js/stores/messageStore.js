import { defineStore } from "pinia";

console.log("🏪 MessageStore - Defining store");

export const useMessageStore = defineStore("messages", {
    state: () => {
        console.log("🏪 MessageStore - Initializing state");
        return {
            messages: [],
        };
    },
    actions: {
        addMessage(message) {
            console.log("🏪 Store before:", this.messages);
            this.messages = [...this.messages, message];
            console.log("🏪 Store after:", this.messages);
        },
    },
    getters: {
        getMessages: (state) => {
            console.log(
                "🏪 Store - Getter called, current messages:",
                state.messages
            );
            return state.messages;
        },
    },
});

// Verify store is exported
console.log("🏪 MessageStore - Store exported:", useMessageStore);
