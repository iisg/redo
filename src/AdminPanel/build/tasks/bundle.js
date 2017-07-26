'use strict';

const gulp = require('gulp');
const bundler = require('aurelia-bundler');
const bundles = require('../bundles.js');
const fs = require('fs');
const path = require('path');
const runSequence = require('run-sequence');

const paths = require('../paths');

const config = {
  force: true,
  baseURL: paths.webRoot,
  configPath: path.join(paths.webAdminRoot, 'jspm.config.js'),
  bundles: bundles.bundles
};

let calledBuild = false;

// does not have build as dependency in order to allow bundling of already built sources
gulp.task('bundle', function () {
  try {
    const locales = fs.readdirSync(paths.locales)
      .filter(file => fs.lstatSync(path.join(paths.locales, file)).isDirectory());
    //noinspection NodeModulesDependencies,ES6ModulesDependencies because JSON is a global builtin
    fs.writeFileSync(path.join(paths.webAdminRoot, 'locales.json'), JSON.stringify(locales));
    return bundler.bundle(config);
  } catch (e) {
    if (calledBuild) {
      throw e;
    } else {
      calledBuild = true;
      return runSequence('build', 'bundle');
    }
  }
});
