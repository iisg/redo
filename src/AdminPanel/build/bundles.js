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
        "jquery",
        "oribella-aurelia-sortable",
        "select2",
        "sweetalert2",
        "sweetalert2/dist/sweetalert2.css!text",
        "text"
      ],
      "options": {
        "inject": true,
        "minify": true,
        "depCache": false,
        "rev": true
      }
    },
    "admin/bundles/cytoscape": {
      "includes": [
        "cytoscape",
        "cytoscape-autopan-on-drag",
        "cytoscape-context-menus",
        "cytoscape-context-menus/cytoscape-context-menus.css!text",
        "cytoscape-edgehandles"
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
