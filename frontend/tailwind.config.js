/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: ['./index.html', './src/**/*.{vue,js,ts,jsx,tsx}'],
  theme: {
    extend: {
      colors: {
        system: {
          primary: 'rgb(var(--pm-sys-primary) / <alpha-value>)',
          secondary: 'rgb(var(--pm-sys-secondary) / <alpha-value>)',
          tertiary: 'rgb(var(--pm-sys-tertiary) / <alpha-value>)',
          quaternary: 'rgb(var(--pm-sys-quaternary) / <alpha-value>)',
        },
        brand: {
          50: 'rgb(var(--pm-brand-50) / <alpha-value>)',
          100: 'rgb(var(--pm-brand-100) / <alpha-value>)',
          200: 'rgb(var(--pm-brand-200) / <alpha-value>)',
          300: 'rgb(var(--pm-brand-300) / <alpha-value>)',
          400: 'rgb(var(--pm-brand-400) / <alpha-value>)',
          500: 'rgb(var(--pm-brand-500) / <alpha-value>)',
          600: 'rgb(var(--pm-brand-600) / <alpha-value>)',
          700: 'rgb(var(--pm-brand-700) / <alpha-value>)',
          800: 'rgb(var(--pm-brand-800) / <alpha-value>)',
        },
        ink: 'rgb(var(--pm-text) / <alpha-value>)',
        muted: 'rgb(var(--pm-text-muted) / <alpha-value>)',
        border: 'rgb(var(--pm-border) / <alpha-value>)',
        surface: 'rgb(var(--pm-surface) / <alpha-value>)',
      },
    },
  },
  plugins: [],
}
