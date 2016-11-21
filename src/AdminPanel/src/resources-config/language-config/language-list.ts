import {HttpClient} from "aurelia-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {computedFrom} from "aurelia-binding";
import {ComponentAttached} from "aurelia-templating";
import {Language} from "./language";
import {FloatingAddFormController} from "../add-form/floating-add-form";

@autoinject
export class LanguageList implements ComponentAttached {
  newLanguage: Language = new Language;
  isValidating: boolean = false;
  languageList: Language[];
  addFormController: FloatingAddFormController = {};

  constructor(private httpClient: HttpClient) {
  }

  @computedFrom('isValidating', 'httpClient.isRequesting')
  get isRequesting() {
    return this.isValidating || this.httpClient.isRequesting;
  }

  attached(): void {
    this.httpClient.get('languages').then(response => {
      this.languageList = response.content;
    });
  }

  addNewLanguage() {
    return this.httpClient.post('languages', this.newLanguage).then(response => {
      this.addFormController.hide();
      this.newLanguage = new Language;
      this.languageList.push(response.content);
    });
  }
}
