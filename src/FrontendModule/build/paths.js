var appRoot = 'src/';
var outputRoot = '../../web/admin/';

module.exports = {
  root: appRoot,
  scripts: [appRoot + '**/*.ts', '!' + appRoot + '**/*.spec.ts'],
  html: appRoot + '**/*.html',
  jspmConfig: 'jspm.config.js',
  scss: appRoot + '**/*.scss',
  output: outputRoot
}
