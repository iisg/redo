module.exports = {
  "bundles": {
    "admin/bundles/app": {
      "includes": [
        "[**/*.js]",
        "**/*.html!text",
        "**/*.css!text",
        "locales/**/*.json!text"
      ],
      "excludes": [
        "[resources-config/**]",
        "resources-config/**/*.html!text",
        "resources-config/**/*.css!text",
      ],
      "options": {
        "inject": true,
        "minify": true,
        "depCache": true,
        "rev": true
      }
    },
    "admin/bundles/resources-config": {
      "includes": [
        "[resources-config/**/*.js]",
        "resources-config/**/*.html!text",
        "resources-config/**/*.css!text"
      ],
      "options": {
        "inject": true,
        "minify": true,
        "depCache": true,
        "rev": true
      }
    },
    "admin/bundles/vendor": {
      "includes": [
        "arrive",
        "aurelia-configuration",
        "aurelia-cookie",
        "aurelia-framework",
        "aurelia-bootstrapper",
        "aurelia-http-client",
        "aurelia-router",
        "aurelia-animator-css",
        "aurelia-i18n",
        "aurelia-templating-binding",
        "aurelia-polyfills",
        "aurelia-templating-resources",
        "aurelia-templating-router",
        "aurelia-loader-default",
        "aurelia-history-browser",
        "aurelia-logging-console",
        "aurelia-validation",
        "bootstrap",
        "bootstrap-material-design",
        "bootstrap-material-design/dist/css/ripples.min.css!text",
        "bootstrap-select",
        "bootstrap-select/dist/css/bootstrap-select.min.css!text",
        "i18next-xhr-backend",
        "jquery",
        "text"
      ],
      "options": {
        "inject": true,
        "minify": true,
        "depCache": false,
        "rev": true
      }
    }
  }
};
