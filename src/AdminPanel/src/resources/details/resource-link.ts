import {bindable} from "aurelia-templating";
import {ResourceRepository} from "../resource-repository";
import {Resource} from "../resource";
import {autoinject} from "aurelia-dependency-injection";
import {cachedResponse, forSeconds} from "../../common/repository/cached-response";
import {HasRoleValueConverter} from "../../common/authorization/has-role-value-converter";
import {computedFrom} from "aurelia-binding";

@autoinject
export class ResourceLink {
  @bindable id: number;
  @bindable resource: Resource;

  loading: boolean = false;

  constructor(private resourceRepository: ResourceRepository, private hasRole: HasRoleValueConverter) {
  }

  idChanged(): void {
    if (this.id) {
      this.loading = true;
      this.fetchResource(this.id)
        .then(resource => this.resource = resource)
        .finally(() => this.loading = false);
    }
  }

  @cachedResponse(forSeconds(30))
  private fetchResource(id: number): Promise<Resource> {
    return this.resourceRepository.get(id, true);
  }

  @computedFrom('resource', 'resource.resourceClass')
  get currentUserIsOperator(): boolean {
    return this.hasRole.toView('OPERATOR', this.resource.resourceClass);
  }
}
