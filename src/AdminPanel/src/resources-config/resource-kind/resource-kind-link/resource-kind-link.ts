import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {ResourceKind} from "../resource-kind";
import {ResourceKindRepository} from "../resource-kind-repository";
import {cachedResponse, forSeconds} from "../../../common/repository/cached-response";

@autoinject
export class ResourceKindLink {
  @bindable id: number;
  resourceKind: ResourceKind;
  loading: boolean = false;

  constructor(private resourceKindRepository: ResourceKindRepository) {
  }

  idChanged(): void {
    if (this.id) {
      this.loading = true;
      this.fetchMetadata(this.id)
        .then(resourceKind => this.resourceKind = resourceKind)
        .finally(() => this.loading = false);
    }
  }

  @cachedResponse(forSeconds(30))
  private fetchMetadata(id: number): Promise<ResourceKind> {
    return this.resourceKindRepository.get(id, true);
  }
}
