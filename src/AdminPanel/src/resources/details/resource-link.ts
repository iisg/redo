import {bindable} from "aurelia-templating";
import {ResourceRepository} from "../resource-repository";
import {Resource} from "../resource";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class ResourceLink {
  @bindable id: number;

  private routerParams = {id: undefined};
  private resource: Resource;
  private loading: boolean = false;

  constructor(private resourceRepository: ResourceRepository) {
  }

  idChanged(): void {
    this.routerParams.id = this.id;
    this.loading = true;
    this.resourceRepository.get(this.id)
      .then(resource => this.resource = resource)
      .finally(() => this.loading = false);
  }
}
