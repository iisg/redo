var fs = require('fs');
var version = require('./version');
var structure =
  '# Config generated automatically by running npm run build\n\n' +
  'repeka:\n' +
  '  version: ';

fs.writeFile('app/config/config_build.yml', structure + version.text);
