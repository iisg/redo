System.config({
  baseURL: "/",
  defaultJSExtensions: true,
  transpiler: false,
  paths: {
    "*": "admin/dist/*",
    "admin/bundles/*": "*",
    "github:*": "jspm_packages/github/*",
    "npm:*": "jspm_packages/npm/*"
  },

  map: {
    "arrive": "npm:arrive@2.3.1",
    "aurelia-animator-css": "npm:aurelia-animator-css@1.0.1",
    "aurelia-binding": "npm:aurelia-binding@1.1.1",
    "aurelia-bootstrapper": "npm:aurelia-bootstrapper@1.0.1",
    "aurelia-configuration": "github:vheissu/aurelia-configuration@1.0.4",
    "aurelia-cookie": "npm:aurelia-cookie@1.0.10",
    "aurelia-dependency-injection": "npm:aurelia-dependency-injection@1.2.1",
    "aurelia-event-aggregator": "npm:aurelia-event-aggregator@1.0.1",
    "aurelia-framework": "npm:aurelia-framework@1.0.8",
    "aurelia-history-browser": "npm:aurelia-history-browser@1.0.0",
    "aurelia-http-client": "npm:aurelia-http-client@1.0.3",
    "aurelia-i18n": "npm:aurelia-i18n@1.2.3",
    "aurelia-loader-default": "npm:aurelia-loader-default@1.0.0",
    "aurelia-logging": "npm:aurelia-logging@1.3.0",
    "aurelia-logging-console": "npm:aurelia-logging-console@1.0.0",
    "aurelia-pal-browser": "npm:aurelia-pal-browser@1.1.0",
    "aurelia-polyfills": "npm:aurelia-polyfills@1.2.0",
    "aurelia-router": "npm:aurelia-router@1.1.0",
    "aurelia-templating": "npm:aurelia-templating@1.1.4",
    "aurelia-templating-binding": "npm:aurelia-templating-binding@1.1.0",
    "aurelia-templating-resources": "npm:aurelia-templating-resources@1.2.0",
    "aurelia-templating-router": "npm:aurelia-templating-router@1.0.1",
    "aurelia-testing": "npm:aurelia-testing@1.0.0-beta.2.0.1",
    "aurelia-validation": "npm:aurelia-validation@1.0.0-beta.1.0.1",
    "bluebird": "npm:bluebird@3.4.1",
    "bootstrap": "github:twbs/bootstrap@3.3.7",
    "bootstrap-material-design": "npm:bootstrap-material-design@0.5.10",
    "bootstrap-sass": "github:twbs/bootstrap-sass@3.3.7",
    "bootstrap-select": "npm:bootstrap-select@1.12.1",
    "compass-mixins": "npm:compass-mixins@0.12.10",
    "cytoscape": "npm:cytoscape@2.7.14",
    "cytoscape-autopan-on-drag": "npm:cytoscape-autopan-on-drag@2.0.1",
    "cytoscape-context-menus": "npm:cytoscape-context-menus@2.1.1",
    "cytoscape-edgehandles": "npm:cytoscape-edgehandles@2.9.0",
    "fetch": "github:github/fetch@1.1.1",
    "font-awesome": "npm:font-awesome@4.6.3",
    "i18next-xhr-backend": "npm:i18next-xhr-backend@1.2.1",
    "jquery": "npm:jquery@2.2.4",
    "oribella-aurelia-sortable": "npm:oribella-aurelia-sortable@0.8.0",
    "region-flags": "github:behdad/region-flags@1.0.1",
    "sweetalert2": "npm:sweetalert2@6.4.2",
    "text": "github:systemjs/plugin-text@0.0.9",
    "github:jspm/nodelibs-assert@0.1.0": {
      "assert": "npm:assert@1.4.1"
    },
    "github:jspm/nodelibs-buffer@0.1.0": {
      "buffer": "npm:buffer@3.6.0"
    },
    "github:jspm/nodelibs-os@0.1.0": {
      "os-browserify": "npm:os-browserify@0.1.2"
    },
    "github:jspm/nodelibs-path@0.1.0": {
      "path-browserify": "npm:path-browserify@0.0.0"
    },
    "github:jspm/nodelibs-process@0.1.2": {
      "process": "npm:process@0.11.9"
    },
    "github:jspm/nodelibs-util@0.1.0": {
      "util": "npm:util@0.10.3"
    },
    "github:jspm/nodelibs-vm@0.1.0": {
      "vm-browserify": "npm:vm-browserify@0.0.4"
    },
    "github:twbs/bootstrap@3.3.7": {
      "jquery": "npm:jquery@2.2.4"
    },
    "github:vheissu/aurelia-configuration@1.0.4": {
      "aurelia-dependency-injection": "npm:aurelia-dependency-injection@1.2.1",
      "aurelia-loader": "npm:aurelia-loader@1.0.0",
      "aurelia-path": "npm:aurelia-path@1.1.1"
    },
    "npm:arrive@2.3.1": {
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:assert@1.4.1": {
      "assert": "github:jspm/nodelibs-assert@0.1.0",
      "buffer": "github:jspm/nodelibs-buffer@0.1.0",
      "process": "github:jspm/nodelibs-process@0.1.2",
      "util": "npm:util@0.10.3"
    },
    "npm:aurelia-animator-css@1.0.1": {
      "aurelia-metadata": "npm:aurelia-metadata@1.0.3",
      "aurelia-pal": "npm:aurelia-pal@1.3.0",
      "aurelia-templating": "npm:aurelia-templating@1.1.4"
    },
    "npm:aurelia-binding@1.1.1": {
      "aurelia-logging": "npm:aurelia-logging@1.3.0",
      "aurelia-metadata": "npm:aurelia-metadata@1.0.3",
      "aurelia-pal": "npm:aurelia-pal@1.3.0",
      "aurelia-task-queue": "npm:aurelia-task-queue@1.1.0"
    },
    "npm:aurelia-bootstrapper@1.0.1": {
      "aurelia-event-aggregator": "npm:aurelia-event-aggregator@1.0.1",
      "aurelia-framework": "npm:aurelia-framework@1.0.8",
      "aurelia-history": "npm:aurelia-history@1.0.0",
      "aurelia-history-browser": "npm:aurelia-history-browser@1.0.0",
      "aurelia-loader-default": "npm:aurelia-loader-default@1.0.0",
      "aurelia-logging-console": "npm:aurelia-logging-console@1.0.0",
      "aurelia-pal": "npm:aurelia-pal@1.3.0",
      "aurelia-pal-browser": "npm:aurelia-pal-browser@1.1.0",
      "aurelia-polyfills": "npm:aurelia-polyfills@1.2.0",
      "aurelia-router": "npm:aurelia-router@1.1.0",
      "aurelia-templating": "npm:aurelia-templating@1.1.4",
      "aurelia-templating-binding": "npm:aurelia-templating-binding@1.1.0",
      "aurelia-templating-resources": "npm:aurelia-templating-resources@1.2.0",
      "aurelia-templating-router": "npm:aurelia-templating-router@1.0.1"
    },
    "npm:aurelia-dependency-injection@1.2.1": {
      "aurelia-metadata": "npm:aurelia-metadata@1.0.3",
      "aurelia-pal": "npm:aurelia-pal@1.3.0"
    },
    "npm:aurelia-event-aggregator@1.0.1": {
      "aurelia-logging": "npm:aurelia-logging@1.3.0"
    },
    "npm:aurelia-framework@1.0.8": {
      "aurelia-binding": "npm:aurelia-binding@1.1.1",
      "aurelia-dependency-injection": "npm:aurelia-dependency-injection@1.2.1",
      "aurelia-loader": "npm:aurelia-loader@1.0.0",
      "aurelia-logging": "npm:aurelia-logging@1.3.0",
      "aurelia-metadata": "npm:aurelia-metadata@1.0.3",
      "aurelia-pal": "npm:aurelia-pal@1.3.0",
      "aurelia-path": "npm:aurelia-path@1.1.1",
      "aurelia-task-queue": "npm:aurelia-task-queue@1.1.0",
      "aurelia-templating": "npm:aurelia-templating@1.1.4"
    },
    "npm:aurelia-history-browser@1.0.0": {
      "aurelia-history": "npm:aurelia-history@1.0.0",
      "aurelia-pal": "npm:aurelia-pal@1.3.0"
    },
    "npm:aurelia-http-client@1.0.3": {
      "aurelia-pal": "npm:aurelia-pal@1.3.0",
      "aurelia-path": "npm:aurelia-path@1.1.1"
    },
    "npm:aurelia-i18n@1.2.3": {
      "aurelia-binding": "npm:aurelia-binding@1.1.1",
      "aurelia-dependency-injection": "npm:aurelia-dependency-injection@1.2.1",
      "aurelia-event-aggregator": "npm:aurelia-event-aggregator@1.0.1",
      "aurelia-loader": "npm:aurelia-loader@1.0.0",
      "aurelia-logging": "npm:aurelia-logging@1.3.0",
      "aurelia-pal": "npm:aurelia-pal@1.3.0",
      "aurelia-templating": "npm:aurelia-templating@1.1.4",
      "aurelia-templating-resources": "npm:aurelia-templating-resources@1.2.0",
      "i18next": "npm:i18next@3.5.2",
      "intl": "npm:intl@1.2.5"
    },
    "npm:aurelia-loader-default@1.0.0": {
      "aurelia-loader": "npm:aurelia-loader@1.0.0",
      "aurelia-metadata": "npm:aurelia-metadata@1.0.3",
      "aurelia-pal": "npm:aurelia-pal@1.3.0"
    },
    "npm:aurelia-loader@1.0.0": {
      "aurelia-metadata": "npm:aurelia-metadata@1.0.3",
      "aurelia-path": "npm:aurelia-path@1.1.1"
    },
    "npm:aurelia-logging-console@1.0.0": {
      "aurelia-logging": "npm:aurelia-logging@1.3.0"
    },
    "npm:aurelia-metadata@1.0.3": {
      "aurelia-pal": "npm:aurelia-pal@1.3.0"
    },
    "npm:aurelia-pal-browser@1.1.0": {
      "aurelia-pal": "npm:aurelia-pal@1.3.0"
    },
    "npm:aurelia-polyfills@1.2.0": {
      "aurelia-pal": "npm:aurelia-pal@1.3.0"
    },
    "npm:aurelia-route-recognizer@1.1.0": {
      "aurelia-path": "npm:aurelia-path@1.1.1"
    },
    "npm:aurelia-router@1.1.0": {
      "aurelia-dependency-injection": "npm:aurelia-dependency-injection@1.2.1",
      "aurelia-event-aggregator": "npm:aurelia-event-aggregator@1.0.1",
      "aurelia-history": "npm:aurelia-history@1.0.0",
      "aurelia-logging": "npm:aurelia-logging@1.3.0",
      "aurelia-path": "npm:aurelia-path@1.1.1",
      "aurelia-route-recognizer": "npm:aurelia-route-recognizer@1.1.0"
    },
    "npm:aurelia-task-queue@1.1.0": {
      "aurelia-pal": "npm:aurelia-pal@1.3.0"
    },
    "npm:aurelia-templating-binding@1.1.0": {
      "aurelia-binding": "npm:aurelia-binding@1.1.1",
      "aurelia-logging": "npm:aurelia-logging@1.3.0",
      "aurelia-templating": "npm:aurelia-templating@1.1.4"
    },
    "npm:aurelia-templating-resources@1.2.0": {
      "aurelia-binding": "npm:aurelia-binding@1.1.1",
      "aurelia-dependency-injection": "npm:aurelia-dependency-injection@1.2.1",
      "aurelia-loader": "npm:aurelia-loader@1.0.0",
      "aurelia-logging": "npm:aurelia-logging@1.3.0",
      "aurelia-metadata": "npm:aurelia-metadata@1.0.3",
      "aurelia-pal": "npm:aurelia-pal@1.3.0",
      "aurelia-path": "npm:aurelia-path@1.1.1",
      "aurelia-task-queue": "npm:aurelia-task-queue@1.1.0",
      "aurelia-templating": "npm:aurelia-templating@1.1.4"
    },
    "npm:aurelia-templating-router@1.0.1": {
      "aurelia-binding": "npm:aurelia-binding@1.1.1",
      "aurelia-dependency-injection": "npm:aurelia-dependency-injection@1.2.1",
      "aurelia-logging": "npm:aurelia-logging@1.3.0",
      "aurelia-metadata": "npm:aurelia-metadata@1.0.3",
      "aurelia-pal": "npm:aurelia-pal@1.3.0",
      "aurelia-path": "npm:aurelia-path@1.1.1",
      "aurelia-router": "npm:aurelia-router@1.1.0",
      "aurelia-templating": "npm:aurelia-templating@1.1.4"
    },
    "npm:aurelia-templating@1.1.4": {
      "aurelia-binding": "npm:aurelia-binding@1.1.1",
      "aurelia-dependency-injection": "npm:aurelia-dependency-injection@1.2.1",
      "aurelia-loader": "npm:aurelia-loader@1.0.0",
      "aurelia-logging": "npm:aurelia-logging@1.3.0",
      "aurelia-metadata": "npm:aurelia-metadata@1.0.3",
      "aurelia-pal": "npm:aurelia-pal@1.3.0",
      "aurelia-path": "npm:aurelia-path@1.1.1",
      "aurelia-task-queue": "npm:aurelia-task-queue@1.1.0"
    },
    "npm:aurelia-testing@1.0.0-beta.2.0.1": {
      "aurelia-dependency-injection": "npm:aurelia-dependency-injection@1.2.1",
      "aurelia-framework": "npm:aurelia-framework@1.0.8",
      "aurelia-logging": "npm:aurelia-logging@1.3.0",
      "aurelia-pal": "npm:aurelia-pal@1.3.0",
      "aurelia-templating": "npm:aurelia-templating@1.1.4"
    },
    "npm:aurelia-validation@1.0.0-beta.1.0.1": {
      "aurelia-binding": "npm:aurelia-binding@1.1.1",
      "aurelia-dependency-injection": "npm:aurelia-dependency-injection@1.2.1",
      "aurelia-logging": "npm:aurelia-logging@1.3.0",
      "aurelia-pal": "npm:aurelia-pal@1.3.0",
      "aurelia-task-queue": "npm:aurelia-task-queue@1.1.0",
      "aurelia-templating": "npm:aurelia-templating@1.1.4"
    },
    "npm:bluebird@3.4.1": {
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:bootstrap-material-design@0.5.10": {
      "bootstrap": "npm:bootstrap@3.3.7",
      "fs": "github:jspm/nodelibs-fs@0.1.2",
      "jquery": "npm:jquery@2.2.4",
      "path": "github:jspm/nodelibs-path@0.1.0",
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:bootstrap-select@1.12.1": {
      "jquery": "npm:jquery@2.2.4"
    },
    "npm:bootstrap@3.3.7": {
      "fs": "github:jspm/nodelibs-fs@0.1.2",
      "path": "github:jspm/nodelibs-path@0.1.0",
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:buffer@3.6.0": {
      "base64-js": "npm:base64-js@0.0.8",
      "child_process": "github:jspm/nodelibs-child_process@0.1.0",
      "fs": "github:jspm/nodelibs-fs@0.1.2",
      "ieee754": "npm:ieee754@1.1.8",
      "isarray": "npm:isarray@1.0.0",
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:cytoscape-autopan-on-drag@2.0.1": {
      "child_process": "github:jspm/nodelibs-child_process@0.1.0",
      "cytoscape": "npm:cytoscape@2.7.14",
      "fs": "github:jspm/nodelibs-fs@0.1.2",
      "path": "github:jspm/nodelibs-path@0.1.0",
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:cytoscape-context-menus@2.1.1": {
      "child_process": "github:jspm/nodelibs-child_process@0.1.0",
      "cytoscape": "npm:cytoscape@2.7.14",
      "fs": "github:jspm/nodelibs-fs@0.1.2",
      "jquery": "npm:jquery@3.1.1",
      "path": "github:jspm/nodelibs-path@0.1.0",
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:cytoscape-edgehandles@2.9.0": {
      "child_process": "github:jspm/nodelibs-child_process@0.1.0",
      "cytoscape": "npm:cytoscape@2.7.14",
      "fs": "github:jspm/nodelibs-fs@0.1.2",
      "path": "github:jspm/nodelibs-path@0.1.0",
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:cytoscape@2.7.14": {
      "os": "github:jspm/nodelibs-os@0.1.0",
      "path": "github:jspm/nodelibs-path@0.1.0",
      "process": "github:jspm/nodelibs-process@0.1.2",
      "systemjs-json": "github:systemjs/plugin-json@0.1.2"
    },
    "npm:es6-promise@4.0.5": {
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:font-awesome@4.6.3": {
      "css": "github:systemjs/plugin-css@0.1.32"
    },
    "npm:i18next@3.5.2": {
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:inherits@2.0.1": {
      "util": "github:jspm/nodelibs-util@0.1.0"
    },
    "npm:intl@1.2.5": {
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:oribella-aurelia-sortable@0.8.0": {
      "aurelia-dependency-injection": "npm:aurelia-dependency-injection@1.2.1",
      "aurelia-pal": "npm:aurelia-pal@1.3.0",
      "aurelia-pal-browser": "npm:aurelia-pal-browser@1.1.0",
      "aurelia-templating": "npm:aurelia-templating@1.1.4",
      "oribella-default-gestures": "npm:oribella-default-gestures@0.5.4"
    },
    "npm:oribella-default-gestures@0.5.4": {
      "oribella-doubletap": "npm:oribella-doubletap@0.5.4",
      "oribella-framework": "npm:oribella-framework@0.5.3",
      "oribella-longtap": "npm:oribella-longtap@0.5.4",
      "oribella-longtap-swipe": "npm:oribella-longtap-swipe@0.5.4",
      "oribella-pinch": "npm:oribella-pinch@0.5.4",
      "oribella-rotate": "npm:oribella-rotate@0.5.4",
      "oribella-swipe": "npm:oribella-swipe@0.5.4",
      "oribella-tap": "npm:oribella-tap@0.5.4"
    },
    "npm:oribella-doubletap@0.5.4": {
      "oribella-framework": "npm:oribella-framework@0.5.3"
    },
    "npm:oribella-longtap-swipe@0.5.4": {
      "oribella-framework": "npm:oribella-framework@0.5.3"
    },
    "npm:oribella-longtap@0.5.4": {
      "oribella-framework": "npm:oribella-framework@0.5.3"
    },
    "npm:oribella-pinch@0.5.4": {
      "oribella-framework": "npm:oribella-framework@0.5.3"
    },
    "npm:oribella-rotate@0.5.4": {
      "oribella-framework": "npm:oribella-framework@0.5.3"
    },
    "npm:oribella-swipe@0.5.4": {
      "oribella-framework": "npm:oribella-framework@0.5.3"
    },
    "npm:oribella-tap@0.5.4": {
      "oribella-framework": "npm:oribella-framework@0.5.3"
    },
    "npm:os-browserify@0.1.2": {
      "os": "github:jspm/nodelibs-os@0.1.0"
    },
    "npm:path-browserify@0.0.0": {
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:process@0.11.9": {
      "assert": "github:jspm/nodelibs-assert@0.1.0",
      "fs": "github:jspm/nodelibs-fs@0.1.2",
      "vm": "github:jspm/nodelibs-vm@0.1.0"
    },
    "npm:sweetalert2@6.4.2": {
      "es6-promise": "npm:es6-promise@4.0.5"
    },
    "npm:util@0.10.3": {
      "inherits": "npm:inherits@2.0.1",
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:vm-browserify@0.0.4": {
      "indexof": "npm:indexof@0.0.1"
    }
  }
});
