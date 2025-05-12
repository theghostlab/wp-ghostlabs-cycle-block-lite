/** @type {import('tailwindcss').Config} */
module.exports = {
  important: '#ghostlabs',
  content: ["./javascript/**/*.jsx"],
  theme: {
    extend: {
      colors: {
        'accent-300': '#0f8dcb',
        'accent-600': '#0073aa',
        'accent-700': '#006799',
      },
      borderWidth: {
        3: '3px',
      },
      ringWidth: {
        3: '3px',
      },
    },
    variants: {
      extend: {
        backgroundColor: ['odd'],
      },
    },
  },
  plugins: [
    require('@tailwindcss/typography'),
    require('@headlessui/tailwindcss')({ prefix: 'ui' })
  ],
}

