import {bindable} from "aurelia-templating";
import {ResourceRepository} from "../resource-repository";
import {Resource} from "../resource";
import {autoinject} from "aurelia-dependency-injection";
import {cachedResponse, forSeconds} from "../../common/repository/cached-response";

@autoinject
export class ResourceLink {
  @bindable id: number;

  routerParams = {id: undefined};
  resource: Resource;
  loading: boolean = false;

  constructor(private resourceRepository: ResourceRepository) {
  }

  idChanged(): void {
    if (this.id) {
      this.loading = true;
      this.fetchResource(this.id)
        .then(resource => this.onResourceFetched(resource))
        .finally(() => this.loading = false);
    }
  }

  @cachedResponse(forSeconds(30))
  private fetchResource(id: number): Promise<Resource> {
    return this.resourceRepository.get(id, true);
  }

  private onResourceFetched(resource: Resource): Promise<void> | void {
    this.resource = resource;
    this.routerParams.id = this.id;
  }
}
