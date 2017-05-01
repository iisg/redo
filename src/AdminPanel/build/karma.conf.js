module.exports = function (config) {
  config.set({
    basePath: '../',
    frameworks: ['systemjs', 'jasmine'],
    systemjs: {
      baseURL: '/base/',
      configFile: 'jspm.config.js',
      config: {
        paths: {
          "*": "src/*",
          "src/*": "src/*",
          "typescript": "node_modules/typescript/lib/typescript.js",
          "systemjs": "node_modules/systemjs/dist/system.js",
          'system-polyfills': 'node_modules/systemjs/dist/system-polyfills.js'
        },
        packages: {
          '/base/src': {
            defaultExtension: 'ts'
          },
        },
        transpiler: 'typescript',
        typescriptOptions: {
          "module": "amd",
          "emitDecoratorMetadata": true,
          "experimentalDecorators": true
        }
      },
      serveFiles: [
        'src/**/*.*',
        'jspm_packages/**/*.js',
        'jspm_packages/**/*.json'
      ]
    },
    files: [
      'src/main.spec.ts',
      'src/**/*.spec.ts'
    ],
    exclude: [],
    preprocessors: {},
    reporters: ['mocha'],
    junitReporter: {
      outputDir: '../../var/reports/tests',
      outputFile: 'jasmine.xml',
      useBrowserName: false
    },
    mochaReporter: {
      ignoreSkipped: true
    },
    port: 9876,
    colors: true,
    logLevel: config.LOG_WARN,
    autoWatch: true,
    browsers: ['Chrome'],
    customLaunchers: {
      chrome_no_sandbox: {
        base: 'Chrome',
        flags: ['--no-sandbox']
      }
    },
    singleRun: false
  });
};
