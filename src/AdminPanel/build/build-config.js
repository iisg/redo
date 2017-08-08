const argv = require('yargs').argv;

module.exports = {
  failOnTSError: argv._.indexOf('watch') >= 0 || argv._.indexOf('build-scripts') >= 0
};
