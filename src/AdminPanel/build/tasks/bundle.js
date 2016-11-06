var gulp = require('gulp');
var bundler = require('aurelia-bundler');
var bundles = require('../bundles.js');
var paths = require('../paths');
var runSequence = require('run-sequence');
var path = require('path');

var config = {
  force: true,
  baseURL: paths.webRoot,
  configPath: path.join(paths.webAdminRoot, 'jspm.config.js'),
  bundles: bundles.bundles
};

var calledBuild = false;

// does not have build as dependency in order to allow bundling of already built sources
gulp.task('bundle', function () {
  try {
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
