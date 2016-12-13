import {Resource} from "../resource";
import {Validator} from "aurelia-validation";
import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class ResourceAddForm {
  @bindable
  submit: (value: {resource: Resource}) => Promise<any>;

  newResource: Resource = new Resource;

  submitting = false;

  errorToDisplay: string;

  constructor(private validator: Validator) {
  }

  validateAndSubmit() {
    this.submitting = true;
    this.errorToDisplay = undefined;
    this.validator.validateObject(this.newResource).then(results => {
      const errors = results.filter(result => !result.valid);
      if (errors.length == 0) {
        return this.submit({resource: this.newResource})
          .then(() => this.newResource = new Resource);
      } else {
        this.errorToDisplay = errors[0].message;
      }
    }).finally(() => this.submitting = false);
  }
}
