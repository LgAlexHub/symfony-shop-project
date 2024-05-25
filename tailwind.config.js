/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./assets/**/*.vue",
    "./templates/**/*.html.twig",
  ],
  theme: {
    container: {
      center: true,
      padding: '2rem',
    },
    extend: {
      colors: {
        transparent: 'transparent',
        current: 'currentColor',
        'white': '#ffffff',
        'brownmecha' : '#80504C',
        'purplemecha': {
          100: '#ccb7c0',
          200: '#b394a1',
          300: '#997081',
          400: '#804c62',
          500: '#804c62',
          600: '#663d4e',
          700: '#4d2e3b',
          800: '#402631',
          900: '#26171d',
        }
      },
      animation: {
        fade: 'fadeIn 1s ease-in-out'
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: 0 },
          '100%': { opacity: 100 },
        },
      },
    },
  },
  safelist: [
    {
      pattern: /bg-purple-+/,
    },
    {
      pattern: /from-purple-+/,
    },
  ],
  plugins: [],
}

