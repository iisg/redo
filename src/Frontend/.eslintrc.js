module.exports = {
  "env": {
    "node": true,
    "browser": true,
    "es6": true
  },
  "extends": "eslint:recommended",
  "globals": {
    $: true
  },
  "parser": "babel-eslint",
  "parserOptions": {
    "sourceType": "module",
  },
  "plugins": [
    "html"
  ],
  "rules": {
    "indent": [
      "error",
      2
    ],
    "semi": [
      "error",
      "always"
    ]
  }
};
