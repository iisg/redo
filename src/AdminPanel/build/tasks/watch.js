'use strict';

const gulp = require('gulp');
const path = require('path');
const paths = require('../paths');

gulp.task('watch', ['build'], () => {
  gulp.watch(paths.scripts, ['build-scripts']);
  gulp.watch(paths.html, ['bundle-views']);
  gulp.watch(paths.scss, ['build-css']);
  gulp.watch(path.join(paths.locales, '**/*'), ['bundle-locales']);
});

