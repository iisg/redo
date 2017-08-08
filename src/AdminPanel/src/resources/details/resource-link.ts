import {bindable} from "aurelia-templating";
import {ResourceRepository} from "../resource-repository";
import {Resource} from "../resource";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class ResourceLink {
  @bindable id: number;

  routerParams = {id: undefined};
  resource: Resource;
  loading: boolean = false;

  constructor(private resourceRepository: ResourceRepository) {
  }

  idChanged(): void {
    this.routerParams.id = this.id;
    this.loading = true;
    this.resourceRepository.get(this.id)
      .catch(() => Promise.resolve(undefined))
      .then(resource => this.resource = resource)
      .finally(() => this.loading = false);
  }
}
