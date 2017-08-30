import {bindable} from "aurelia-templating";
import {Metadata} from "resources-config/metadata/metadata";
import {I18N} from "aurelia-i18n";
import {autoinject} from "aurelia-dependency-injection";
import {computedFrom} from "aurelia-binding";

@autoinject
export class MaxCountConstraintInfo {
  @bindable metadata: Metadata;
  @bindable valueCount: number;

  max: number;
  message: string;

  constructor(private i18n: I18N) {
  }

  metadataChanged(): void {
    this.max = this.metadata.constraints.maxCount || Infinity;
    this.message = this.getLocalizedMessage();
  }

  @computedFrom('valueCount')
  get constraintsExceeded(): boolean {
    return this.valueCount > this.max;
  }

  @computedFrom('constraintsExceeded')
  get badgeClass(): string {
    return this.constraintsExceeded ? 'badge badge-danger' : 'badge';
  }

  get hasMaxConstraint(): boolean {
    return this.metadata.constraints.maxCount > 0;
  }

  private getLocalizedMessage(): string {
    const message: string = this.hasMaxConstraint
      ? 'At most {{max}} values required'
      : 'No value count constraints';
    return this.i18n.tr(message, {max: this.max});
  }
}
