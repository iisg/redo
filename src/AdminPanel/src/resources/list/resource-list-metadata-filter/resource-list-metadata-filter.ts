import {bindable} from "aurelia-templating";
import {Metadata} from "../../../resources-config/metadata/metadata";
import {MetadataValue} from "../../metadata-value";
import {BindingEngine} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {Router} from "aurelia-router";
import {twoWay} from "../../../common/components/binding-mode";
import {pickBy} from "lodash";

@autoinject
export class ResourceListMetadataFilter {
  @bindable metadata: Metadata;
  @bindable(twoWay) contentsFilter: NumberMap<string>;
  isFilterUsed: boolean = false;

  metadataValue: MetadataValue = new MetadataValue();

  constructor(private bindingEngine: BindingEngine, private router: Router) {
  }

  attached() {
    this.ensureContentsFilterExists();
    this.metadataValue.onChange(this.bindingEngine, () => this.onValueChange());
    this.updateIsFilterUsed();
  }

  detached() {
    this.metadataValue.clearChangeListener();
  }

  contentsFilterChanged() {
    this.metadataValue.value = this.contentsFilter ? this.contentsFilter[this.metadata.id] : '';
  }

  private ensureContentsFilterExists() {
    if (!this.contentsFilter) {
      this.contentsFilter = {};
    }
  }

  private onValueChange() {
    this.ensureContentsFilterExists();
    this.contentsFilter[this.metadata.id] = this.metadataValue.value;
  }

  private updateIsFilterUsed() {
    this.isFilterUsed = !!this.metadataValue.value;
  }

  fetchFilteredResources() {
    this.updateIsFilterUsed();
    this.contentsFilter[this.metadata.id] = this.metadataValue.value;
    const currentInstruction = this.router.currentInstruction;
    let parameters = Object.assign(currentInstruction.params, currentInstruction.queryParams);
    const notEmptyFilters = pickBy(this.contentsFilter, v => v && v.trim());
    parameters['contentsFilter'] = JSON.stringify(notEmptyFilters);
    this.router.navigateToRoute(currentInstruction.config.name, parameters, {replace: true});
  }
}
