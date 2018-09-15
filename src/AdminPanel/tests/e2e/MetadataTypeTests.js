describe('Metadata Type Tests', function() {

  function waitForElement (locator) {
	   browser.driver.wait(function () {
			return locator.isPresent()
	   }, 10000);
  }

  beforeAll(function() {
		browser.get('https://repekadev.fslab.agh.edu.pl/');

	  var loginButton = element(by.cssContainingText('span', 'Zaloguj'));
	    loginButton.click();

	    var nameField = element(by.id('username'));
	    var passwordField = element(by.id('password'));
	    var loginIcons = element.all(by.className('login-box-icon'));
	    nameField.clear();
		passwordField.clear();
		nameField.sendKeys('admin');
	    passwordField.sendKeys('admin');
	    loginIcons.get(2).click();
  });

  beforeEach(function() {
		browser.get('https://repekadev.fslab.agh.edu.pl/admin/metadata/books');
		browser.driver.manage().window().maximize();
  });

  it('Try to add without name', function() {
		waitForElement(element(by.cssContainingText('span', 'Dodaj')));
		var addMetadataTypeButton = element(by.cssContainingText('span', 'Dodaj'));
		addMetadataTypeButton.click();

		var fields = element.all(by.className('form-control au-target'));
		var addButton = element(by.buttonText('Dodaj'));
		fields.get(1).sendKeys('Nazwa_Testowanie_Automatyczne');
		fields.get(2).sendKeys('Nazwa_Testowanie_Automatyczne');
		addButton.click();

		var errorMessage = element(by.className('help-block validation-message'));
		expect(errorMessage.getText()).toContain('To pole jest wymagane.');
  });

  it('Try to add without Polish display name', function() {
	    alertDialog = browser.switchTo().alert();
		alertDialog.accept();
		waitForElement(element(by.cssContainingText('span', 'Dodaj')));
		var addMetadataTypeButton = element(by.cssContainingText('span', 'Dodaj'));
		addMetadataTypeButton.click();

		var fields = element.all(by.className('form-control au-target'));
		var addButton = element(by.buttonText('Dodaj'));
		fields.get(0).sendKeys('Nazwa_Testowanie_Automatyczne');
		fields.get(2).sendKeys('Nazwa_Testowanie_Automatyczne');
		addButton.click();

		var errorMessage = element(by.className('help-block validation-message'));
		expect(errorMessage.getText()).toContain('Nazwa wyświetlana musi mieć wartość we wszystkich językach.');
  });

  it('Try to add without English display name', function() {
	  	alertDialog = browser.switchTo().alert();
		alertDialog.accept();
		waitForElement(element(by.cssContainingText('span', 'Dodaj')));
		var addMetadataTypeButton = element(by.cssContainingText('span', 'Dodaj'));
		addMetadataTypeButton.click();

		var fields = element.all(by.className('form-control au-target'));
		var addButton = element(by.buttonText('Dodaj'));
		fields.get(0).sendKeys('Nazwa_Testowanie_Automatyczne');
		fields.get(1).sendKeys('Nazwa_Testowanie_Automatyczne');
		addButton.click();

		var errorMessage = element(by.className('help-block validation-message'));
		expect(errorMessage.getText()).toContain('Nazwa wyświetlana musi mieć wartość we wszystkich językach.');
  });

  it('Add metadata type', function() {
	  	alertDialog = browser.switchTo().alert();
		alertDialog.accept();
		waitForElement(element(by.cssContainingText('span', 'Dodaj')));
		var addMetadataTypeButton = element(by.cssContainingText('span', 'Dodaj'));
		addMetadataTypeButton.click();

		var fields = element.all(by.className('form-control au-target'));
		var addButton = element(by.buttonText('Dodaj'));
		fields.get(0).sendKeys('Nazwa_Testowanie_Automatyczne');
		fields.get(1).sendKeys('Nazwa_Testowanie_Automatyczne');
		fields.get(2).sendKeys('Nazwa_Testowanie_Automatyczne');
		addButton.click();

	    waitForElement(element(by.className('metadata-details au-animate fade-inup-outup')));
		var parameters = element(by.className('metadata-details au-animate fade-inup-outup'));
		expect(parameters.getText()).toContain('Nazwa_Testowanie_Automatyczne');
  });

  it('Edit metadata Polish display name', function() {
		waitForElement(element(by.linkText('Nazwa_Testowanie_Automatyczne')));
		var newMetadataTypeRowLink = element(by.linkText('Nazwa_Testowanie_Automatyczne'));
		newMetadataTypeRowLink.click();

		waitForElement(element(by.cssContainingText('span', 'Edytuj')));
		var editButton = element(by.cssContainingText('span', 'Edytuj'));
		editButton.click();

		waitForElement(element(by.className('form-control au-target')));
		var fields = element.all(by.className('form-control au-target'));
		fields.get(1).clear();
		fields.get(1).sendKeys('Edycja_Nazwy');
		var confirmButton = element(by.buttonText('Zatwierdź'));
		confirmButton.click();

		waitForElement(element(by.cssContainingText('span', 'Edytuj')));
	    waitForElement(element(by.className('metadata-details au-animate fade-inup-outup')));
		var parameters = element(by.className('metadata-details au-animate fade-inup-outup'));
		expect(parameters.getText()).toContain('Edycja_Nazwy');
  });

  it('Edit metadata English display name', function() {
		waitForElement(element(by.linkText('nazwa_testowanie_automatyczne')));
		var newMetadataTypeRowLink = element(by.linkText('nazwa_testowanie_automatyczne'));
		newMetadataTypeRowLink.click();

		waitForElement(element(by.cssContainingText('span', 'Edytuj')));
		var editButton = element(by.cssContainingText('span', 'Edytuj'));
		editButton.click();

		waitForElement(element(by.className('form-control au-target')));
		var fields = element.all(by.className('form-control au-target'));
		fields.get(2).clear();
		fields.get(2).sendKeys('Edycja_NazwyENG');

		var confirmButton = element(by.buttonText('Zatwierdź'));
		confirmButton.click();

		waitForElement(element(by.cssContainingText('span', 'Edytuj')));
		var languageMenu = element(by.className('au-target flag-icon-xs'));
		languageMenu.click();

		var englishOption = element(by.linkText('English'));
		englishOption.click();

		waitForElement(element(by.cssContainingText('span', 'Edit')));
	    waitForElement(element(by.className('metadata-details au-animate fade-inup-outup')));
		var parameters = element(by.className('metadata-details au-animate fade-inup-outup'));
		expect(parameters.getText()).toContain('Edycja_NazwyENG');

		var languageMenu = element(by.className('au-target flag-icon-xs'));
		languageMenu.click();
		var polishOption = element(by.linkText('Polski'));
		polishOption.click();
  });

  it('Delete metadata type', function() {
		waitForElement(element(by.linkText('nazwa_testowanie_automatyczne')));
		var newMetadataTypeRowLink = element(by.linkText('nazwa_testowanie_automatyczne'));
		newMetadataTypeRowLink.click();
		waitForElement(element(by.cssContainingText('span', 'Usuń')));
		var deleteButton = element(by.cssContainingText('span', 'Usuń'));
		deleteButton.click();

		var confirmButton = element.all(by.className('swal2-confirm toggle-button red'));
		confirmButton.click();

		waitForElement(element(by.cssContainingText('span', 'rodzaj metadanej')));
		expect(element(by.linkText('nazwa_testowanie_automatyczne')).isPresent()).toBe(false);
  });
});