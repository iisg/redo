module.exports = {
  "bundles": {
    "admin/bundles/app": {
      "includes": [
        "[**/*.js]",
        "**/*.html!text",
        "**/*.css!text",
      ],
      "excludes": [
        "common/dto/**",
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
    "admin/bundles/res": {
      "includes": [
        "[res/**/*!text]",
      ],
      "options": {
        "inject": true,
        "minify": true,
        "depCache": true,
        "rev": true
      }
    },
    "admin/bundles/dto": {
      "includes": [
        "[common/dto/**/*.js]",
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
    "admin/bundles/vendor-select2": {
      "includes": [
        "select2",
      ],
      "options": {
        "inject": true,
        "minify": true,
        "depCache": false,
        "rev": true
      }
    }, "admin/bundles/vendor": {
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
        "change-case",
        "jquery",
        "oribella-aurelia-sortable",
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
