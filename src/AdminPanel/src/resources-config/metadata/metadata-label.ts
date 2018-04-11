import {Metadata} from "./metadata";
import {I18N} from "aurelia-i18n";
import {autoinject} from "aurelia-dependency-injection";
import {InCurrentLanguageValueConverter} from "../multilingual-field/in-current-language";

@autoinject
export class MetadataLabelValueConverter implements ToViewValueConverter {
  constructor(private i18n: I18N, private inCurrentLanguage: InCurrentLanguageValueConverter) {
  }

  toView(metadata: Metadata): string {
    return this.inCurrentLanguage.toView(metadata.label) || metadata.name;
  }
}
