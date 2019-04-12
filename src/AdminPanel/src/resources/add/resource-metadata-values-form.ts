import {computedFrom} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {bindable} from "aurelia-templating";
import {ValidationController} from "aurelia-validation";
import {booleanAttribute} from "common/components/boolean-attribute";
import {Metadata} from "resources-config/metadata/metadata";
import {changeHandler} from "../../common/components/binding-mode";
import {ChangeEvent} from "../../common/events/change-event";
import {MetadataValue} from "../metadata-value";
import {Resource} from "../resource";
import {debounce} from "lodash";

@autoinject
export class ResourceMetadataValuesForm {
  @bindable(changeHandler('resourceDataChanged')) metadata: Metadata;
  @bindable(changeHandler('resourceDataChanged')) resource: Resource;
  @bindable(changeHandler('resourceDataChanged')) @booleanAttribute disabled: boolean = false;
  @bindable @booleanAttribute required: boolean = false;
  @bindable skipValidation: boolean = false;
  @bindable validationController: ValidationController;
  @bindable forceSimpleFileUpload: boolean = false;

  valueTable: Element;

  public constructor(private element: Element) {
  }

  resourceDataChanged() {
    this.addEmptyMetadataField();
  }

  private addEmptyMetadataField = debounce(() => {
    if (this.resource && this.metadata) {
      this.ensureResourceHasMetadataContents();
      const length = this.resource.contents[this.metadata.id].length;
      if (!length && !this.controlCanShowEmptyField && !this.disabled) {
        this.addNew(false);
      }
    }
  });

  private ensureResourceHasMetadataContents() {
    const contents = this.resource.contents;
    const id = this.metadata.id;
    if (!contents[id]) {
      contents[id] = [];
    }
  }

  deleteIndex(index: number, offset: number = 1) {
    this.resource.contents[this.metadata.id].splice(index, offset);
    this.element.dispatchEvent(ChangeEvent.newInstance());
  }

  addNew(dispatchChangeEvent: boolean = true) {
    this.resource.contents[this.metadata.id].push(new MetadataValue());
    if (dispatchChangeEvent) {
      this.element.dispatchEvent(ChangeEvent.newInstance());
      // queueMicroTask and queueTask fire too early and the <input> doesn't exist yet.
      // setTimeout(..., 0) fires at right time, but something steals the focus later.
      // setTimeout + queue[Micro]Task isn't reliable, it works for second and subsequent inputs but not first one
      setTimeout(() => {
        $(this.valueTable).find('td').last().find('input, textarea').first().focus();
      }, 50);
    }
  }

  @computedFrom("metadata.control")
  get controlCanShowEmptyField(): boolean {
    return ['relationship', 'file', 'directory'].indexOf(this.metadata.control) !== -1;
  }

  @computedFrom("required", "metadata.constraints")
  get metadataShownWithoutButtons(): boolean {
    return this.required && this.metadata.constraints.maxCount === 1;
  }

  @computedFrom('metadata.constraints.maxCount', 'values.length')
  get itIsPossibleToAddMoreValues() {
    const noValuesLimit = !this.metadata.constraints.maxCount || this.metadata.constraints.maxCount === -1;
    return this.skipValidation || noValuesLimit || this.values.length < this.metadata.constraints.maxCount;
  }

  @computedFrom('resource.contents', 'metadata.id')
  get values() {
    return this.resource.contents[this.metadata.id];
  }
}
