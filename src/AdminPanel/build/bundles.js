const bundles = function () {
  const dev = process.env.REPEKA_ENV !== 'prod';
  return {
    app: {
      "admin/bundles/app": {
        "includes": [
          "[**/*.js]",
          "**/*.css!text",
        ],
        "options": {
          "inject": true,
          "minify": !dev,
          "depCache": true,
          "rev": !dev
        }
      }
    },
    resources: {
      "admin/bundles/res": {
        "includes": [
          "res/**/*!text",
        ],
        "options": {
          "inject": true,
          "minify": true,
          "depCache": true,
          "rev": !dev
        }
      },
    },
    views: {
      "admin/bundles/views": {
        "includes": [
          "**/*.html!text",
        ],
        "options": {
          "inject": true,
          "minify": !dev,
          "depCache": true,
          "rev": !dev
        }
      }
    },
    vendors: {
      "admin/bundles/vendor-select2": {
        "includes": [
          "select2",
        ],
        "excludes": [
          "jquery"
        ],
        "options": {
          "inject": true,
          "minify": !dev,
          "depCache": true,
          "rev": !dev
        }
      },
      "admin/bundles/vendor": {
        "includes": [
          "arrive",
          "aurelia-configuration",
          "aurelia-cookie",
          "aurelia-dialog",
          "aurelia-framework",
          "aurelia-bootstrapper",
          "aurelia-http-client",
          "aurelia-router",
          "aurelia-animator-css",
          "aurelia-i18n",
          "aurelia-templating-binding",
          "aurelia-plugins-tabs",
          "aurelia-polyfills",
          "aurelia-templating-resources",
          "aurelia-templating-router",
          "aurelia-loader-default",
          "aurelia-history-browser",
          "aurelia-logging-console",
          "aurelia-validation",
          "bootstrap",
          "change-case",
          "handlebars",
          "jquery",
          "moment",
          "nprogress",
          "nprogress/nprogress.css!text",
          // "martingust/aurelia-repeat-strategies",  // causes "Unable to dynamically transpile ES module" error
          "oribella-aurelia-sortable",
          "sticky-table-headers",
          "sweetalert2",
          "sweetalert2/dist/sweetalert2.css!text",
          "text"
        ],
        "options": {
          "inject": true,
          "minify": !dev,
          "depCache": false,
          "rev": !dev
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
          "minify": !dev,
          "depCache": false,
          "rev": !dev
        }
      }
    }
  }
};

module.exports = bundles;
