import {HttpClient} from "aurelia-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {ComponentAttached} from "aurelia-templating";
import {Language} from "./language";
import {FloatingAddFormController} from "../add-form/floating-add-form";

@autoinject
export class LanguageList implements ComponentAttached {
  languageList: Language[];
  addFormController: FloatingAddFormController = {};

  constructor(private httpClient: HttpClient) {
  }

  attached(): void {
    this.httpClient.get('languages').then(response => {
      this.languageList = response.content;
    });
  }

  addNewLanguage(newLanguage: Language) {
    return this.httpClient.post('languages', newLanguage).then(response => {
      this.addFormController.hide();
      this.languageList.push(response.content);
    });
  }
}
