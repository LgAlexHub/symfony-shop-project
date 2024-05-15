/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./assets/**/*.vue",
    "./templates/**/*.html.twig",
  ],
  theme: {
    container : {
      center : true,
      padding: '2rem',
    },
    extend: {
      backgroundImage:{
        'hero' : "url('/public/hero_clipped.jpg')"
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

