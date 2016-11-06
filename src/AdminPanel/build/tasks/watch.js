var gulp = require('gulp');
var paths = require('../paths');
var browserSync = require('browser-sync').create();
var replace = require('gulp-replace');

gulp.task('watch', ['browser-sync'], () => {
  gulp.watch(paths.scripts, ['build-scripts', browserSync.reload]);
  gulp.watch(paths.html, ['build-html', browserSync.reload]);
  gulp.watch(paths.scss, ['build-css', browserSync.reload]);
  gulp.watch('index.html').on('change', () => injectBrowserSyncSnippet() && browserSync.reload());
  gulp.watch('../Repeka/**', {interval: 500}).on('change', browserSync.reload);
});

function injectBrowserSyncSnippet() {
  var snippet = browserSync.getOption('snippet');
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
