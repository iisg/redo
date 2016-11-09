var fs = require('fs');
var version = require('./version');
var structure =
  'parameters:\n' +
  '  application_version: ';

fs.writeFile('app/config/config_build.yml', structure + version.text);
