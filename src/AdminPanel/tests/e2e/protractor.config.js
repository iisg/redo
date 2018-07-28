exports.config = {
  framework: 'jasmine',
  specs: ['LoginTests.js', 'MetadataTypeTests.js', 'ResourceTypeTests.js', 'ResourceTests.js', 'TransitionTests.js'],
  getPageTimeout: 120000,
  allScriptsTimeout: 120000,
  capabilities: {
    'browserName': 'chrome',
    chromeOptions: {
      args: [ "--headless", "--disable-gpu", "--window-size=800x600" ]
    }
  },
  onPrepare: function(){
      browser.ignoreSynchronization = true;
      getPageTimeout: 120000;
      allScriptsTimeout: 120000;
      jasmine.DEFAULT_TIMEOUT_INTERVAL = 180000;

   },
}
