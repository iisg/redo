import {HttpClient} from "aurelia-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {ApiRepository} from "common/repository/api-repository";
import {Resource} from "./resource";
import {ResourceKindRepository} from "../resources-config/resource-kind/resource-kind-repository";

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

  public toEntity(data: Object): Resource {
    let resource = $.extend(new Resource(), data);
    resource.kind = this.resourceKindRepository.toEntity(resource.kind);
    return resource;
  }
}
