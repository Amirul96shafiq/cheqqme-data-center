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
    theme: {
        extend: {
            colors: {
                CheQQme_yellow: {
                    DEFAULT: "#fbb43e",
                    50: "#fbb43e",
                    100: "#fbb43e",
                    200: "#fbb43e",
                    300: "#fbb43e",
                    400: "#fbb43e",
                    500: "#fbb43e",
                    600: "#fbb43e",
                    700: "#fbb43e",
                    800: "#fbb43e",
                    900: "#fbb43e",
                },
            },
        },
    },
    plugins: [],
};
