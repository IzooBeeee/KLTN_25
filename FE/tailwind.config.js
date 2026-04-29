/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
    "./resources/js/**/*.{vue,js}", // Nếu dùng Laravel + Vite
  ],
  theme: {
    extend: {
      colors: {
        'luxury-black': '#0a0a0a',
        "primary-fixed-dim": "#bdc2ff",
        "outline-variant": "#c6c5d4",
        "surface-container-high": "#e8e8e8",
        "tertiary": "#686000",
        "secondary": "#006a62",
        "on-primary-container": "#8690ee",
        "background": "#f9f9f9",
        "surface-container": "#eeeeee",
        "surface-container-low": "#f3f3f3",
        "inverse-primary": "#bdc2ff",
        "on-primary": "#ffffff",
        "secondary-fixed-dim": "#66d9cc",
        "secondary-container": "#81f3e5",
        "surface-dim": "#dadada",
        "tertiary-fixed-dim": "#d6c953",
        "surface-tint": "#4c56af",
        "on-tertiary": "#ffffff",
        "surface": "#f9f9f9",
        "on-tertiary-fixed-variant": "#4e4800",
        "on-secondary": "#ffffff",
        "on-surface-variant": "#454652",
        "primary": "#000666",
        "on-primary-fixed-variant": "#343d96",
        "error": "#ba1a1a",
        "surface-container-highest": "#e2e2e2",
        "on-surface": "#1a1c1c",
        "primary-fixed": "#e0e0ff",
        "tertiary-fixed": "#f3e56c",
        "on-tertiary-fixed": "#1f1c00",
        "inverse-surface": "#2f3131",
        "tertiary-container": "#baad3a",
        "on-tertiary-container": "#464000",
        "error-container": "#ffdad6",
        "outline": "#767683",
        "on-secondary-fixed-variant": "#005049",
        "on-error-container": "#93000a",
        "surface-variant": "#e2e2e2",
        "on-error": "#ffffff",
        "surface-bright": "#f9f9f9",
        "on-primary-fixed": "#000767",
        "on-secondary-fixed": "#00201d",
        "surface-container-lowest": "#ffffff",
        "on-secondary-container": "#006f66",
        "secondary-fixed": "#84f5e8",
        "primary-container": "#1a237e",
        "on-background": "#1a1c1c",
        "inverse-on-surface": "#f1f1f1"
      },
      borderRadius: {
        "DEFAULT": "0.25rem",
        "lg": "0.5rem",
        "xl": "0.75rem",
        "full": "9999px"
      },
      fontFamily: {
        "headline": ["'Be Vietnam Pro'", "sans-serif"],
        "body": ["'Be Vietnam Pro'", "sans-serif"],
        "label": ["'Inter'", "sans-serif"]
      }
    }
  },
  plugins: [],
}
