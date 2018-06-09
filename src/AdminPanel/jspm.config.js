System.config({
  baseURL: "/",
  defaultJSExtensions: true,
  transpiler: false,
  paths: {
    "*": "admin/dist/*",
    "res/*": "admin/res/*",
    "admin/bundles/*": "*",
    "github:*": "jspm_packages/github/*",
    "npm:*": "jspm_packages/npm/*"
  },

  map: {
    "arrive": "npm:arrive@2.4.1",
    "aurelia-animator-css": "npm:aurelia-animator-css@1.0.4",
    "aurelia-binding": "npm:aurelia-binding@1.5.0",
    "aurelia-bootstrapper": "npm:aurelia-bootstrapper@1.0.1",
    "aurelia-configuration": "github:vheissu/aurelia-configuration@1.0.4",
    "aurelia-cookie": "npm:aurelia-cookie@1.0.10",
    "aurelia-dependency-injection": "npm:aurelia-dependency-injection@1.3.2",
    "aurelia-dialog": "npm:aurelia-dialog@1.0.0-rc.1.0.3",
    "aurelia-event-aggregator": "npm:aurelia-event-aggregator@1.0.1",
    "aurelia-framework": "npm:aurelia-framework@1.1.4",
    "aurelia-history-browser": "npm:aurelia-history-browser@1.1.0",
    "aurelia-http-client": "npm:aurelia-http-client@1.2.1",
    "aurelia-i18n": "npm:aurelia-i18n@1.6.2",
    "aurelia-loader-default": "npm:aurelia-loader-default@1.0.3",
    "aurelia-logging": "npm:aurelia-logging@1.4.0",
    "aurelia-logging-console": "npm:aurelia-logging-console@1.0.0",
    "aurelia-metadata": "npm:aurelia-metadata@1.0.3",
    "aurelia-pal-browser": "npm:aurelia-pal-browser@1.7.0",
    "aurelia-path": "npm:aurelia-path@1.1.1",
    "aurelia-plugins-tabs": "npm:aurelia-plugins-tabs@2.5.2",
    "aurelia-polyfills": "npm:aurelia-polyfills@1.2.2",
    "aurelia-router": "npm:aurelia-router@1.4.0",
    "aurelia-task-queue": "npm:aurelia-task-queue@1.2.1",
    "aurelia-templating": "npm:aurelia-templating@1.7.0",
    "aurelia-templating-binding": "npm:aurelia-templating-binding@1.4.0",
    "aurelia-templating-resources": "npm:aurelia-templating-resources@1.5.4",
    "aurelia-templating-router": "npm:aurelia-templating-router@1.2.0",
    "aurelia-testing": "npm:aurelia-testing@1.0.0-beta.3.0.1",
    "aurelia-validation": "npm:aurelia-validation@1.1.2",
    "bluebird": "npm:bluebird@3.4.1",
    "bootstrap": "github:twbs/bootstrap@3.3.7",
    "bootstrap-sass": "github:twbs/bootstrap-sass@3.3.7",
    "change-case": "npm:change-case@3.0.1",
    "compass-mixins": "npm:compass-mixins@0.12.10",
    "cytoscape": "npm:cytoscape@2.7.25",
    "cytoscape-autopan-on-drag": "npm:cytoscape-autopan-on-drag@2.0.2",
    "cytoscape-context-menus": "npm:cytoscape-context-menus@2.1.1",
    "cytoscape-edgehandles": "npm:cytoscape-edgehandles@2.15.0",
    "fetch": "github:github/fetch@1.1.1",
    "jquery": "npm:jquery@3.2.1",
    "lodash": "npm:lodash@4.17.10",
    "martingust/aurelia-repeat-strategies": "github:martingust/aurelia-repeat-strategies@master",
    "moment": "npm:moment@2.22.2",
    "nprogress": "github:rstacruz/nprogress@0.2.0",
    "oribella-aurelia-sortable": "npm:oribella-aurelia-sortable@0.13.0",
    "region-flags": "npm:region-flags@1.1.0",
    "select2": "github:select2/select2@4.0.5",
    "sweetalert2": "npm:sweetalert2@6.11.5",
    "text": "github:systemjs/plugin-text@0.0.9",
    "yamljs": "npm:yamljs@0.3.0",
    "github:jspm/nodelibs-assert@0.1.0": {
      "assert": "npm:assert@1.4.1"
    },
    "github:jspm/nodelibs-buffer@0.1.1": {
      "buffer": "npm:buffer@5.1.0"
    },
    "github:jspm/nodelibs-events@0.1.1": {
      "events": "npm:events@1.0.2"
    },
    "github:jspm/nodelibs-os@0.1.0": {
      "os-browserify": "npm:os-browserify@0.1.2"
    },
    "github:jspm/nodelibs-path@0.1.0": {
      "path-browserify": "npm:path-browserify@0.0.0"
    },
    "github:jspm/nodelibs-process@0.1.2": {
      "process": "npm:process@0.11.10"
    },
    "github:jspm/nodelibs-util@0.1.0": {
      "util": "npm:util@0.10.3"
    },
    "github:jspm/nodelibs-vm@0.1.0": {
      "vm-browserify": "npm:vm-browserify@0.0.4"
    },
    "github:martingust/aurelia-repeat-strategies@master": {
      "aurelia-templating-resources": "npm:aurelia-templating-resources@1.5.4"
    },
    "github:rstacruz/nprogress@0.2.0": {
      "css": "github:systemjs/plugin-css@0.1.36"
    },
    "github:select2/select2@4.0.5": {
      "jquery": "npm:jquery@3.2.1"
    },
    "github:twbs/bootstrap@3.3.7": {
      "jquery": "npm:jquery@3.2.1"
    },
    "github:vheissu/aurelia-configuration@1.0.4": {
      "aurelia-dependency-injection": "npm:aurelia-dependency-injection@1.3.2",
      "aurelia-loader": "npm:aurelia-loader@1.0.0",
      "aurelia-path": "npm:aurelia-path@1.1.1"
    },
    "npm:argparse@1.0.10": {
      "fs": "github:jspm/nodelibs-fs@0.1.2",
      "path": "github:jspm/nodelibs-path@0.1.0",
      "process": "github:jspm/nodelibs-process@0.1.2",
      "sprintf-js": "npm:sprintf-js@1.0.3",
      "util": "github:jspm/nodelibs-util@0.1.0"
    },
    "npm:arrive@2.4.1": {
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:assert@1.4.1": {
      "assert": "github:jspm/nodelibs-assert@0.1.0",
      "buffer": "github:jspm/nodelibs-buffer@0.1.1",
      "process": "github:jspm/nodelibs-process@0.1.2",
      "util": "npm:util@0.10.3"
    },
    "npm:aurelia-animator-css@1.0.4": {
      "aurelia-metadata": "npm:aurelia-metadata@1.0.3",
      "aurelia-pal": "npm:aurelia-pal@1.7.0",
      "aurelia-templating": "npm:aurelia-templating@1.7.0"
    },
    "npm:aurelia-binding@1.5.0": {
      "aurelia-logging": "npm:aurelia-logging@1.4.0",
      "aurelia-metadata": "npm:aurelia-metadata@1.0.3",
      "aurelia-pal": "npm:aurelia-pal@1.7.0",
      "aurelia-task-queue": "npm:aurelia-task-queue@1.2.1"
    },
    "npm:aurelia-bootstrapper@1.0.1": {
      "aurelia-event-aggregator": "npm:aurelia-event-aggregator@1.0.1",
      "aurelia-framework": "npm:aurelia-framework@1.1.4",
      "aurelia-history": "npm:aurelia-history@1.1.0",
      "aurelia-history-browser": "npm:aurelia-history-browser@1.1.0",
      "aurelia-loader-default": "npm:aurelia-loader-default@1.0.3",
      "aurelia-logging-console": "npm:aurelia-logging-console@1.0.0",
      "aurelia-pal": "npm:aurelia-pal@1.7.0",
      "aurelia-pal-browser": "npm:aurelia-pal-browser@1.7.0",
      "aurelia-polyfills": "npm:aurelia-polyfills@1.2.2",
      "aurelia-router": "npm:aurelia-router@1.4.0",
      "aurelia-templating": "npm:aurelia-templating@1.7.0",
      "aurelia-templating-binding": "npm:aurelia-templating-binding@1.4.0",
      "aurelia-templating-resources": "npm:aurelia-templating-resources@1.5.4",
      "aurelia-templating-router": "npm:aurelia-templating-router@1.2.0"
    },
    "npm:aurelia-dependency-injection@1.3.2": {
      "aurelia-metadata": "npm:aurelia-metadata@1.0.3",
      "aurelia-pal": "npm:aurelia-pal@1.7.0"
    },
    "npm:aurelia-dialog@1.0.0-rc.1.0.3": {
      "aurelia-dependency-injection": "npm:aurelia-dependency-injection@1.3.2",
      "aurelia-metadata": "npm:aurelia-metadata@1.0.3",
      "aurelia-pal": "npm:aurelia-pal@1.7.0",
      "aurelia-templating": "npm:aurelia-templating@1.7.0"
    },
    "npm:aurelia-event-aggregator@1.0.1": {
      "aurelia-logging": "npm:aurelia-logging@1.4.0"
    },
    "npm:aurelia-framework@1.1.4": {
      "aurelia-binding": "npm:aurelia-binding@1.5.0",
      "aurelia-dependency-injection": "npm:aurelia-dependency-injection@1.3.2",
      "aurelia-loader": "npm:aurelia-loader@1.0.0",
      "aurelia-logging": "npm:aurelia-logging@1.4.0",
      "aurelia-metadata": "npm:aurelia-metadata@1.0.3",
      "aurelia-pal": "npm:aurelia-pal@1.7.0",
      "aurelia-path": "npm:aurelia-path@1.1.1",
      "aurelia-task-queue": "npm:aurelia-task-queue@1.2.1",
      "aurelia-templating": "npm:aurelia-templating@1.7.0"
    },
    "npm:aurelia-history-browser@1.1.0": {
      "aurelia-history": "npm:aurelia-history@1.1.0",
      "aurelia-pal": "npm:aurelia-pal@1.7.0"
    },
    "npm:aurelia-http-client@1.2.1": {
      "aurelia-pal": "npm:aurelia-pal@1.7.0",
      "aurelia-path": "npm:aurelia-path@1.1.1"
    },
    "npm:aurelia-i18n@1.6.2": {
      "aurelia-binding": "npm:aurelia-binding@1.5.0",
      "aurelia-dependency-injection": "npm:aurelia-dependency-injection@1.3.2",
      "aurelia-event-aggregator": "npm:aurelia-event-aggregator@1.0.1",
      "aurelia-loader": "npm:aurelia-loader@1.0.0",
      "aurelia-logging": "npm:aurelia-logging@1.4.0",
      "aurelia-metadata": "npm:aurelia-metadata@1.0.3",
      "aurelia-pal": "npm:aurelia-pal@1.7.0",
      "aurelia-templating": "npm:aurelia-templating@1.7.0",
      "aurelia-templating-resources": "npm:aurelia-templating-resources@1.5.4",
      "i18next": "npm:i18next@3.5.2",
      "intl": "npm:intl@1.2.5"
    },
    "npm:aurelia-loader-default@1.0.3": {
      "aurelia-loader": "npm:aurelia-loader@1.0.0",
      "aurelia-metadata": "npm:aurelia-metadata@1.0.3",
      "aurelia-pal": "npm:aurelia-pal@1.7.0"
    },
    "npm:aurelia-loader@1.0.0": {
      "aurelia-metadata": "npm:aurelia-metadata@1.0.3",
      "aurelia-path": "npm:aurelia-path@1.1.1"
    },
    "npm:aurelia-logging-console@1.0.0": {
      "aurelia-logging": "npm:aurelia-logging@1.4.0"
    },
    "npm:aurelia-metadata@1.0.3": {
      "aurelia-pal": "npm:aurelia-pal@1.7.0"
    },
    "npm:aurelia-pal-browser@1.7.0": {
      "aurelia-pal": "npm:aurelia-pal@1.7.0"
    },
    "npm:aurelia-plugins-tabs@2.5.2": {
      "aurelia-dependency-injection": "npm:aurelia-dependency-injection@1.3.2",
      "aurelia-event-aggregator": "npm:aurelia-event-aggregator@1.0.1",
      "aurelia-pal": "npm:aurelia-pal@1.7.0",
      "aurelia-templating": "npm:aurelia-templating@1.7.0"
    },
    "npm:aurelia-polyfills@1.2.2": {
      "aurelia-pal": "npm:aurelia-pal@1.7.0"
    },
    "npm:aurelia-route-recognizer@1.1.1": {
      "aurelia-path": "npm:aurelia-path@1.1.1"
    },
    "npm:aurelia-router@1.4.0": {
      "aurelia-dependency-injection": "npm:aurelia-dependency-injection@1.3.2",
      "aurelia-event-aggregator": "npm:aurelia-event-aggregator@1.0.1",
      "aurelia-history": "npm:aurelia-history@1.1.0",
      "aurelia-logging": "npm:aurelia-logging@1.4.0",
      "aurelia-path": "npm:aurelia-path@1.1.1",
      "aurelia-route-recognizer": "npm:aurelia-route-recognizer@1.1.1"
    },
    "npm:aurelia-task-queue@1.2.1": {
      "aurelia-pal": "npm:aurelia-pal@1.7.0"
    },
    "npm:aurelia-templating-binding@1.4.0": {
      "aurelia-binding": "npm:aurelia-binding@1.5.0",
      "aurelia-logging": "npm:aurelia-logging@1.4.0",
      "aurelia-templating": "npm:aurelia-templating@1.7.0"
    },
    "npm:aurelia-templating-resources@1.5.4": {
      "aurelia-binding": "npm:aurelia-binding@1.5.0",
      "aurelia-dependency-injection": "npm:aurelia-dependency-injection@1.3.2",
      "aurelia-loader": "npm:aurelia-loader@1.0.0",
      "aurelia-logging": "npm:aurelia-logging@1.4.0",
      "aurelia-metadata": "npm:aurelia-metadata@1.0.3",
      "aurelia-pal": "npm:aurelia-pal@1.7.0",
      "aurelia-path": "npm:aurelia-path@1.1.1",
      "aurelia-task-queue": "npm:aurelia-task-queue@1.2.1",
      "aurelia-templating": "npm:aurelia-templating@1.7.0"
    },
    "npm:aurelia-templating-router@1.2.0": {
      "aurelia-binding": "npm:aurelia-binding@1.5.0",
      "aurelia-dependency-injection": "npm:aurelia-dependency-injection@1.3.2",
      "aurelia-logging": "npm:aurelia-logging@1.4.0",
      "aurelia-metadata": "npm:aurelia-metadata@1.0.3",
      "aurelia-pal": "npm:aurelia-pal@1.7.0",
      "aurelia-path": "npm:aurelia-path@1.1.1",
      "aurelia-router": "npm:aurelia-router@1.4.0",
      "aurelia-templating": "npm:aurelia-templating@1.7.0"
    },
    "npm:aurelia-templating@1.7.0": {
      "aurelia-binding": "npm:aurelia-binding@1.5.0",
      "aurelia-dependency-injection": "npm:aurelia-dependency-injection@1.3.2",
      "aurelia-loader": "npm:aurelia-loader@1.0.0",
      "aurelia-logging": "npm:aurelia-logging@1.4.0",
      "aurelia-metadata": "npm:aurelia-metadata@1.0.3",
      "aurelia-pal": "npm:aurelia-pal@1.7.0",
      "aurelia-path": "npm:aurelia-path@1.1.1",
      "aurelia-task-queue": "npm:aurelia-task-queue@1.2.1"
    },
    "npm:aurelia-testing@1.0.0-beta.3.0.1": {
      "aurelia-dependency-injection": "npm:aurelia-dependency-injection@1.3.2",
      "aurelia-framework": "npm:aurelia-framework@1.1.4",
      "aurelia-logging": "npm:aurelia-logging@1.4.0",
      "aurelia-pal": "npm:aurelia-pal@1.7.0",
      "aurelia-templating": "npm:aurelia-templating@1.7.0"
    },
    "npm:aurelia-validation@1.1.2": {
      "aurelia-binding": "npm:aurelia-binding@1.5.0",
      "aurelia-dependency-injection": "npm:aurelia-dependency-injection@1.3.2",
      "aurelia-logging": "npm:aurelia-logging@1.4.0",
      "aurelia-pal": "npm:aurelia-pal@1.7.0",
      "aurelia-task-queue": "npm:aurelia-task-queue@1.2.1",
      "aurelia-templating": "npm:aurelia-templating@1.7.0"
    },
    "npm:bluebird@3.4.1": {
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:brace-expansion@1.1.11": {
      "balanced-match": "npm:balanced-match@1.0.0",
      "concat-map": "npm:concat-map@0.0.1"
    },
    "npm:buffer@5.1.0": {
      "base64-js": "npm:base64-js@1.3.0",
      "ieee754": "npm:ieee754@1.1.11"
    },
    "npm:camel-case@3.0.0": {
      "no-case": "npm:no-case@2.3.2",
      "upper-case": "npm:upper-case@1.1.3"
    },
    "npm:change-case@3.0.1": {
      "camel-case": "npm:camel-case@3.0.0",
      "constant-case": "npm:constant-case@2.0.0",
      "dot-case": "npm:dot-case@2.1.1",
      "header-case": "npm:header-case@1.0.1",
      "is-lower-case": "npm:is-lower-case@1.1.3",
      "is-upper-case": "npm:is-upper-case@1.1.2",
      "lower-case": "npm:lower-case@1.1.4",
      "lower-case-first": "npm:lower-case-first@1.0.2",
      "no-case": "npm:no-case@2.3.2",
      "param-case": "npm:param-case@2.1.1",
      "pascal-case": "npm:pascal-case@2.0.1",
      "path-case": "npm:path-case@2.1.1",
      "sentence-case": "npm:sentence-case@2.1.1",
      "snake-case": "npm:snake-case@2.1.0",
      "swap-case": "npm:swap-case@1.1.2",
      "title-case": "npm:title-case@2.1.1",
      "upper-case": "npm:upper-case@1.1.3",
      "upper-case-first": "npm:upper-case-first@1.1.2"
    },
    "npm:constant-case@2.0.0": {
      "snake-case": "npm:snake-case@2.1.0",
      "upper-case": "npm:upper-case@1.1.3"
    },
    "npm:cytoscape-autopan-on-drag@2.0.2": {
      "child_process": "github:jspm/nodelibs-child_process@0.1.0",
      "cytoscape": "npm:cytoscape@2.7.25",
      "fs": "github:jspm/nodelibs-fs@0.1.2",
      "path": "github:jspm/nodelibs-path@0.1.0",
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:cytoscape-context-menus@2.1.1": {
      "child_process": "github:jspm/nodelibs-child_process@0.1.0",
      "cytoscape": "npm:cytoscape@2.7.25",
      "fs": "github:jspm/nodelibs-fs@0.1.2",
      "jquery": "npm:jquery@3.2.1",
      "path": "github:jspm/nodelibs-path@0.1.0",
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:cytoscape-edgehandles@2.15.0": {
      "child_process": "github:jspm/nodelibs-child_process@0.1.0",
      "cytoscape": "npm:cytoscape@2.7.25",
      "fs": "github:jspm/nodelibs-fs@0.1.2",
      "lodash.debounce": "npm:lodash.debounce@4.0.8",
      "lodash.throttle": "npm:lodash.throttle@4.1.1",
      "path": "github:jspm/nodelibs-path@0.1.0",
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:cytoscape@2.7.25": {
      "os": "github:jspm/nodelibs-os@0.1.0",
      "path": "github:jspm/nodelibs-path@0.1.0",
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:dot-case@2.1.1": {
      "no-case": "npm:no-case@2.3.2"
    },
    "npm:fs.realpath@1.0.0": {
      "fs": "github:jspm/nodelibs-fs@0.1.2",
      "path": "github:jspm/nodelibs-path@0.1.0",
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:glob@7.1.2": {
      "assert": "github:jspm/nodelibs-assert@0.1.0",
      "events": "github:jspm/nodelibs-events@0.1.1",
      "fs": "github:jspm/nodelibs-fs@0.1.2",
      "fs.realpath": "npm:fs.realpath@1.0.0",
      "inflight": "npm:inflight@1.0.6",
      "inherits": "npm:inherits@2.0.1",
      "minimatch": "npm:minimatch@3.0.4",
      "once": "npm:once@1.4.0",
      "path": "github:jspm/nodelibs-path@0.1.0",
      "path-is-absolute": "npm:path-is-absolute@1.0.1",
      "process": "github:jspm/nodelibs-process@0.1.2",
      "util": "github:jspm/nodelibs-util@0.1.0"
    },
    "npm:header-case@1.0.1": {
      "no-case": "npm:no-case@2.3.2",
      "upper-case": "npm:upper-case@1.1.3"
    },
    "npm:i18next@3.5.2": {
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:inflight@1.0.6": {
      "once": "npm:once@1.4.0",
      "process": "github:jspm/nodelibs-process@0.1.2",
      "wrappy": "npm:wrappy@1.0.2"
    },
    "npm:inherits@2.0.1": {
      "util": "github:jspm/nodelibs-util@0.1.0"
    },
    "npm:intl@1.2.5": {
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:is-lower-case@1.1.3": {
      "lower-case": "npm:lower-case@1.1.4"
    },
    "npm:is-upper-case@1.1.2": {
      "upper-case": "npm:upper-case@1.1.3"
    },
    "npm:lodash.debounce@4.0.8": {
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:lodash.throttle@4.1.1": {
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:lower-case-first@1.0.2": {
      "lower-case": "npm:lower-case@1.1.4"
    },
    "npm:minimatch@3.0.4": {
      "brace-expansion": "npm:brace-expansion@1.1.11",
      "path": "github:jspm/nodelibs-path@0.1.0"
    },
    "npm:no-case@2.3.2": {
      "lower-case": "npm:lower-case@1.1.4"
    },
    "npm:once@1.4.0": {
      "wrappy": "npm:wrappy@1.0.2"
    },
    "npm:oribella-aurelia-sortable@0.13.0": {
      "aurelia-dependency-injection": "npm:aurelia-dependency-injection@1.3.2",
      "aurelia-pal": "npm:aurelia-pal@1.7.0",
      "aurelia-templating": "npm:aurelia-templating@1.7.0",
      "aurelia-templating-resources": "npm:aurelia-templating-resources@1.5.4",
      "oribella": "npm:oribella@0.8.2",
      "oribella-framework": "npm:oribella-framework@0.10.3",
      "tslib": "npm:tslib@1.8.0"
    },
    "npm:oribella-framework@0.10.3": {
      "tslib": "npm:tslib@1.8.0"
    },
    "npm:oribella@0.8.2": {
      "oribella-framework": "npm:oribella-framework@0.10.3",
      "tslib": "npm:tslib@1.8.0"
    },
    "npm:os-browserify@0.1.2": {
      "os": "github:jspm/nodelibs-os@0.1.0"
    },
    "npm:param-case@2.1.1": {
      "no-case": "npm:no-case@2.3.2"
    },
    "npm:pascal-case@2.0.1": {
      "camel-case": "npm:camel-case@3.0.0",
      "upper-case-first": "npm:upper-case-first@1.1.2"
    },
    "npm:path-browserify@0.0.0": {
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:path-case@2.1.1": {
      "no-case": "npm:no-case@2.3.2"
    },
    "npm:path-is-absolute@1.0.1": {
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:process@0.11.10": {
      "assert": "github:jspm/nodelibs-assert@0.1.0",
      "fs": "github:jspm/nodelibs-fs@0.1.2",
      "vm": "github:jspm/nodelibs-vm@0.1.0"
    },
    "npm:sentence-case@2.1.1": {
      "no-case": "npm:no-case@2.3.2",
      "upper-case-first": "npm:upper-case-first@1.1.2"
    },
    "npm:snake-case@2.1.0": {
      "no-case": "npm:no-case@2.3.2"
    },
    "npm:swap-case@1.1.2": {
      "lower-case": "npm:lower-case@1.1.4",
      "upper-case": "npm:upper-case@1.1.3"
    },
    "npm:title-case@2.1.1": {
      "no-case": "npm:no-case@2.3.2",
      "upper-case": "npm:upper-case@1.1.3"
    },
    "npm:upper-case-first@1.1.2": {
      "upper-case": "npm:upper-case@1.1.3"
    },
    "npm:util@0.10.3": {
      "inherits": "npm:inherits@2.0.1",
      "process": "github:jspm/nodelibs-process@0.1.2"
    },
    "npm:vm-browserify@0.0.4": {
      "indexof": "npm:indexof@0.0.1"
    },
    "npm:yamljs@0.3.0": {
      "argparse": "npm:argparse@1.0.10",
      "fs": "github:jspm/nodelibs-fs@0.1.2",
      "glob": "npm:glob@7.1.2",
      "path": "github:jspm/nodelibs-path@0.1.0",
      "process": "github:jspm/nodelibs-process@0.1.2",
      "systemjs-json": "github:systemjs/plugin-json@0.1.2"
    }
  }
});
