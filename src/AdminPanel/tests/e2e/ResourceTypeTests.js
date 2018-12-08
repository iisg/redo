describe('Resource Type Tests', function() {

  function waitForElement (locator) {
	   browser.driver.wait(function () {
			return locator.isPresent()
	   }, 10000);
  }
 
  beforeEach(function() {
		browser.get('https://repekadev.fslab.agh.edu.pl/admin/resource-kinds/books');
		browser.driver.manage().window().setSize(1536, 864);
  });

  it('Try to add without metadata', function() {
		waitForElement(element(by.cssContainingText('span', 'rodzaj zasobu')));
		var addResourceTypeButton = element(by.cssContainingText('span', 'rodzaj zasobu'));
		browser.sleep(500);
		addResourceTypeButton.click();

		var fields = element.all(by.className('form-control au-target'));
		fields.get(0).sendKeys('Nazwa_Testowanie_Automatyczne');
		fields.get(1).sendKeys('Nazwa_Testowanie_Automatyczne');

		waitForElement(element(by.buttonText('Dodaj')));
		var addButton = element(by.buttonText('Dodaj'));
		addButton.click();

		waitForElement(element(by.className('help-block validation-message')));
		var errorMessage = element(by.className('help-block validation-message'));
		expect(errorMessage.getText()).toContain('Dodaj rodzaj metadanej');
  });

  it('Try to add without Polish name', function() {
	  	alertDialog = browser.switchTo().alert();
		alertDialog.accept();

		waitForElement(element(by.cssContainingText('span', 'rodzaj zasobu')));
		var addResourceTypeButton = element(by.cssContainingText('span', 'rodzaj zasobu'));
		browser.sleep(500);
		addResourceTypeButton.click();

		waitForElement(element(by.className('form-control au-target')));
		var fields = element.all(by.className('form-control au-target'));
		fields.get(0).sendKeys('Nazwa_Testowanie_Automatyczne');

		browser.sleep(500);
		waitForElement(element(by.className('select2-selection__rendered')));
		var lists = element.all(by.className('select2-selection__rendered'));
		var listToClick = lists.get(1);
		browser.sleep(500);
		listToClick.click();
		browser.sleep(500);
		browser.driver.switchTo().activeElement().sendKeys('Metadana_do_testowania_automatycznego1');
		browser.driver.switchTo().activeElement().sendKeys(protractor.Key.ENTER);
		browser.sleep(500);

		waitForElement(element(by.buttonText('Dodaj')));
		var addButton = element(by.buttonText('Dodaj'));
		addButton.click();

		waitForElement(element(by.className('help-block validation-message')));
		var errorMessage = element(by.className('help-block validation-message'));
		expect(errorMessage.getText()).toContain('Nazwa wyświetlana musi mieć wartość we wszystkich językach.');
  });

  it('Try to add without English name', function() {
	  	alertDialog = browser.switchTo().alert();
		alertDialog.accept();

		waitForElement(element(by.cssContainingText('span', 'rodzaj zasobu')));
		var addResourceTypeButton = element(by.cssContainingText('span', 'rodzaj zasobu'));
		browser.sleep(500);
		addResourceTypeButton.click();

		waitForElement(element(by.className('form-control au-target')));
		var fields = element.all(by.className('form-control au-target'));
		fields.get(1).sendKeys('Nazwa_Testowanie_Automatyczne');

		waitForElement(element(by.className('select2-selection__rendered')));
		var lists = element.all(by.className('select2-selection__rendered'));
		var listToClick = lists.get(1);
		browser.sleep(500);
		listToClick.click();
		browser.sleep(500);
		browser.driver.switchTo().activeElement().sendKeys('Metadana_do_testowania_automatycznego1');
		browser.driver.switchTo().activeElement().sendKeys(protractor.Key.ENTER);
		browser.sleep(500);

		waitForElement(element(by.buttonText('Dodaj')));
		var addButton = element(by.buttonText('Dodaj'));
		addButton.click();

		waitForElement(element(by.className('help-block validation-message')));
		var errorMessage = element(by.className('help-block validation-message'));
		expect(errorMessage.getText()).toContain('Nazwa wyświetlana musi mieć wartość we wszystkich językach.');
  });

  it('Add resource type', function() {
	  	alertDialog = browser.switchTo().alert();
		alertDialog.accept();

		waitForElement(element(by.cssContainingText('span', 'rodzaj zasobu')));
		var addResourceTypeButton = element(by.cssContainingText('span', 'rodzaj zasobu'));
		browser.sleep(500);
		addResourceTypeButton.click();

		waitForElement(element(by.className('form-control au-target')));
		var fields = element.all(by.className('form-control au-target'));
		fields.get(0).sendKeys('Nazwa_Testowanie_Automatyczne');
		fields.get(1).sendKeys('Nazwa_Testowanie_Automatyczne');

		waitForElement(element(by.className('select2-selection__rendered')));
		var lists = element.all(by.className('select2-selection__rendered'));
		var listToClick = lists.get(1);
		browser.sleep(500);
		listToClick.click();
		browser.sleep(500);
		browser.driver.switchTo().activeElement().sendKeys('Metadana_do_testowania_automatycznego1');
		browser.driver.switchTo().activeElement().sendKeys(protractor.Key.ENTER);
		browser.sleep(500);

		waitForElement(element(by.buttonText('Dodaj')));
		var addButton = element(by.buttonText('Dodaj'));
		addButton.click();
		browser.sleep(1000);

		waitForElement(element(by.linkText('Nazwa_Testowanie_Automatyczne')));
		expect(element(by.linkText('Nazwa_Testowanie_Automatyczne')).isPresent()).toBe(true);
  });

  it('Try to edit by removing Polish name', function() {
	    browser.get('https://repekadev.fslab.agh.edu.pl/admin/resource-kinds/books');
		browser.sleep(5000);
	    waitForElement(element(by.linkText('Nazwa_Testowanie_Automatyczne')));
		var newResourceTypeRowLink = element(by.linkText('Nazwa_Testowanie_Automatyczne'));
		newResourceTypeRowLink.click();

		waitForElement(element(by.cssContainingText('span', 'Edytuj')));
		var editButton = element(by.cssContainingText('span', 'Edytuj'));
		editButton.click();

		waitForElement(element(by.className('form-control au-target')));
		var fields = element.all(by.className('form-control au-target'));
		var confirmButton = element(by.buttonText('Zatwierdź'));
		fields.get(1).clear();
		confirmButton.click();

		waitForElement(element(by.className('help-block validation-message')));
		var errorMessage = element(by.className('help-block validation-message'));
		expect(errorMessage.getText()).toContain('Nazwa wyświetlana musi mieć wartość we wszystkich językach.');
  });

  it('Try to edit by removing English name', function() {
	  	alertDialog = browser.switchTo().alert();
		alertDialog.accept();

	    browser.get('https://repekadev.fslab.agh.edu.pl/admin/resource-kinds/books');
		waitForElement(element(by.linkText('Nazwa_Testowanie_Automatyczne')));
		var newResourceTypeRowLink = element(by.linkText('Nazwa_Testowanie_Automatyczne'));
		newResourceTypeRowLink.click();

		waitForElement(element(by.cssContainingText('span', 'Edytuj')));
		var editButton = element(by.cssContainingText('span', 'Edytuj'));
		editButton.click();

		waitForElement(element(by.className('form-control au-target')));
		var fields = element.all(by.className('form-control au-target'));
		var confirmButton = element(by.buttonText('Zatwierdź'));
		fields.get(0).clear();
		confirmButton.click();

		waitForElement(element(by.className('help-block validation-message')));
		var errorMessage = element(by.className('help-block validation-message'));
		expect(errorMessage.getText()).toContain('Nazwa wyświetlana musi mieć wartość we wszystkich językach.');
  });

  it('Try to edit by removing the only metadata', function() {
	   	alertDialog = browser.switchTo().alert();
		alertDialog.accept();

	    browser.get('https://repekadev.fslab.agh.edu.pl/admin/resource-kinds/books');
		waitForElement(element(by.linkText('Nazwa_Testowanie_Automatyczne')));
		var newResourceTypeRowLink = element(by.linkText('Nazwa_Testowanie_Automatyczne'));
		newResourceTypeRowLink.click();

		waitForElement(element(by.cssContainingText('span', 'Edytuj')));
		var editButton = element(by.cssContainingText('span', 'Edytuj'));
		editButton.click();

		waitForElement(element(by.className('buttons')));
		var metadataButtonsLabel = element.all(by.className('buttons'));
		var metadataButtons = metadataButtonsLabel.get(1).all(by.className('au-target'));
		metadataButtons.get(10).click();

		var confirmButton = element(by.buttonText('Zatwierdź'));
		confirmButton.click();

		waitForElement(element(by.className('help-block validation-message')));
		var errorMessage = element(by.className('help-block validation-message'));
		expect(errorMessage.getText()).toContain('Dodaj rodzaj metadanej');
  });

  it('Edit by adding metadata', function() {
	    browser.get('https://repekadev.fslab.agh.edu.pl/admin/resource-kinds/books');
	    waitForElement(element(by.linkText('Nazwa_Testowanie_Automatyczne')));
		var newResourceTypeRowLink = element(by.linkText('Nazwa_Testowanie_Automatyczne'));
		newResourceTypeRowLink.click();

		waitForElement(element(by.cssContainingText('span', 'Edytuj')));
		var editButton = element(by.cssContainingText('span', 'Edytuj'));
		editButton.click();

		browser.sleep(500);
		var lists = element.all(by.className('select2-selection__rendered'));
		lists.get(1).click();
		browser.sleep(500);
		browser.driver.switchTo().activeElement().sendKeys('Metadana_do_testowania_automatycznego2');
		browser.driver.switchTo().activeElement().sendKeys(protractor.Key.ENTER);
		browser.sleep(500);

		var confirmButton = element(by.buttonText('Zatwierdź'));
		confirmButton.click();
		browser.sleep(1000);

	    waitForElement(element(by.className('resource-kind-details')));
		var parameters = element(by.className('resource-kind-details'));
		expect(parameters.getText()).toContain('Metadana_do_testowania_automatycznego2');
  });

  it('Edit by changing Polish name', function() {
	    browser.get('https://repekadev.fslab.agh.edu.pl/admin/resource-kinds/books');
		browser.sleep(5000);
	    waitForElement(element(by.linkText('Nazwa_Testowanie_Automatyczne')));
		var newResourceTypeRowLink = element(by.linkText('Nazwa_Testowanie_Automatyczne'));
		newResourceTypeRowLink.click();

		waitForElement(element(by.cssContainingText('span', 'Edytuj')));
		var editButton = element(by.cssContainingText('span', 'Edytuj'));
		editButton.click();

		waitForElement(element(by.className('form-control au-target')));
		var fields = element.all(by.className('form-control au-target'));
		var confirmButton = element(by.buttonText('Zatwierdź'));
		fields.get(1).clear();
		fields.get(1).sendKeys('Nowa_Nazwa_Automatyczna');
		confirmButton.click();
		browser.sleep(500);

		waitForElement(element(by.className('page-title')));
		var titleLabel = element(by.className('page-title'));
		expect(titleLabel.getText()).toContain('Nowa_Nazwa_Automatyczna');
  });

  // it('Edit by changing English name', function() {
	    // waitForElement(element(by.linkText('Nowa_Nazwa_Automatyczna')));
		// var newResourceTypeRowLink = element(by.linkText('Nowa_Nazwa_Automatyczna'));
		// newResourceTypeRowLink.click();

		// waitForElement(element(by.cssContainingText('span', 'Edytuj')));
		// var editButton = element(by.cssContainingText('span', 'Edytuj'));
		// editButton.click();

		// waitForElement(element(by.className('form-control au-target')));
		// var fields = element.all(by.className('form-control au-target'));
		// var confirmButton = element(by.buttonText('Zatwierdź'));
		// fields.get(0).clear();
		// fields.get(0).sendKeys('Nowa_Nazwa_AutomatycznaENG');
		// confirmButton.click();
		// browser.sleep(500);

		// waitForElement(element(by.cssContainingText('span', 'Edytuj')));
		// var languageMenu = element(by.className('au-target flag-icon-xs'));
		// languageMenu.click();
		// var englishOption = element(by.linkText('English'));
		// englishOption.click();

		// waitForElement(element(by.className('page-title')));
		// var titleLabel = element(by.className('page-title'));
		// expect(titleLabel.getText()).toContain('Nowa_Nazwa_AutomatycznaENG');

		// var languageMenu = element(by.className('au-target flag-icon-xs'));
		// languageMenu.click();
		// var polishOption = element(by.linkText('Polski'));
		// polishOption.click();
  // });

  it('Delete resource type', function() {
	    browser.get('https://repekadev.fslab.agh.edu.pl/admin/resource-kinds/books');
		browser.sleep(5000);
		waitForElement(element(by.linkText('Nowa_Nazwa_Automatyczna')));
		var newResourceTypeRowLink = element(by.linkText('Nowa_Nazwa_Automatyczna'));
		browser.sleep(3000);
		newResourceTypeRowLink.click();

		waitForElement(element(by.cssContainingText('span', 'Usuń')));
		var deleteButton = element(by.cssContainingText('span', 'Usuń'));
		deleteButton.click();

		var confirmButton = element.all(by.className('swal2-confirm toggle-button red'));
		confirmButton.click();

		waitForElement(element(by.cssContainingText('span', 'rodzaj zasobu')));
		expect(element(by.linkText('Nowa_Nazwa_Automatyczna')).isPresent()).toBe(false);
  });
});