var path = require('path');

var appRoot = 'src/';
var resourcesRoot = 'res/';
var outputRoot = 'dist/';
var webRoot = '../../web/';

module.exports = {
  root: appRoot,
  resourcesRoot: resourcesRoot,
  scripts: [path.join(appRoot, '**/*.ts'), '!' + path.join(appRoot, '**/*.spec.ts')],
  html: path.join(appRoot, '**/*.html'),
  jspmConfig: 'jspm.config.js',
  scss: path.join(appRoot, '**/*.scss'),
  locales: path.join(resourcesRoot, 'locales'),
  output: outputRoot,
  webRoot: webRoot,
  webAdminRoot: path.join(webRoot, 'admin/'),
  webAdminResources: path.join(webRoot, 'admin/res/'),
  icons: '../../src/Repeka/DeveloperBundle/Resources/icons/',
  lintReports: '../../var/reports/lints/',
  themes: '../../app/Resources/views',
};
