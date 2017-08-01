'use strict';

const changed = require('gulp-changed');
const concat = require('gulp-concat');
const gulp = require('gulp');
const htmlmin = require('gulp-htmlmin');
const minifyCSS = require('gulp-clean-css');
const minifyJSON = require('gulp-jsonminify');
const notify = require('gulp-notify');
const path = require('path');
const paths = require('../paths');
const plumber = require('gulp-plumber');
const runSequence = require('run-sequence');
const sass = require('gulp-sass');
const sourcemaps = require('gulp-sourcemaps');
const symlink = require("symlink-or-copy").sync;
const typescript = require('gulp-typescript');
const buildConfig = require('../build-config');

const htmlMinifierOptions = {
  collapseWhitespace: true,
  conservativeCollapse: true,
  minifyCSS: true,
};

let typescriptCompiler;
gulp.task('build-scripts', () => {
  if (!typescriptCompiler) {
    typescriptCompiler = typescript.createProject('tsconfig.json');
  }
  const errorHandler = buildConfig.watch ? notify.onError('Error: <%= error.message %>') : (error) => {
    throw new Error(error.message);
  };
  return typescriptCompiler.src()
    .pipe(plumber({errorHandler}))
    .pipe(changed(paths.output, {extension: '.js'}))
    .pipe(sourcemaps.init({loadMaps: true}))
    .pipe(typescriptCompiler())
    .pipe(sourcemaps.write('.', {includeContent: false, sourceRoot: '/src'}))
    .pipe(gulp.dest(paths.output));
});

gulp.task('build-index', () => {
  return gulp.src('index.html')
    .pipe(plumber({errorHandler: notify.onError('HTML: <%= error.message %>')}))
    .pipe(changed(paths.output, {extension: '.html'}))
    .pipe(htmlmin(htmlMinifierOptions))
    .pipe(gulp.dest(paths.webAdminRoot));
});

gulp.task('build-html', () => {
  return gulp.src(paths.html)
    .pipe(plumber({errorHandler: notify.onError('HTML: <%= error.message %>')}))
    .pipe(changed(paths.output, {extension: '.html'}))
    .pipe(htmlmin(htmlMinifierOptions))
    .pipe(gulp.dest(paths.output));
});

gulp.task('build-css', () => {
  return gulp.src(paths.scss)
    .pipe(plumber({errorHandler: notify.onError('SCSS: <%= error.message %>')}))
    .pipe(sourcemaps.init())
    .pipe(sass())
    .pipe(concat('style.css'))
    .pipe(minifyCSS())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(paths.output));
});

gulp.task('build-locales', () => {
  return gulp.src(path.join(paths.locales, '**/*.json'), {base: paths.resourcesRoot})
    .pipe(plumber({errorHandler: notify.onError('Locales: <%= error.message %>')}))
    .pipe(changed(paths.webAdminResources))
    .pipe(minifyJSON())
    .pipe(gulp.dest(paths.webAdminResources));
});

gulp.task('copy-jspm-config', () => {
  return gulp.src(paths.jspmConfig)
    .pipe(plumber({errorHandler: notify.onError('JSPM config: <%= error.message %>')}))
    .pipe(htmlmin({collapseWhitespace: true}))
    .pipe(gulp.dest(paths.webAdminRoot));
});

gulp.task('symlink-jspm-packages', () => {
  return symlink('jspm_packages', path.join(paths.webRoot, 'jspm_packages'));
});

gulp.task('symlink-dist', () => {
  return symlink('dist', path.join(paths.webAdminRoot, 'dist'));
});

gulp.task('build', (callback) => {
  return runSequence(
    'clean',
    ['build-scripts', 'build-index', 'build-html', 'build-css', 'build-locales', 'symlink-jspm-packages'],
    ['symlink-dist', 'copy-jspm-config'],
    callback
  );
});
