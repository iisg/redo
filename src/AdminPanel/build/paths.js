var path = require('path');

var appRoot = 'src/';
var outputRoot = 'dist/';
var webRoot = '../../web/';

module.exports = {
  root: appRoot,
  scripts: [path.join(appRoot, '**/*.ts'), '!' + path.join(appRoot, '**/*.spec.ts')],
  html: path.join(appRoot, '**/*.html'),
  jspmConfig: 'jspm.config.js',
  scss: path.join(appRoot, '**/*.scss'),
  output: outputRoot,
  webRoot: webRoot,
  webAdminRoot: path.join(webRoot, 'admin/'),
  lintReports: '../../var/reports/lints/'
};
