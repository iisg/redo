import {HttpClient} from "aurelia-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {ApiRepository} from "common/repository/api-repository";
import {Resource} from "./resource";
import {ResourceKindRepository} from "resources-config/resource-kind/resource-kind-repository";
import {ResourceKind} from "../resources-config/resource-kind/resource-kind";

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
    let fileCounter = 0;

    for (let metadataId in resource.contents) {
      if (resource.contents[metadataId].length > 0 && resource.contents[metadataId][0] instanceof File) {
        resourceCopy.contents[metadataId] = resource.contents[metadataId].map(file => {
          fileCounter++;
          return this.wrapFileWithFormData(formData, file, resource.kind, metadataId, fileCounter);
        });
      } else {
        resourceCopy.contents[metadataId] = resource.contents[metadataId];
      }
    }
    formData.append('id', resource.id);
    formData.append('kind_id', resource.kind.id);
    formData.append('contents', JSON.stringify(resourceCopy.contents));
    return formData;
  }

  private wrapFileWithFormData(formData: FormData, file: File, resourceKind: ResourceKind, metadataId: any, fileIndex: number): string {
    let resourceName;
    for (let metadata of resourceKind.metadataList) {
      if (metadata.baseId == metadataId) {
        resourceName = `metadata${metadata.id}_file${fileIndex}`;
        formData.append(resourceName, file, file.name);
        return resourceName;
      }
    }
    throw new Error(`Matching base metadata ${metadataId} not found in resource kind ${resourceKind.id}`);
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
