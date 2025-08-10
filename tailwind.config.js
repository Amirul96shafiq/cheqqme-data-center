import colors from "tailwindcss/colors";

export default {
    darkMode: "class",

    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./vendor/filament/**/*.blade.php",
        "./storage/framework/views/*.php",
    ],

    safelist: [
        // Ensure danger/red classes are always generated for dynamic markup
        "bg-danger-50",
        "bg-danger-100",
        "bg-danger-500",
        "bg-danger-600",
        "hover:bg-danger-500",
        "text-danger-600",
        "text-danger-500",
        "hover:text-danger-600",
        // Legacy direct red usage fallback
        "bg-red-50",
        "bg-red-100",
        "bg-red-500",
        "bg-red-600",
        "hover:bg-red-500",
        "text-red-600",
    ],

    theme: {
        extend: {
            colors: {
                primary: {
                    50: "#fff8eb",
                    100: "#fde7c3",
                    200: "#fcd39b",
                    300: "#fbbe72",
                    400: "#fab54f",
                    500: "#fbb43e", // main color
                    600: "#e6a135",
                    700: "#c5862c",
                    800: "#a56b23",
                    900: "#844f1a",
                },
                danger: colors.red, // Map danger palette to Tailwind red scale
            },
        },
    },

    plugins: [],
};
