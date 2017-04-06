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

  public put(resource: Resource) {
    return this.httpClient.post(this.oneEntityEndpoint(resource), this.toBackend(resource))
      .then(response => this.toEntity(response.content));
  }

  protected toBackend(resource: Resource): Object {
    return this.prepareFormData(resource);

  }

  private prepareFormData(resource: Resource): FormData {
    let formData = new FormData();
    let resourceCopy = new Resource();

    for (let metadataId in resource.contents) {
      if (resource.contents[metadataId] instanceof File) {
        resourceCopy.contents[metadataId] = this.wrapFileWithFormData(formData, resource, metadataId);
      } else
        resourceCopy.contents[metadataId] = resource.contents[metadataId];
    }
    formData.append('id', resource.id);
    formData.append('kind_id', resource.kind.id);
    formData.append('contents', JSON.stringify(resourceCopy.contents));
    return formData;
  }

  private wrapFileWithFormData(formData: FormData, resource: Resource, metadataId: any): string {
    let file = resource.contents[metadataId];
    let fileName = resource.contents[metadataId].name;
    let resourceName;

    for (let metadata of resource.kind.metadataList) {
      if (metadata.baseId == metadataId) {
        resourceName = metadata.id;
        break;
      }
    }
    formData.append(resourceName, file, fileName);

    return resourceName.toString();
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
