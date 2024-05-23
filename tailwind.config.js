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
      animation: {
        fade: 'fadeIn 1s ease-in-out'
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: 0 },
          '100%': { opacity: 100 },
        },
      },
      backgroundImage: {
        'hero': "url('/public/hero_clipped.jpg')"
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

