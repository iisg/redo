import {bindable} from "aurelia-templating";
import {Metadata} from "resources-config/metadata/metadata";
import {Resource} from "../resource";
import {autoinject} from "aurelia-dependency-injection";
import {booleanAttribute} from "common/components/boolean-attribute";
import {ValidationController} from "aurelia-validation";
import {MetadataValue} from "../metadata-value";
import {changeHandler} from "../../common/components/binding-mode";

@autoinject
export class ResourceMetadataValuesForm {
  @bindable(changeHandler('resourceDataChanged')) metadata: Metadata;
  @bindable(changeHandler('resourceDataChanged')) resource: Resource;
  @bindable @booleanAttribute disabled: boolean = false;
  @bindable @booleanAttribute required: boolean = false;
  @bindable validationController: ValidationController;

  valueTable: Element;

  resourceDataChanged() {
    if (this.resource && this.metadata) {
      this.ensureResourceHasMetadataContents();
    }
  }

  requiredChanged() {
    if (this.resource && this.metadata) {
      this.ensureResourceHasMetadataContents();
      const length = this.resource.contents[this.metadata.id].length;
      if (this.required) {
        if (!length) {
          this.addNew();
        }
      } else {
        this.resource.contents[this.metadata.id].forEach((metadataValue, index) => {
          if ((Array.isArray(metadataValue.value) && metadataValue.value.length === 0) || !metadataValue.value) {
            this.deleteIndex(index, 1);
          }
        });
      }
    }
  }

  private ensureResourceHasMetadataContents() {
    const contents = this.resource.contents;
    const id = this.metadata.id;
    if (!contents.hasOwnProperty(id)) {
      contents[id] = [];
    }
  }

  deleteIndex(index: number, offset: number = 1) {
    this.resource.contents[this.metadata.id].splice(index, offset);
  }

  addNew() {
    this.resource.contents[this.metadata.id].push(new MetadataValue());
    // queueMicroTask and queueTask fire too early and the <input> doesn't exist yet.
    // setTimeout(..., 0) fires at right time, but something steals the focus later.
    // setTimeout + queue[Micro]Task isn't reliable, it works for second and subsequent inputs but not first one
    setTimeout(() => {
      $(this.valueTable).find('td').last().find('input, textarea').first().focus();
    }, 50);
  }

  isDragHandle(data: { evt: MouseEvent }) {
    return $(data.evt.target).is('.drag-handle') || $(data.evt.target).parents('.drag-handle').length > 0;
  }
}
