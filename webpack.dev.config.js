const path = require('path-browserify');

module.exports = {
  entry: {
    settings: './javascript/settings/settings.jsx'
  },
  output: {
    filename: '[name]-bundle.jsx',
    path: path.resolve(__dirname, 'js'),
  },
  resolve: {
    extensions: ['.js', '.jsx'],
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env', '@babel/preset-react'],
          },
        },
      },
    ],
  },
};
