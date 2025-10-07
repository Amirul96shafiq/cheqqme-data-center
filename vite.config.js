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
                "resources/css/task-comments.css",
                "resources/js/presence-status.js",
                "resources/js/kanban-alpine.js",
                "resources/css/kanban-drag-drop.css",
                "resources/js/spotify-player.js",
            ],
            refresh: true,
        }),
    ],
    server: {
        host: "localhost",
        port: 5173,
        cors: true,
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: (id) => {
                    if (id.includes("node_modules")) {
                        if (
                            id.includes("axios") ||
                            id.includes("laravel-echo")
                        ) {
                            return "vendor";
                        }
                        if (id.includes("emoji-picker-element")) {
                            return "ui";
                        }
                        return "vendor";
                    }
                },
                assetFileNames: (assetInfo) => {
                    const info = assetInfo.name.split(".");
                    const ext = info[info.length - 1];
                    if (/\.(css)$/.test(assetInfo.name)) {
                        return `css/[name]-[hash][extname]`;
                    }
                    return `assets/[name]-[hash][extname]`;
                },
                chunkFileNames: "js/[name]-[hash].js",
                entryFileNames: "js/[name]-[hash].js",
            },
        },
        cssCodeSplit: true,
        sourcemap: false,
        minify: "esbuild",
    },
    optimizeDeps: {
        include: ["axios", "laravel-echo", "emoji-picker-element"],
    },
});
