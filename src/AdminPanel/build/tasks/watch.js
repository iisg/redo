'use strict';

const gulp = require('gulp');
const path = require('path');
const paths = require('../paths');
const browserSync = require('browser-sync').create();
const replace = require('gulp-replace');

gulp.task('watch', ['browser-sync'], () => {
  gulp.watch(paths.scripts, ['build-scripts', browserSync.reload]);
  gulp.watch(paths.html, ['build-html', browserSync.reload]);
  gulp.watch(paths.scss, ['build-css', browserSync.reload]);
  gulp.watch(path.join(paths.locales, '**/*'), ['build-locales', browserSync.reload]);
  gulp.watch('index.html').on('change', () => injectBrowserSyncSnippet() && browserSync.reload());
});

function injectBrowserSyncSnippet() {
  const snippet = browserSync.getOption('snippet');
  return gulp.src('index.html')
    .pipe(replace('</body>', snippet + '</body>'))
    .pipe(gulp.dest(paths.webAdminRoot));
}

gulp.task('browser-sync', ['build'], (done) => {
  browserSync.init({
    reloadOnRestart: true,
    reloadDelay: 300,
    online: false,
    logSnippet: false
  }, () => {
    injectBrowserSyncSnippet();
    done();
  });
});
