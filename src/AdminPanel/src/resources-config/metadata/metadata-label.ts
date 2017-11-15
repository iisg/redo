import {Metadata} from "./metadata";
import {I18N} from "aurelia-i18n";
import {autoinject} from "aurelia-dependency-injection";
import {InCurrentLanguageValueConverter} from "../multilingual-field/in-current-language";

@autoinject
export class MetadataLabelValueConverter implements ToViewValueConverter {
  constructor(private i18n: I18N, private inCurrentLanguage: InCurrentLanguageValueConverter) {
  }

  toView(metadata: Metadata): string {
    return (metadata.baseId < 0)
      ? this.getSystemMetadataLabel(metadata)
      : this.getRegularMetadataLabel(metadata);
  }

  private getSystemMetadataLabel(metadata: Metadata): string {
    return this.i18n.tr(`system_metadata::${metadata.baseId || metadata.id}`);
  }

  private getRegularMetadataLabel(metadata: Metadata): string {
    return this.inCurrentLanguage.toView(metadata.label) || metadata.name;
  }
}
