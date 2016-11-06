var gulp = require('gulp');
var paths = require('../paths');
var del = require('del');
var vinylPaths = require('vinyl-paths');
var path = require('path');

gulp.task('clean', () => {
  return gulp.src([paths.output, path.join(paths.webRoot, 'jspm_packages'), paths.webAdminRoot], {read: false})
    .pipe(vinylPaths((paths) => {
      return del(paths, {force: true});
    }));
});
