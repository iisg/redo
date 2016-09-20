module.exports = function (config) {
  config.set({
    basePath: '../',
    frameworks: ['systemjs', 'jasmine'],
    systemjs: {
      configFile: 'jspm.config.js',
      config: {
        paths: {
          "*": "*",
          "src/*": "src/*",
          "typescript": "node_modules/typescript/lib/typescript.js",
          "systemjs": "node_modules/systemjs/dist/system.js",
          'system-polyfills': 'node_modules/systemjs/dist/system-polyfills.js'
        },
        packages: {
          'test/unit': {
            defaultExtension: 'ts'
          },
          'src': {
            defaultExtension: 'ts'
          }
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
        'jspm_packages/**/*.js'
      ]
    },
    files: [
      'src/main.spec.ts',
      'src/**/*.spec.ts'
    ],
    exclude: [],
    preprocessors: {},
    reporters: ['mocha'],
    mochaReporter: {
      ignoreSkipped: true
    },
    port: 9876,
    colors: true,
    logLevel: config.LOG_WARN,
    autoWatch: true,
    browsers: ['Chrome'],
    singleRun: false
  });
};
