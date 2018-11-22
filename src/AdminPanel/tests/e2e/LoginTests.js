describe('Login Tests', function() {

  beforeEach(function() {
		browser.get('https://repekadev.fslab.agh.edu.pl/');
  });

  it('Proper login', function() {
	  var loginButton = element(by.cssContainingText('span', 'Zaloguj'));
	  loginButton.click();

	  var nameField = element(by.id('username'));
	  var passwordField = element(by.id('password'));
	  var loginIcon = element(by.className('submit-button'));
	  nameField.sendKeys('admin');
	  passwordField.sendKeys('admin');
	  loginIcon.click();

	  expect(element(by.cssContainingText('span', 'admin')).isPresent()).toBe(true);
  });

  it('Log out', function() {
	  var adminDropdown = element(by.cssContainingText('span', 'admin'));
	  adminDropdown.click();
	  
	  var logOutButton = element(by.cssContainingText('span', 'Wyloguj'));
	  logOutButton.click();

	  expect(element(by.cssContainingText('span', 'Zaloguj')).isPresent()).toBe(true);
  });

  it('Wrong user login', function() {
	  var loginButton = element(by.cssContainingText('span', 'Zaloguj'));
	  loginButton.click();

	  var nameField = element(by.id('username'));
	  var passwordField = element(by.id('password'));
	  var loginIcon = element(by.className('submit-button'));
	  nameField.sendKeys('xxx');
	  passwordField.sendKeys('admin');
	  loginIcon.click();

	  var errorInfo = element(by.className('login-error-element alert alert-dismissible alert-danger'));
	  expect(errorInfo.getText()).toContain('Logowanie nieudane.');
  });

  it('Wrong password login', function() {
	  var loginButton = element(by.cssContainingText('span', 'Zaloguj'));
	  loginButton.click();

	  var nameField = element(by.id('username'));
	  var passwordField = element(by.id('password'));
	  var loginIcon = element(by.className('submit-button'));
	  nameField.clear();
	  passwordField.clear();
	  nameField.sendKeys('admin');
	  passwordField.sendKeys('xxx');
	  loginIcon.click();

	  var errorInfo = element(by.className('login-error-element alert alert-dismissible alert-danger'));
	  expect(errorInfo.getText()).toContain('Logowanie nieudane.');
  });
});