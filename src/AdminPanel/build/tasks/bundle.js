'use strict';

const gulp = require('gulp');
const bundler = require('aurelia-bundler');
const bundles = require('../bundles.js');
const path = require('path');
const runSequence = require('run-sequence');

const paths = require('../paths');

const bundlingConfig = function (whatToBundle) {
  return {
    force: true,
    baseURL: paths.webRoot,
    configPath: path.join(paths.webAdminRoot, 'jspm.config.js'),
    bundles: bundles[whatToBundle]
  };
};

gulp.task('bundle', function (cb) {
  return runSequence('bundle-vendors', 'bundle-views', 'bundle-locales', cb);
});

gulp.task('bundle-app', function () {
  return bundler.bundle(bundlingConfig('app'));
});

gulp.task('bundle-vendors', function () {
  return bundler.bundle(bundlingConfig('vendors'));
});

gulp.task('bundle-views', ['build-html'], function () {
  return bundler.bundle(bundlingConfig('views'));
});

gulp.task('bundle-locales', ['build-locales'], function () {
  return bundler.bundle(bundlingConfig('resources'));
});
