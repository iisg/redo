import {I18N} from "aurelia-i18n";
import {Resource} from "../resource";
import {SystemMetadata} from "../../resources-config/metadata/system-metadata";
import {autoinject} from "aurelia-dependency-injection";
import {HTMLSanitizer} from "aurelia-templating-resources";

@autoinject
export class ResourceLabelValueConverter implements ToViewValueConverter {
  constructor(private i18n: I18N, private htmlSanitizer: HTMLSanitizer) {
  }

  toView(resource: Resource): any {
    if (resource.contents) {
      const labelMetadata = resource.contents[SystemMetadata.RESOURCE_LABEL.id];
      return labelMetadata && labelMetadata[0] && this.htmlSanitizer.sanitize(labelMetadata[0].value)
        || this.i18n.tr('Resource') + ` #${resource.id}`;
    } else {
      return '';
    }
  }
}
