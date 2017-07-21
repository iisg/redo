import {Resource} from "../resource";
import {Validator} from "aurelia-validation";
import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {deepCopy} from "common/utils/object-utils";
import {computedFrom} from "aurelia-binding";
import {SystemMetadata} from "../../resources-config/metadata/system-metadata";

@autoinject
export class ResourceForm {
  @bindable submit: (value: {savedResource: Resource}) => Promise<any>;
  @bindable edit: Resource;
  @bindable parent: Resource;

  resource: Resource = new Resource;
  submitting = false;
  errorToDisplay: string;

  constructor(private validator: Validator) {
  }

  @computedFrom('resource.id')
  get editing(): boolean {
    return !!this.resource.id;
  }

  editChanged(newValue: Resource) {
    this.resource = $.extend(new Resource(), deepCopy(newValue));
    this.parentChanged(this.parent);
  }

  parentChanged(newParent: Resource) {
    if (newParent != undefined) {
      this.resource.contents[SystemMetadata.PARENT.baseId] = [newParent.id];
    }
  }

  validateAndSubmit() {
    this.submitting = true;
    this.errorToDisplay = undefined;
    this.validator.validateObject(this.resource).then(results => {
      const errors = results.filter(result => !result.valid);
      if (errors.length == 0) {
        return Promise.resolve(this.submit({savedResource: this.resource}))
          .then(() => this.editing || (this.resource = new Resource));
      } else {
        this.errorToDisplay = errors[0].message;
      }
    }).finally(() => this.submitting = false);
  }
}
