var gulp = require('gulp');
var paths = require('../paths');
var tslint = require('gulp-tslint');
var templateLinter = require('gulp-aurelia-template-lint');
var TemplateLintConfig = require('aurelia-template-lint').Config;
var gutil = require('gulp-util');
var chalk = require('chalk');

gulp.task('lint', ['lint-ts', 'lint-aurelia-templates']);

gulp.task('lint-ts', () => {
  return gulp.src(paths.scripts[0])
    .pipe(tslint({formatter: "verbose"}))
    .pipe(tslint.report());
});

gulp.task('lint-aurelia-templates', () => {
  var hasErrors = false;
  var config = new TemplateLintConfig();
  config.useRuleAureliaBindingAccess = true;
  config.reflectionOpts.sourceFileGlob = paths.scripts[0];
  return gulp.src(paths.html)
    .pipe(templateLinter(config, (error, file) => {
      hasErrors = true;
      gutil.log(chalk.red(`${error.message} Line ${error.line} Col ${error.column}`), file);
    }))
    .on('end', () => {
      if (hasErrors) {
        throw 'There are some aurelia-template-lint errors';
      }
    });
});
