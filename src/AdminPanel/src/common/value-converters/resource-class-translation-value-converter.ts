import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";

@autoinject
export class ResourceClassTranslationValueConverter implements ToViewValueConverter {
  constructor(private i18n: I18N) {
  }

  toView(key: string, resourceClass: string): string {
    if (!resourceClass) {
      console.warn('Defaulting resource class to books, as none were given for the translation: ' + key); // tslint:disable-line
      resourceClass = 'books';
    }
    return this.i18n.tr(`resource_classes::${resourceClass}//${key}`);
  }
}
