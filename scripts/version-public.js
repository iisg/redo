var fs = require('fs');
var version = require('./version');
var contents =
  `# Config generated automatically by running npm run build

repeka:
  version: '${version.text}'
  version_full: '${version.full}'
`;
fs.writeFileSync('app/config/config_build.yml', contents);
