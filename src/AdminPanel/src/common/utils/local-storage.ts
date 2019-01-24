export class LocalStorage {
  static set(key: string, value: any) {
    try {
      localStorage.setItem(key, JSON.stringify(value));
    } catch (exception) {
      this.handleLocalStorageException(exception);
    }
  }

  static setString(key: string, value: string) {
    try {
      localStorage.setItem(key, value);
    } catch (exception) {
      this.handleLocalStorageException(exception);
    }
  }

  static get(key: string) {
    try {
      return JSON.parse(localStorage.getItem(key));
    } catch (exception) {
      this.handleLocalStorageException(exception);
    }
  }

  static getString(key: string) {
    try {
      return localStorage.getItem(key);
    } catch (exception) {
      this.handleLocalStorageException(exception);
    }
  }

  static remove(key: string) {
    try {
      localStorage.removeItem(key);
    } catch (exception) {
      this.handleLocalStorageException(exception);
    }
  }

  private static handleLocalStorageException(exception) {
    console.warn(exception); // tslint:disable-line
  }
}
