var describe = require('git-describe');

var version = describe.gitDescribeSync(__dirname);
var semver = version.semver;
var appVersion = [semver.major, semver.minor, semver.patch + version.distance].join('.');

module.exports = {
  text: appVersion,
  full: version
};
