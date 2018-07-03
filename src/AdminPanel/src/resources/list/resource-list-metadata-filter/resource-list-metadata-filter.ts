import {observable} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {Router} from "aurelia-router";
import {bindable} from "aurelia-templating";
import {pickBy} from "lodash";
import {twoWay} from "../../../common/components/binding-mode";
import {Metadata} from "../../../resources-config/metadata/metadata";

@autoinject
export class ResourceListMetadataFilter {
  @bindable metadata: Metadata;
  @bindable(twoWay) contentsFilter: NumberMap<string>;
  @observable metadataValue: any;
  previousMetadataValue: any;
  inputBoxVisible: boolean;
  inputBoxFocused: boolean;
  inputBoxSize = 1;

  constructor(private router: Router) {
  }

  attached() {
    if (!this.contentsFilter) {
      this.contentsFilter = {};
    } else {
      this.contentsFilterChanged();
    }
  }

  contentsFilterChanged() {
    this.previousMetadataValue = this.metadataValue;
    this.metadataValue = this.contentsFilter[this.metadata.id];
    if (this.metadataValue != this.previousMetadataValue) {
      this.inputBoxVisible = !!this.metadataValue;
      this.previousMetadataValue = this.metadataValue;
    }
  }

  metadataValueChanged() {
    this.contentsFilter[this.metadata.id] = this.metadataValue;
    this.inputBoxSize = this.metadataValue && this.metadataValue.length || 1;
  }

  toggleInputBoxVisibility() {
    if (this.inputBoxVisible) {
      if (this.metadataValue) {
        this.metadataValue = undefined;
        this.fetchFilteredResourcesIfMetadataValueChanged();
      }
      this.takeFocusOutOfInputBoxAndHideIt();
    } else {
      this.showInputBoxAndSetFocusOnIt();
    }
  }

  onInputBoxBlurred() {
    if (this.inputBoxVisible && !this.metadataValue) {
      this.takeFocusOutOfInputBoxAndHideIt();
    }
    this.fetchFilteredResourcesIfMetadataValueChanged();
  }

  fetchFilteredResources() {
    this.previousMetadataValue = this.metadataValue || undefined;
    this.contentsFilter[this.metadata.id] = this.metadataValue;
    const currentInstruction = this.router.currentInstruction;
    let parameters = Object.assign(currentInstruction.params, currentInstruction.queryParams);
    const notEmptyFilters = pickBy(this.contentsFilter, v => v && v.trim());
    parameters['contentsFilter'] = JSON.stringify(notEmptyFilters);
    this.router.navigateToRoute(currentInstruction.config.name, parameters, {replace: true});
  }

  private fetchFilteredResourcesIfMetadataValueChanged() {
    if ((this.metadataValue || undefined) != this.previousMetadataValue) {
      this.fetchFilteredResources();
    }
  }

  private showInputBoxAndSetFocusOnIt() {
    this.inputBoxVisible = true;
    this.inputBoxFocused = true;
  }

  private takeFocusOutOfInputBoxAndHideIt() {
    this.inputBoxFocused = false;
    this.inputBoxVisible = false;
  }
}
