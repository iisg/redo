import {bindable} from "aurelia-templating";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {Resource} from "resources/resource";
import {I18N} from "aurelia-i18n";
import {autoinject} from "aurelia-dependency-injection";
import {SystemMetadata} from "resources-config/metadata/system-metadata";
import {Metadata} from "resources-config/metadata/metadata";
import {twoWay} from "common/components/binding-mode";

@autoinject
export class ResourceFormGenerated {
  @bindable resourceKind: ResourceKind;
  @bindable(twoWay) resource: Resource;
  @bindable disableParent: boolean = false;

  currentLanguageCode: string;

  resourceKindChanged() {
    if (!this.resourceKind) {
      this.resource.contents = {};
    }
  }

  constructor(i18n: I18N) {
    this.currentLanguageCode = i18n.getLocale().toUpperCase();
  }

  isParentMetadata(metadata: Metadata): boolean {
    return metadata.baseId == SystemMetadata.PARENT.baseId;
  }
}
