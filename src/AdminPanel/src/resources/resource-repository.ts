import {HttpClient} from "aurelia-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {ApiRepository} from "common/repository/api-repository";
import {Resource} from "./resource";
import {ResourceKindRepository} from "resources-config/resource-kind/resource-kind-repository";

@autoinject
export class ResourceRepository extends ApiRepository<Resource> {
  constructor(httpClient: HttpClient, private resourceKindRepository: ResourceKindRepository) {
    super(httpClient, 'resources');
  }

  protected toBackend(entity: Resource): Object {
    return {
      id: entity.id,
      kind_id: entity.kind.id,
      contents: entity.contents,
    };
  }

  public toEntity(data: Object): Promise<Resource> {
    return this.resourceKindRepository.get(data['kindId']).then(resourceKind => {
      delete data['kindId'];
      let resource = $.extend(new Resource(), data);
      resource.kind = resourceKind;
      return resource;
    });
  }

  update(resource: Resource): Promise<Resource> {
    return this.put(resource);
  }

  applyTransition(resource: Resource, transitionId: string): Promise<Resource> {
    return this.patch(resource, {transitionId});
  }
}
