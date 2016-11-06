var gulp = require('gulp');
var help = require('../../scripts/help');

gulp.task('help', function () {
  help.printHelp({
    "clean": "Clean built files",
    "lint": "Lint sources",
    "test": "Run unit tests",
    "watch": "Watch changes in the files and builds them continuously",
    "build": "Build the sources once",
    "bundle": "Bundle files for production"
  });
});

gulp.task('default', ['help']);

require('require-dir')('build/tasks');
