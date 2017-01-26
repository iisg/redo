var project = require("./repeka");
var version = require('./version');
var chalk = require('chalk');
const ora = require('ora');
var fs = require('fs-extra');
var path = require('path');
var async = require('async');
var del = require('del');
var exec = require('child_process').exec;

project.printAsciiLogoAndVersion();

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
      spinner.succeed('Application files copied.');
      copyJsDependencies();
    }
  });
}

function createRequriedDirectories() {
  ['var/cache', 'var/logs', 'var/sessions'].forEach(function (dirname) {
    fs.mkdirsSync('release/' + dirname);
  });
}

function copySingleRequiredFiles() {
  fs.copySync('var/bootstrap.php.cache', 'release/var/bootstrap.php.cache');
  fs.copySync('src/.htaccess', 'release/src/.htaccess');
  fs.copySync('src/.htaccess', 'release/var/.htaccess');
}

function clearLocalConfigFiles() {
  del.sync([
    'release/app/config/config_local.yml',
    'release/app/config/config_dev.yml',
    'release/app/config/config_test.yml',
    'release/app/config/routing_dev.yml',
  ]);
}

function copyJsDependencies() {
  var spinner = ora({text: 'Copying JS dependencies.', color: 'yellow'}).start();
  fs.mkdirsSync('release/web/jspm_packages');
  fs.copy('src/AdminPanel/jspm_packages', 'release/web/jspm_packages', function (err) {
    if (err) {
      spinner.fail();
      console.error(err);
    } else {
      spinner.succeed('JS dependencies copied.');
      dumpDockerConfiguration();
    }
  });
}

function dumpDockerConfiguration() {
  var spinner = ora({text: 'Dumping docker configuration.', color: 'yellow'}).start();
  exec('git fetch && git archive -o release/docker-configuration.tar.gz origin/docker', function (err) {
    if (err) {
      spinner.fail();
      console.error(err);
    } else {
      spinner.succeed('Docker configuraiton dumped.');
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
  var filename = 'repeka-' + version.text + '-' + version.full.hash + '.tar.gz'
  exec('tar -czf ' + filename + ' release --transform=\'s/release\\/\\{0,1\\}//g\'', function (err) {
    if (err) {
      spinner.fail();
      console.log(err);
    } else {
      spinner.succeed('Release archive created.');
      console.log('');
      console.log("Package: " + chalk.green(filename));
    }
  });
}

start();
