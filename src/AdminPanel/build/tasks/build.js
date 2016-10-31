var gulp = require('gulp');
var runSequence = require('run-sequence');
var paths = require('../paths');
var plumber = require('gulp-plumber');
var changed = require('gulp-changed');
var htmlmin = require('gulp-htmlmin');
var sourcemaps = require('gulp-sourcemaps');
var notify = require('gulp-notify');
var typescript = require('gulp-typescript');
var sass = require('gulp-sass');
var symlink = require("symlink-or-copy").sync;

var typescriptCompiler;
gulp.task('build-scripts', () => {
  if (!typescriptCompiler) {
    typescriptCompiler = typescript.createProject('tsconfig.json');
  }
  return typescriptCompiler.src()
    .pipe(plumber({errorHandler: notify.onError('Error: <%= error.message %>')}))
    .pipe(changed(paths.output, {extension: '.js'}))
    .pipe(sourcemaps.init({loadMaps: true}))
    .pipe(typescriptCompiler())
    .pipe(sourcemaps.write('.', {includeContent: false, sourceRoot: '/src'}))
    .pipe(gulp.dest(paths.output));
});

gulp.task('build-html', () => {
  return gulp.src(paths.html)
    .pipe(plumber({errorHandler: notify.onError('HTML: <%= error.message %>')}))
    .pipe(changed(paths.output, {extension: '.html'}))
    .pipe(htmlmin({collapseWhitespace: true}))
    .pipe(gulp.dest(paths.output));
});

gulp.task('build-css', () => {
  return gulp.src(paths.scss)
    .pipe(plumber({errorHandler: notify.onError('SCSS: <%= error.message %>')}))
    .pipe(changed(paths.output, {extension: '.css'}))
    .pipe(sass())
    .pipe(gulp.dest(paths.output))
});

gulp.task('copy-jspm-config', () => {
  return gulp.src(paths.jspmConfig)
    .pipe(plumber({errorHandler: notify.onError('HTML: <%= error.message %>')}))
    .pipe(htmlmin({collapseWhitespace: true}))
    .pipe(gulp.dest(paths.output))
});

gulp.task('symlink-jspm-packages', () => {
  return symlink('jspm_packages', paths.outputRoot + 'jspm_packages');
});

gulp.task('build', (callback) => {
  return runSequence(
    'clean',
    ['build-scripts', 'build-html', 'build-css', 'copy-jspm-config', 'symlink-jspm-packages'],
    callback
  );
});
