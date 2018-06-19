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
          "aurelia-animator-css",
          "aurelia-bootstrapper",
          "aurelia-configuration",
          "aurelia-cookie",
          "aurelia-dialog",
          "aurelia-i18n",
          "aurelia-framework",
          "aurelia-history-browser",
          "aurelia-http-client",
          "aurelia-loader-default",
          "aurelia-logging-console",
          "aurelia-plugins-tabs",
          "aurelia-polyfills",
          "aurelia-router",
          "aurelia-templating-binding",
          "aurelia-templating-resources",
          "aurelia-templating-router",
          "aurelia-validation",
          "bootstrap",
          "change-case",
          "jquery",
          "lodash",
          "moment",
          "nprogress",
          "nprogress/nprogress.css!text",
          "oribella-aurelia-sortable",
          "sweetalert2",
          "sweetalert2/dist/sweetalert2.css!text",
          "text",
          "yamljs"
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
