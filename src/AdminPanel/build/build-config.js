const argv = require('yargs').argv;

module.exports = {
  watch: argv._.indexOf('watch') >= 0
};
