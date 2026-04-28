/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: ['./index.html', './src/**/*.{vue,js,ts,jsx,tsx}'],
  theme: {
    extend: {
      /* UI/UX Design Policy Token Extensions - Version 2.0 */
      
      /* Color System */
      colors: {
        /* System Colors */
        system: {
          primary: 'rgb(var(--pm-sys-primary) / <alpha-value>)',
          secondary: 'rgb(var(--pm-sys-secondary) / <alpha-value>)',
          tertiary: 'rgb(var(--pm-sys-tertiary) / <alpha-value>)',
          quaternary: 'rgb(var(--pm-sys-quaternary) / <alpha-value>)',
        },
        /* Brand Palette */
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
        /* Text Colors */
        ink: 'rgb(var(--pm-text) / <alpha-value>)',
        muted: 'rgb(var(--pm-text-muted) / <alpha-value>)',
        soft: 'rgb(var(--pm-text-soft) / <alpha-value>)',
        /* Surface & Border */
        surface: 'rgb(var(--pm-surface) / <alpha-value>)',
        surfaceSoft: 'rgb(var(--pm-surface-soft) / <alpha-value>)',
        surfaceStrong: 'rgb(var(--pm-surface-strong) / <alpha-value>)',
        border: 'rgb(var(--pm-border) / <alpha-value>)',
        borderStrong: 'rgb(var(--pm-border-strong) / <alpha-value>)',
        /* Semantic - Success (Green) */
        success: {
          50: 'rgb(var(--pm-success-50) / <alpha-value>)',
          100: 'rgb(var(--pm-success-100) / <alpha-value>)',
          200: 'rgb(var(--pm-success-200) / <alpha-value>)',
          500: 'rgb(var(--pm-success-500) / <alpha-value>)',
          600: 'rgb(var(--pm-success-600) / <alpha-value>)',
          700: 'rgb(var(--pm-success-700) / <alpha-value>)',
        },
        /* Semantic - Warning (Amber/Orange) */
        warning: {
          50: 'rgb(var(--pm-warning-50) / <alpha-value>)',
          100: 'rgb(var(--pm-warning-100) / <alpha-value>)',
          200: 'rgb(var(--pm-warning-200) / <alpha-value>)',
          500: 'rgb(var(--pm-warning-500) / <alpha-value>)',
          600: 'rgb(var(--pm-warning-600) / <alpha-value>)',
          700: 'rgb(var(--pm-warning-700) / <alpha-value>)',
        },
        /* Semantic - Danger (Red) */
        danger: {
          50: 'rgb(var(--pm-danger-50) / <alpha-value>)',
          100: 'rgb(var(--pm-danger-100) / <alpha-value>)',
          200: 'rgb(var(--pm-danger-200) / <alpha-value>)',
          500: 'rgb(var(--pm-danger-500) / <alpha-value>)',
          600: 'rgb(var(--pm-danger-600) / <alpha-value>)',
          700: 'rgb(var(--pm-danger-700) / <alpha-value>)',
        },
      },
      
      /* Shadow System - Policy Compliant */
      boxShadow: {
        'xs': 'var(--shadow-xs)',
        'sm': 'var(--shadow-sm)',
        'md': 'var(--shadow-md)',
        'lg': 'var(--shadow-lg)',
      },
      
      /* Border Radius - Policy Compliant Scale */
      borderRadius: {
        'xs': 'var(--radius-xs)',  // 4px - small badges
        'sm': 'var(--radius-sm)',  // 6px - buttons, inputs
        'md': 'var(--radius-md)',  // 10px - cards
        'lg': 'var(--radius-lg)',  // 14px - large cards
        'xl': 'var(--radius-xl)',  // 18px - modals
        'full': 'var(--radius-full)', // pills, status badges
      },
      
      /* Transition Timing - Policy Compliant */
      transitionDuration: {
        'fast': '100ms',
        'base': '180ms',
        'slow': '320ms',
      },
      
      /* Transition Timing Function */
      transitionTimingFunction: {
        'ease': 'ease',
        'ease-in': 'ease-in',
        'ease-out': 'ease-out',
        'ease-in-out': 'ease-in-out',
      },
      
      /* Typography Scale - Policy Compliant */
      fontSize: {
        'xs': ['var(--text-xs)', { lineHeight: '1.4' }],   // 11px
        'sm': ['var(--text-sm)', { lineHeight: '1.5' }],   // 13px
        'base': ['var(--text-base)', { lineHeight: '1.5' }], // 15px
        'md': ['var(--text-md)', { lineHeight: '1.5' }],   // 17px
        'lg': ['var(--text-lg)', { lineHeight: '1.4' }],   // 20px
        'xl': ['var(--text-xl)', { lineHeight: '1.3' }],   // 24px
        '2xl': ['var(--text-2xl)', { lineHeight: '1.2' }], // 30px
        '3xl': ['var(--text-3xl)', { lineHeight: '1.1' }], // 40px
      },
      
      /* Font Family - Utility-first geometric sans */
      fontFamily: {
        'sans': ['"Inter"', '"Plus Jakarta Sans"', '"DM Sans"', '"Segoe UI"', 'system-ui', 'sans-serif'],
      },
      
      /* Custom Animation Durations */
      animation: {
        'fade-in': 'fadeIn var(--transition-base) ease-out',
        'slide-up': 'slideUp var(--transition-base) ease-out',
        'scale-in': 'scaleIn var(--transition-base) ease-out',
      },
      
      /* Keyframes for animations */
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideUp: {
          '0%': { opacity: '0', transform: 'translateY(8px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        scaleIn: {
          '0%': { opacity: '0', transform: 'scale(0.95)' },
          '100%': { opacity: '1', transform: 'scale(1)' },
        },
      },
    },
  },
  plugins: [],
}
