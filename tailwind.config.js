import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import wireuiConfig from "./vendor/wireui/wireui/tailwind.config.js";
import powerGridConfig from "./vendor/power-components/livewire-powergrid/tailwind.config.js";

/** @type {import('tailwindcss').Config} */
export default {
    presets: [wireuiConfig, powerGridConfig],
    darkMode: "class",
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./vendor/wireui/wireui/src/*.php",
        "./vendor/wireui/wireui/ts/**/*.ts",
        "./vendor/wireui/wireui/src/WireUi/**/*.php",
        "./vendor/wireui/wireui/src/Components/**/*.php",
        "./app/Livewire/**/*Table.php",
        "./vendor/power-components/livewire-powergrid/resources/views/**/*.php",
        "./vendor/power-components/livewire-powergrid/src/Themes/Tailwind.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
        },
        colors: {
            "pg-primary": colors.gray,
        },
    },

    plugins: [forms],
};
