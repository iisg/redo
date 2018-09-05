exports.config = {
  framework: 'jasmine2',
  specs: ['LoginTests.js', 'MetadataTypeTests.js', 'ResourceTypeTests.js'],
  //specs: ['LoginTests.js', 'MetadataTypeTests.js', 'ResourceTypeTests.js', 'ResourceTests.js', 'TransitionTests.js'],
  getPageTimeout: 120000,
  allScriptsTimeout: 120000,
  capabilities: {
    'browserName': 'chrome',
    chromeOptions: {
		args: [ "--headless", '--no-sandbox']
    }
  },
  onPrepare: function(){
      browser.ignoreSynchronization = true;
      getPageTimeout: 120000;
      allScriptsTimeout: 120000;
      jasmine.DEFAULT_TIMEOUT_INTERVAL = 180000;
	  
	  var jasmineReporters = require('jasmine-reporters');
	  jasmine.getEnv().addReporter(new jasmineReporters.JUnitXmlReporter({
        consolidateAll: true,
        savePath: './reports',
        filePrefix: 'xmloutput'
    }));
	
	var browserName, browserVersion;
     var capsPromise = browser.getCapabilities();
 
     capsPromise.then(function (caps) {
        browserName = caps.get('browserName');
        browserVersion = caps.get('version');
        platform = caps.get('platform');
 
        var HTMLReport = require('protractor-html-reporter-2');
 
        testConfig = {
            reportTitle: 'Protractor Test Execution Report',
            outputPath: './reports',
            outputFilename: 'ProtractorTestReport',
            screenshotPath: './screenshots',
            testBrowser: browserName,
            browserVersion: browserVersion,
            modifiedSuiteName: false,
            screenshotsOnlyOnFailure: true,
            testPlatform: platform
        };
        new HTMLReport().from('./reports/xmloutput.xml', testConfig);
    });
   },
}

//args: [ "--headless", "--disable-gpu", "--window-size=800x600", '--no-sandbox','-disable-popup-blocking', '-disable-dev-shm-usage' ]
