/** @type {import('tailwindcss').Config} */
module.exports = {
  important: '#ghostlabs',
  // Every class this screen renders is in dev/javascript now, including the
  // presentational primitives in _components/ui.jsx — this edition inlined them
  // rather than importing the shared package, which is not publicly installable
  // and so cannot appear in sources published to WordPress.org.
  //
  // Tailwind only emits classes it can see, so a component whose markup lives
  // somewhere unscanned ships unstyled. That is what the removed entry existed
  // to prevent when the markup was inside the package's dist.
  content: [ "./dev/javascript/**/*.jsx" ],
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
  ],
}
