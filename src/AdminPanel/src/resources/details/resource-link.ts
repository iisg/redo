import {bindable} from "aurelia-templating";
import {Resource} from "../resource";
import {autoinject} from "aurelia-dependency-injection";
import {HasRoleValueConverter} from "../../common/authorization/has-role-value-converter";
import {computedFrom} from "aurelia-binding";

@autoinject
export class ResourceLink {
  @bindable id: number;
  @bindable resource: Resource;

  constructor(private hasRole: HasRoleValueConverter) {
  }

  @computedFrom('resource', 'resource.resourceClass')
  get currentUserIsOperator(): boolean {
    return this.resource && this.hasRole.toView('OPERATOR', this.resource.resourceClass);
  }
}
