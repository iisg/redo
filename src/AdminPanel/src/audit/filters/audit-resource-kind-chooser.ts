import {computedFrom} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {bindable} from "aurelia-templating";
import {twoWay} from "../../common/components/binding-mode";
import {InCurrentLanguageValueConverter} from 'resources-config/multilingual-field/in-current-language';
import {ResourceKindRepository} from "../../resources-config/resource-kind/resource-kind-repository";

@autoinject
export class AuditResourceKindChooser {
  @bindable(twoWay) selectedResourceKindsIds: string[];
  private resourceKindsLabelsByIds: StringMap<string>;

  constructor(private resourceKindRepository: ResourceKindRepository,
              private inCurrentLanguage: InCurrentLanguageValueConverter,
              private i18n: I18N) {
  }

  async attached() {
    await this.resourceKindRepository.getList().then(values => {
      let resourceKindsLabelsByIds = {};
      values.forEach(value => resourceKindsLabelsByIds[value.id] = this.inCurrentLanguage.toView(value.label));
      this.resourceKindsLabelsByIds = resourceKindsLabelsByIds;
    });
  }

  resourceKindLabel(resourceKindId: string) {
    return this.resourceKindsLabelsByIds[resourceKindId] || this.i18n.tr('Resource kind') + " " + resourceKindId;
  }

  @computedFrom('resourceKindsLabelsByIds')
  get resourceKindsIds(): string[] {
    return this.resourceKindsLabelsByIds && Object.keys(this.resourceKindsLabelsByIds);
  }

}
