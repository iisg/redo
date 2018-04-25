import {bindable, useView, customElement} from "aurelia-templating";
import {Metadata} from "resources-config/metadata/metadata";
import {Resource} from "../resource";
import {computedFrom} from "aurelia-binding";
import {DOM} from "aurelia-framework";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {IconButton} from "common/components/buttons/icon-button";

@useView('common/components/buttons/icon-button.html')
@customElement('new-metadata-value-button')
@autoinject
export class NewMetadataValueButton extends IconButton {
  @bindable metadata: Metadata;
  @bindable resource: Resource;

  public constructor(private element: Element, private i18n: I18N) {
    super();
    this.iconName = 'add-2';
    this.tooltipTextWhenEnabled = this.i18n.tr('Add value');
    this.tooltipTextWhenDisabled = this.i18n.tr('No more values are allowed');
  }

  get disabled(): boolean {
    return !this.canAddMore;
  }

  @computedFrom('metadata.constraints.maxCount', 'values.length')
  get canAddMore() {
    return !this.metadata.constraints.maxCount || this.values.length < this.metadata.constraints.maxCount;
  }

  @computedFrom('resource.contents', 'metadata.id')
  get values() {
    return this.resource.contents[this.metadata.id];
  }

  addNew() {
    let event = DOM.createCustomEvent("add", {bubbles: true});
    this.element.dispatchEvent(event);
  }
}
