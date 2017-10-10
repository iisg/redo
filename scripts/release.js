var project = require("./repeka");
var version = require('./version');
var chalk = require('chalk');
const ora = require('ora');
var fs = require('fs-extra');
var path = require('path');
var async = require('async');
var del = require('del');
var exec = require('child_process').exec;
var preprocess = require('preprocess');

project.printAsciiLogoAndVersion();

var releaseFilename = process.env.RELEASE_FILENAME || 'repeka-' + version.text + '-' + version.full.hash + '.tar.gz';

console.log('');

function start() {
  clearVendorDirectory();
}

function clearVendorDirectory() {
  var spinner = ora({text: 'Cleaning vendor directory.', color: 'yellow'}).start();
  del('vendor/**/.git')
    .then(() => {
      spinner.succeed('Vendor directory cleaned.');
      clearReleaseDirectory();
    })
    .catch((err) => {
      console.log(err);
      spinner.fail();
    })
}

function clearReleaseDirectory() {
  var spinner = ora({text: 'Deleting release directory.', color: 'yellow'}).start();
  fs.remove('release/', function (err) {
    if (err) {
      spinner.fail();
      console.error(err);
    } else {
      spinner.succeed('Release directory deleted.');
      copyToReleaseDirectory();
    }
  });
}

function copyToReleaseDirectory() {
  var spinner = ora({text: 'Copying application files.', color: 'yellow'}).start();
  var calls = [];
  [
    'app/',
    'bin/',
    'docker/',
    'src/Repeka/Application',
    'src/Repeka/Domain',
    'vendor/',
    'web/',
  ].forEach(function (filename) {
    calls.push(function (callback) {
      fs.mkdirsSync('release/' + filename);
      fs.copy(filename, 'release/' + filename, function (err) {
        if (!err) {
          callback(err);
        } else {
          callback(null, filename);
        }
      });
    });
  });
  async.series(calls, function (err) {
    if (err) {
      spinner.fail();
      console.error(err);
    } else {
      createRequriedDirectories();
      clearLocalConfigFiles();
      copySingleRequiredFiles();
      preprocessSources();
      spinner.succeed('Application files copied.');
      deleteUnwantedSources();
    }
  });
}

function createRequriedDirectories() {
  ['var/cache', 'var/logs', 'var/sessions'].forEach(function (dirname) {
    fs.mkdirsSync('release/' + dirname);
  });
}

function preprocessSources() {
  preprocess.preprocessFileSync('release/docker/repeka/Dockerfile', 'release/docker/repeka/Dockerfile', {}, {type: 'shell'});
}

function copySingleRequiredFiles() {
  fs.copySync('var/bootstrap.php.cache', 'release/var/bootstrap.php.cache');
  fs.copySync('src/.htaccess', 'release/src/.htaccess');
  fs.copySync('src/.htaccess', 'release/var/.htaccess');
}

function clearLocalConfigFiles() {
  del.sync([
    'release/docker/.env',
    'release/apache2-ssl/server.crt',
    'release/apache2-ssl/server.key',
    'release/app/config/config_local.yml',
    'release/app/config/config_dev.yml',
    'release/app/config/config_test.yml',
    'release/app/config/routing_dev.yml',
  ]);
}

function deleteUnwantedSources() {
  var spinner = ora({text: 'Deleting unneeded sources.', color: 'yellow'}).start();
  del([
    'release/backend/vendor/**/test/**',
    'release/backend/vendor/**/tests/**',
    'release/backend/vendor/**/doc/**',
    'release/backend/vendor/**/docs/**',
    'release/backend/vendor/**/.idea/**',
    'release/backend/vendor/**/img/**',
    'release/backend/vendor/**/composer.json',
    'release/backend/vendor/**/composer.lock',
    'release/backend/vendor/**/*.md',
    'release/backend/vendor/**/LICENSE',
    'release/backend/vendor/**/*.dist',
  ])
    .then(() => {
      spinner.succeed('Unneeded sources deleted.');
      copyJsDependencies();
    })
    .catch((err) => {
      console.log(err);
      spinner.fail();
    })
}

function copyJsDependencies() {
  var spinner = ora({text: 'Copying JS dependencies.', color: 'yellow'}).start();
  del.sync('release/web/jspm_packages');
  fs.mkdirsSync('release/web/jspm_packages');
  fs.copy('src/AdminPanel/jspm_packages', 'release/web/jspm_packages', function (err) {
    if (err) {
      spinner.fail();
      console.error(err);
    } else {
      spinner.succeed('JS dependencies copied.');
      includeInstallationDocs();
    }
  });
}

function includeInstallationDocs() {
  var spinner = ora({text: 'Including installation docs.', color: 'yellow'}).start();
  exec('git fetch && git show origin/docs:Installation.pdf > release/Installation.pdf && git show origin/docs:Upgrading.pdf > release/Upgrading.pdf', function (err) {
    if (err) {
      spinner.fail();
      console.error(err);
    } else {
      spinner.succeed('Installation docs included.');
      createZipArchive();
    }
  });
}

function createZipArchive() {
  var spinner = ora({text: 'Creating release archive.', color: 'yellow'}).start();
  exec('tar -czf ' + releaseFilename + ' release --transform=\'s/release\\/\\{0,1\\}//g\'', function (err) {
    if (err) {
      spinner.fail();
      console.log(err);
    } else {
      spinner.succeed('Release archive created.');
      console.log('');
      console.log("Package: " + chalk.green(releaseFilename));
    }
  });
}

start();
