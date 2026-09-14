module.exports = {
  important: '#ghostlabs',
  content: [ "./dev/javascript/**/*.jsx" ],
  theme: {
    extend: {
      colors: {
        primary: {
          25: '#E2D8F3',
          50: '#BFA8DD',
          100: '#B499D7',
          200: '#9F7CCB',
          300: '#895FC0',
          400: '#7446B0',
          500: '#613A93',
          600: '#462A6B',
          700: '#2C1A43',
        },
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
  ],
}
