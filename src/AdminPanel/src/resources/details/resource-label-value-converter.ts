import {I18N} from "aurelia-i18n";
import {Resource} from "../resource";
import {SystemMetadata} from "../../resources-config/metadata/system-metadata";
import {autoinject} from "aurelia-dependency-injection";
import {HTMLSanitizer} from "aurelia-templating-resources";
import {I18nParams} from "../../config/i18n";
import {MetadataValue} from "../metadata-value";

@autoinject
export class ResourceLabelValueConverter implements ToViewValueConverter {
  constructor(private i18n: I18N, private htmlSanitizer: HTMLSanitizer, private i18nParams: I18nParams) {
  }

  toView(resource: Resource): any {
    if (resource.contents) {
      let labelMetadata = resource.contents[SystemMetadata.RESOURCE_LABEL.id];
      labelMetadata = this.filterValuesInCurrentLanguage(labelMetadata);
      return labelMetadata && labelMetadata[0] && this.htmlSanitizer.sanitize(labelMetadata[0].value)
        || this.i18n.tr('Resource') + ` #${resource.id}`;
    } else {
      return '';
    }
  }

  filterValuesInCurrentLanguage(metadataValues: MetadataValue[]) {
    const filteredMetadataValues = metadataValues.filter(metadataValue => {
      const submetadataContents = metadataValue.submetadata && metadataValue.submetadata[SystemMetadata.RESOURCE_LABEL_LANGUAGE.id];
      if (submetadataContents) {
        const index = submetadataContents.findIndex(language =>
          language.value == this.i18nParams.currentUiLanguage.toUpperCase()
        );
        return index != -1;
      }
    });
    return filteredMetadataValues.length ? filteredMetadataValues : metadataValues;
  }

}
