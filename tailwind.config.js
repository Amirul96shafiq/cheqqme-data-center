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
            },
        },
    },

    plugins: [],
};
