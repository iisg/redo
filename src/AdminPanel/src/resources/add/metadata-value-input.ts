import {bindable} from "aurelia-templating";
import {Metadata} from "resources-config/metadata/metadata";
import {autoinject} from "aurelia-dependency-injection";
import {booleanAttribute} from "common/components/boolean-attribute";
import {Resource} from "../resource";
import {ValidationController} from "aurelia-validation";
import {MetadataValue} from "../metadata-value";
import {InCurrentLanguageValueConverter} from "resources-config/multilingual-field/in-current-language";

@autoinject
export class MetadataValueInput {
  @bindable metadata: Metadata;
  @bindable resource: Resource;
  @bindable value: MetadataValue;
  @bindable @booleanAttribute disabled: boolean = false;
  @bindable validationController: ValidationController;
  @bindable description: string;
  @bindable @booleanAttribute skipValidation: boolean = false;

  constructor(private inCurrentLanguage: InCurrentLanguageValueConverter) {
  }
}
