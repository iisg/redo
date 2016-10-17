var appRoot = 'src/';
var outputRoot = '../../web/';

module.exports = {
  root: appRoot,
  scripts: [appRoot + '**/*.ts', '!' + appRoot + '**/*.spec.ts'],
  html: appRoot + '**/*.html',
  jspmConfig: 'jspm.config.js',
  scss: appRoot + '**/*.scss',
  output: outputRoot + 'admin/',
  outputRoot: outputRoot,
  lintReports: '../../var/reports/lints/'
};
