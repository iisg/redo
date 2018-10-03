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

  it('Try to add submetadata without name', function() {
		waitForElement(element(by.linkText('Nazwa_Testowanie_Automatyczne')));
		var newMetadataTypeRowLink = element(by.linkText('Nazwa_Testowanie_Automatyczne'));
		newMetadataTypeRowLink.click();

		waitForElement(element(by.cssContainingText('a', 'Rodzaje podmetadanych')));
		var submetadataLink = element(by.cssContainingText('a', 'Rodzaje podmetadanych'));
		submetadataLink.click();

		waitForElement(element(by.cssContainingText('span', 'Dodaj')));
		var addSubmetadataButton = element(by.cssContainingText('span', 'Dodaj'));
		addSubmetadataButton.click();

		waitForElement(element(by.cssContainingText('span', 'Dodaj nowy')));
		var addNewSubmetadataButton = element(by.cssContainingText('span', 'Dodaj nowy'));
		addNewSubmetadataButton.click();

		var fields = element.all(by.className('form-control au-target'));
		var addButton = element(by.buttonText('Dodaj'));
		fields.get(1).sendKeys('SUBNazwa_Testowanie_Automatyczne');
		fields.get(2).sendKeys('SUBNazwa_Testowanie_Automatyczne');
		addButton.click();

		var errorMessage = element(by.className('help-block validation-message'));
		expect(errorMessage.getText()).toContain('To pole jest wymagane.');
  });

  it('Try to add submetadata without display name', function() {
	  	alertDialog = browser.switchTo().alert();
		alertDialog.accept();

		waitForElement(element(by.linkText('Nazwa_Testowanie_Automatyczne')));
		var newMetadataTypeRowLink = element(by.linkText('Nazwa_Testowanie_Automatyczne'));
		newMetadataTypeRowLink.click();

		waitForElement(element(by.cssContainingText('a', 'Rodzaje podmetadanych')));
		var submetadataLink = element(by.cssContainingText('a', 'Rodzaje podmetadanych'));
		submetadataLink.click();

		waitForElement(element(by.cssContainingText('span', 'Dodaj')));
		var addSubmetadataButton = element(by.cssContainingText('span', 'Dodaj'));
		addSubmetadataButton.click();

		waitForElement(element(by.cssContainingText('span', 'Dodaj nowy')));
		var addNewSubmetadataButton = element(by.cssContainingText('span', 'Dodaj nowy'));
		addNewSubmetadataButton.click();

		var fields = element.all(by.className('form-control au-target'));
		var addButton = element(by.buttonText('Dodaj'));
		fields.get(0).sendKeys('SUBNazwa_Testowanie_Automatyczne');
		addButton.click();

		var errorMessage = element(by.className('help-block validation-message'));
		expect(errorMessage.getText()).toContain('Nazwa wyświetlana musi mieć wartość we wszystkich językach.');
  });

  it('Add submetadata', function() {
	  	alertDialog = browser.switchTo().alert();
		alertDialog.accept();

		waitForElement(element(by.linkText('Nazwa_Testowanie_Automatyczne')));
		var newMetadataTypeRowLink = element(by.linkText('Nazwa_Testowanie_Automatyczne'));
		newMetadataTypeRowLink.click();

		waitForElement(element(by.cssContainingText('a', 'Rodzaje podmetadanych')));
		var submetadataLink = element(by.cssContainingText('a', 'Rodzaje podmetadanych'));
		submetadataLink.click();

		waitForElement(element(by.cssContainingText('span', 'Dodaj')));
		var addSubmetadataButton = element(by.cssContainingText('span', 'Dodaj'));
		addSubmetadataButton.click();

		waitForElement(element(by.cssContainingText('span', 'Dodaj nowy')));
		var addNewSubmetadataButton = element(by.cssContainingText('span', 'Dodaj nowy'));
		addNewSubmetadataButton.click();

		var fields = element.all(by.className('form-control au-target'));
		var addButton = element(by.buttonText('Dodaj'));
		fields.get(0).sendKeys('SUBNazwa_Testowanie_Automatyczne');
		fields.get(1).sendKeys('SUBNazwa_Testowanie_Automatyczne');
		fields.get(2).sendKeys('SUBNazwa_Testowanie_Automatyczne');
		addButton.click();

		waitForElement(element(by.cssContainingText('span', 'Edytuj')));
	    waitForElement(element(by.className('metadata-details au-animate fade-inup-outup')));
		var parameters = element(by.className('metadata-details au-animate fade-inup-outup'));
		expect(parameters.getText()).toContain('SUBNazwa_Testowanie_Automatyczne');
  });

  it('Try to edit submetadata by removing Polish name', function() {
		waitForElement(element(by.linkText('Nazwa_Testowanie_Automatyczne')));
		var newMetadataTypeRowLink = element(by.linkText('Nazwa_Testowanie_Automatyczne'));
		newMetadataTypeRowLink.click();

		waitForElement(element(by.cssContainingText('span', 'Edytuj')));
		var editButton = element(by.cssContainingText('span', 'Edytuj'));
		editButton.click();

		waitForElement(element(by.cssContainingText('a', 'Rodzaje podmetadanych')));
		var submetadataLink = element(by.cssContainingText('a', 'Rodzaje podmetadanych'));
		submetadataLink.click();

		waitForElement(element(by.linkText('SUBNazwa_Testowanie_Automatyczne')));
		var submetadataTypeRowLink = element(by.linkText('SUBNazwa_Testowanie_Automatyczne'));
		submetadataTypeRowLink.click();
		browser.sleep(1000);

		waitForElement(element(by.cssContainingText('span', 'Edytuj')));
		var editButton = element(by.cssContainingText('span', 'Edytuj'));
		editButton.click();
		browser.sleep(1000);

		waitForElement(element(by.className('form-control au-target')));
		var fields = element.all(by.className('form-control au-target'));
		fields.get(1).clear();

		var confirmButton = element(by.buttonText('Zatwierdź'));
		confirmButton.click();

		var errorMessage = element(by.className('help-block validation-message'));
		expect(errorMessage.getText()).toContain('Nazwa wyświetlana musi mieć wartość we wszystkich językach.');
  });

  it('Edit submetadata Polish name', function() {
	  	alertDialog = browser.switchTo().alert();
		alertDialog.accept();

		waitForElement(element(by.linkText('Nazwa_Testowanie_Automatyczne')));
		var newMetadataTypeRowLink = element(by.linkText('Nazwa_Testowanie_Automatyczne'));
		newMetadataTypeRowLink.click();

		waitForElement(element(by.cssContainingText('span', 'Edytuj')));
		var editButton = element(by.cssContainingText('span', 'Edytuj'));
		editButton.click();

		waitForElement(element(by.cssContainingText('a', 'Rodzaje podmetadanych')));
		var submetadataLink = element(by.cssContainingText('a', 'Rodzaje podmetadanych'));
		submetadataLink.click();

		waitForElement(element(by.linkText('SUBNazwa_Testowanie_Automatyczne')));
		var submetadataTypeRowLink = element(by.linkText('SUBNazwa_Testowanie_Automatyczne'));
		submetadataTypeRowLink.click();
		browser.sleep(1000);

		waitForElement(element(by.cssContainingText('span', 'Edytuj')));
		var editButton = element(by.cssContainingText('span', 'Edytuj'));
		editButton.click();
		browser.sleep(1000);

		waitForElement(element(by.className('form-control au-target')));
		var fields = element.all(by.className('form-control au-target'));
		fields.get(1).clear();
		fields.get(1).sendKeys('SUBNazwaNEW_Testowanie_Automatyczne');

		var confirmButton = element(by.buttonText('Zatwierdź'));
		confirmButton.click();

		waitForElement(element(by.cssContainingText('span', 'Edytuj')));
	    waitForElement(element(by.className('metadata-details au-animate fade-inup-outup')));
		var parameters = element(by.className('metadata-details au-animate fade-inup-outup'));
		expect(parameters.getText()).toContain('SUBNazwaNEW_Testowanie_Automatyczne');
  });

  it('Try to delete metadata having a submetadata', function() {
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

		waitForElement(element(by.cssContainingText('span', 'rodzaj metadanej ma podmetadane')));
  });

  it('Delete submetadata', function() {
		waitForElement(element(by.linkText('Nazwa_Testowanie_Automatyczne')));
		var newMetadataTypeRowLink = element(by.linkText('Nazwa_Testowanie_Automatyczne'));
		newMetadataTypeRowLink.click();

		waitForElement(element(by.cssContainingText('span', 'Edytuj')));
		var editButton = element(by.cssContainingText('span', 'Edytuj'));
		editButton.click();

		waitForElement(element(by.cssContainingText('a', 'Rodzaje podmetadanych')));
		var submetadataLink = element(by.cssContainingText('a', 'Rodzaje podmetadanych'));
		submetadataLink.click();

		waitForElement(element(by.linkText('SUBNazwaNEW_Testowanie_Automatyczne')));
		var submetadataTypeRowLink = element(by.linkText('SUBNazwaNEW_Testowanie_Automatyczne'));
		submetadataTypeRowLink.click();
		browser.sleep(1000);

		waitForElement(element(by.cssContainingText('span', 'Usuń')));
		var deleteButton = element(by.cssContainingText('span', 'Usuń'));
		deleteButton.click();

		var confirmButton = element.all(by.className('swal2-confirm toggle-button red'));
		confirmButton.click();

		waitForElement(element(by.cssContainingText('span', 'Edytuj')));
		browser.sleep(1000);
		expect(element(by.linkText('SUBNazwaNEW_Testowanie_Automatyczne')).isPresent()).toBe(false);
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