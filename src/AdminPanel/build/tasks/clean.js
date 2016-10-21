var gulp = require('gulp');
var paths = require('../paths');
var del = require('del');
var vinylPaths = require('vinyl-paths');

gulp.task('clean', () => {
  return gulp.src([paths.output, paths.outputRoot + 'jspm_packages'], {read: false})
    .pipe(vinylPaths((paths) => {
      return del(paths, {force: true});
    }));
});
