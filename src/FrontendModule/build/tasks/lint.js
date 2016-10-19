var gulp = require('gulp');
var paths = require('../paths');
var tslint = require('gulp-tslint');
var lintReporter = require('gulp-tslint-jenkins-reporter');
var templateLinter = require('gulp-aurelia-template-lint');
var TemplateLintConfig = require('aurelia-template-lint').Config;
var gutil = require('gulp-util');
var chalk = require('chalk');
var fs = require('fs');

gulp.task('lint', ['lint-ts', 'lint-aurelia-templates']);

gulp.task('lint-ts', () => {
  return gulp.src(paths.scripts[0])
    .pipe(tslint({
      formatter: "verbose",
      configuration: "build/tslint.json"
    }))
    .pipe(lintReporter({
      filename: paths.lintReports + 'ts.xml'
    }))
    .pipe(tslint.report());
});

gulp.task('lint-aurelia-templates', () => {
  var hasErrors = false;
  var lintErrors = {};
  var config = new TemplateLintConfig();
  config.useRuleAureliaBindingAccess = true;
  config.reflectionOpts.sourceFileGlob = paths.scripts[0];
  return gulp.src(paths.html)
    .pipe(templateLinter(config, (error, file) => {
      hasErrors = true;
      if (!lintErrors[file]) {
        lintErrors[file] = [];
      }
      lintErrors[file].push(error);
      gutil.log(chalk.red(`${error.message} Line ${error.line} Col ${error.column}`), file);
    }))
    .on('end', () => {
      if (Object.keys(lintErrors).length > 0) {
        fs.writeFileSync(paths.lintReports + 'aurelia-template.xml', aureliaTemplatesLintErrorsToXml(lintErrors));
        throw 'There are some aurelia-template-lint errors';
      }
    });
});

function aureliaTemplatesLintErrorsToXml(lintErrors) {
  var xml = '<?xml version="1.0" encoding="utf-8"?><checkstyle version="4.3">';
  for (var file in lintErrors) {
    var filepath = process.cwd() + file;
    xml += '<file name="' + filepath + '">';
    for (var i = 0; i < lintErrors[file].length; i++) {
      var error = lintErrors[file][i];
      xml += '<error line="' + error.line + '" column="' + error.column + '" severity="error" message="' + error.message + '" source="aurelia-template-lint"/>'
    }
    xml += '</file>';
  }
  xml += '</checkstyle>';
  return xml;
}
