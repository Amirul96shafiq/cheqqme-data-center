import { defineConfig } from "vite";
import laravel, { refreshPaths } from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/css/filament/admin/theme.css",
                "resources/js/chatbot.js",
                "resources/js/app-custom.js",
                "resources/js/drag-drop-upload.js",
                "resources/js/document-upload-handler.js",
                "resources/css/emoji-picker-theme.css",
            ],
            refresh: true,
        }),
    ],
});
