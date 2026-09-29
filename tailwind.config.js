import defaultTheme from "tailwindcss/defaultTheme";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
                cabinet: ["Cabinet Grotesk", "sans-serif"],
                plush: ["Plush Trial", "serif"],
            },
            colors: {
                neutral: {
                    dark: {
                        50: "#f6f6f6",
                        100: "#e7e7e7",
                        200: "#d1d1d1",
                        300: "#B0B0B0",
                        400: "#888888",
                        500: "#6D6D6D",
                        600: "#5d5d5d",
                        700: "#4f4f4f",
                        800: "#454545",
                        900: "#3d3d3d",
                        950: "#141414",
                    },
                },
                primary: {
                    dark: {
                        50: "#f9f7ed",
                        100: "#f1ebd0",
                        200: "#e4d7a4",
                        300: "#d4bc70",
                        400: "#c6a349",
                        500: "#b78f3b",
                        600: "#9d7231",
                        700: "#7E552A",
                        800: "#6a4629",
                        900: "#5b3c28",
                        950: "#482B1B",
                    },
                    light: {
                        50: "#F7F6EF",
                        100: "#EDE7D4",
                        200: "#DCCEAC",
                        300: "#C7AF7D",
                        500: "#A37E49",
                        600: "#9D7231",
                    },
                },
                secondary: {
                    terra: {
                        600: "#D85640",
                        700: "#B23823",
                        800: "#943120",
                    },
                    steel: {
                        50: "#f2f7fb",
                        100: "#e8f0f7",
                        200: "#d5e5f0",
                        300: "#bcd2e5",
                        400: "#a0b9d9",
                        500: "#8ca5ce",
                        600: "#6f86bc",
                        700: "#5e72a4",
                        800: "#4e5f85",
                        900: "#44516b",
                        950: "#272e3f",
                    },
                },
                ivory: {
                    700: "#BC7012",
                },
            },
        },
    },
    plugins: [],
};
