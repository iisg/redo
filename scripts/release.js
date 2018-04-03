'use strict';

const project = require("./repeka");
const version = require('./version');

const async = require('async');
const chalk = require('chalk');
const del = require('del');
const dos2unix = require('@dpwolfe/dos2unix').dos2unix;
const exec = require('child_process').exec;
const fs = require('fs-extra');
const ora = require('ora');
const preprocess = require('preprocess');

project.printAsciiLogoAndVersion();

const releaseFilename = process.env.RELEASE_FILENAME || 'repeka-' + version.text + '-' + version.full.hash + '.tar.gz';

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
    'src/Repeka/Plugins',
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
      createVarDirectoryStructure();
      copySingleRequiredFiles();
      clearLocalConfigFiles();
      preprocessSources();
      spinner.succeed('Application files copied.');
      convertToUnixLineEndings();
    }
  });
}

function createVarDirectoryStructure() {
  [
    'var/backups',
    'var/cache',
    'var/config',
    'var/logs',
    'var/sessions',
    'var/ssl',
    'var/uploads',
    'var/volumes/elasticsearch',
    'var/volumes/metrics/data/whisper',
    'var/volumes/metrics/data/elasticsearch',
    'var/volumes/metrics/data/grafana',
    'var/volumes/metrics/log/graphite/webapp',
    'var/volumes/metrics/log/elasticsearch',
    'var/volumes/postgres'
  ].forEach(function (dirname) {
    fs.mkdirsSync('release/' + dirname);
  });
}

function copySingleRequiredFiles() {
  [
    'src/.htaccess',
    'var/.htaccess',
    'var/config/config_local.yml.sample',
    'var/config/docker.env.sample',
    'var/config/user_data_mapping.json.sample',
    'var/ssl/generate-self-signed-certs.sh',
    'var/volumes/initialize-directory-structure.sh',
  ].forEach((filepath) => {
    fs.copySync(filepath, 'release/' + filepath);
  });
}

function clearLocalConfigFiles() {
  del.sync([
    'release/docker/.env',
    'release/docker/jenkins-agent',
    'release/app/config/config_dev.yml',
    'release/app/config/config_test.yml',
    'release/app/config/routing_dev.yml',
    'release/**/.gitignore',
    'release/composer.*',
    'release/**/package.json',
  ]);
}

function preprocessSources() {
  preprocess.preprocessFileSync('release/docker/repeka/Dockerfile', 'release/docker/repeka/Dockerfile', {}, {type: 'shell'});
}

function convertToUnixLineEndings() {
  var spinner = ora({text: 'Converting all line endings to unix.', color: 'yellow'}).start();
  new dos2unix()
    .on('error', (err) => {
      console.log(err);
      spinner.fail();
    })
    .on('end', () => {
      spinner.succeed('All line endings converted to unix.');
      deleteUnwantedSources();
    })
    .process(['release/app/**', 'release/docker/**', 'release/src/**', 'release/var/**']);
}

function deleteUnwantedSources() {
  var spinner = ora({text: 'Deleting unneeded sources.', color: 'yellow'}).start();
  del([
    'release/**/composer.json',
    'release/**/composer.lock',
    'release/vendor/**/*.md',
    'release/vendor/**/*.dist',
    'release/vendor/**/.idea/**',
    'release/vendor/**/doc/**',
    'release/vendor/**/docs/**',
    'release/vendor/**/img/**',
    'release/vendor/**/LICENSE',
  ]).then(() => {
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
  exec('git fetch ' +
    '&& git show origin/docs:Installation.pdf > release/Installation.pdf ' +
    '&& git show origin/docs:Upgrading.pdf > release/Upgrading.pdf ' +
    '&& git show origin/docs:Changelog.pdf > release/Changelog.pdf', function (err) {
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
      var fileSizeInBytes = fs.statSync(releaseFilename).size;
      console.log('Size: ' + Math.round(fileSizeInBytes / 1024) + 'kB (' + Math.round(fileSizeInBytes * 10 / 1024 / 1024) / 10 + 'MB)');
    }
  });
}

start();
