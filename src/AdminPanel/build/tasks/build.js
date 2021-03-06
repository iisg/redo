'use strict';

const changed = require('gulp-changed');
const concat = require('gulp-concat');
const convert = require('gulp-convert');
const gulpif = require('gulp-if');
const gulp = require('gulp');
const fs = require('fs');
const htmlmin = require('gulp-htmlmin');
const minifyCSS = require('gulp-clean-css');
const postcss = require('gulp-postcss');
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
  const errorHandler = buildConfig.failOnTSError ? notify.onError('Error: <%= error.message %>') : (error) => {
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
    .pipe(concat('repeka.css'))
    .pipe(minifyCSS())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(paths.webRoot));
});

gulp.task('build-themes-css', (callback) => {
  const availableThemes = fs.readdirSync(paths.themes);
  const buildStyles = () => {
    if (availableThemes.length) {
      const template = availableThemes.pop();
      gulp.src(path.join(paths.themes, template, '*.scss'))
        .pipe(plumber({errorHandler: notify.onError('SCSS: <%= error.message %>')}))
        .pipe(sourcemaps.init())
        .pipe(sass())
        .pipe(gulpif(file => file.path.endsWith("high-contrast-styles.css"), postcss([require('postcss-colors-only')])))
        .pipe(concat(template + `.css`))
        .pipe(minifyCSS())
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(paths.webRoot + '/themes'))
        .on('end', buildStyles);
    } else {
      callback();
    }
  };
  buildStyles();
});

gulp.task('build-locales', () => {
  //noinspection JSCheckFunctionSignatures
  return gulp.src(path.join(paths.locales, '**/*.yml'))
    .pipe(plumber({errorHandler: notify.onError('Locales: <%= error.message %>')}))
    .pipe(changed(paths.webAdminResources))
    .pipe(convert({from: 'yml', to: 'json'}))
    .pipe(minifyJSON())
    .pipe(gulp.dest(paths.webAdminResources + '/locales'));
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
    ['build-scripts', 'build-css', 'build-themes-css', 'symlink-jspm-packages'],
    'copy-jspm-config',
    'symlink-dist',
    'bundle',
    callback
  );
});
