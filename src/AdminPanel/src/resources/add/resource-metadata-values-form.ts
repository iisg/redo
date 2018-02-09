import {bindable} from "aurelia-templating";
import {Metadata} from "resources-config/metadata/metadata";
import {Resource} from "../resource";
import {autoinject} from "aurelia-dependency-injection";
import {twoWay} from "common/components/binding-mode";
import {booleanAttribute} from "common/components/boolean-attribute";
import {BindingSignaler} from "aurelia-templating-resources";
import {ValidationController} from "aurelia-validation";

@autoinject
export class ResourceMetadataValuesForm {
  @bindable metadata: Metadata;
  @bindable(twoWay) resource: Resource;
  @bindable @booleanAttribute disabled: boolean = false;
  @bindable @booleanAttribute required: boolean = false;
  @bindable validationController: ValidationController;

  valueTable: Element;

  private readonly VALUES_CHANGED_SIGNAL = 'metadata-values-changed';

  constructor(private bindingSignaler: BindingSignaler) {
  }

  metadataChanged() {
    if (this.resource != undefined) {
      this.ensureResourceHasMetadataContents();
    }
  }

  resourceChanged() {
    if (this.metadata != undefined) {
      this.ensureResourceHasMetadataContents();
    }
  }

  get allValuesCount(): number {
    return this.resource.contents[this.metadata.id].length;
  }

  isFilled(value: any): boolean {
    return (value !== undefined) && (value !== '');
  }

  get filledValuesCount(): number {
    return this.resource.contents[this.metadata.id].filter(this.isFilled).length;
  }

  private valueIsUndefined(index: number): boolean {
    return this.resource.contents[this.metadata.id][index] === undefined;
  }

  isDeletingDisabled(index: number): boolean {
    if (this.disabled) {
      return true;
    }

    return this.required && this.filledValuesCount <= 1 && !this.valueIsUndefined(index);
  }

  canAddMoreValues(): boolean {
    const maxCount: number = this.metadata.constraints.maxCount || Infinity;
    return this.allValuesCount < maxCount;
  }

  private ensureResourceHasMetadataContents() {
    const contents = this.resource.contents;
    const id = this.metadata.id;
    if (!contents.hasOwnProperty(id)) {
      contents[id] = [];
    }
  }

  deleteIndex(index: number) {
    this.resource.contents[this.metadata.id].splice(index, 1);
    this.bindingSignaler.signal(this.VALUES_CHANGED_SIGNAL);
  }

  addNew() {
    this.resource.contents[this.metadata.id].push(undefined);
    this.bindingSignaler.signal(this.VALUES_CHANGED_SIGNAL);
    // queueMicroTask and queueTask fire too early and the <input> doesn't exist yet.
    // setTimeout(..., 0) fires at right time, but something steals the focus later.
    // setTimeout + queue[Micro]Task isn't reliable, it works for second and subsequent inputs but not first one
    setTimeout(() => {
      $(this.valueTable).find('td.content').last().find('input, textarea').first().focus();
    }, 50);
  }

  isDragHandle(data: { evt: MouseEvent }) {
    return $(data.evt.target).is('.drag-handle') || $(data.evt.target).parents('.drag-handle').length > 0;
  }
}
