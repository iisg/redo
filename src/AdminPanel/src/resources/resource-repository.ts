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

  protected toBackend(resource: Resource): Object {
    return this.prepareFormData(resource);
  }

  public getListWithSystemResourceKinds(): Promise<Resource[]> {
    return this.httpClient
      .createRequest(this.endpoint)
      .asGet()
      .withParams({systemResourceKind: true})
      .send()
      .then(response => this.responseToEntities(response));
  }

  public getByParent(parent: Resource): Promise<Resource[]> {
    const parentId: number = (parent == undefined) ? 0 : parent.id;
    const endpoint = this.endpoint + `/${parentId}/resources`;
    return this.httpClient.get(endpoint).then(response => this.responseToEntities(response));
  }

  public toEntity(data: Object): Promise<Resource> {
    return this.resourceKindRepository.get(data['kindId']).then(resourceKind => {
      delete data['kindId'];
      let resource: Resource = $.extend(new Resource(), data);
      resource.kind = resourceKind;
      for (const metadata of resource.kind.metadataList) {
        if (!resource.contents.hasOwnProperty(metadata.baseId)) {
          resource.contents[metadata.baseId] = [];
        }
      }
      return resource;
    });
  }

  update(resource: Resource): Promise<Resource> {
    return this.postOne(resource);
  }

  applyTransition(resource: Resource, transitionId: string): Promise<Resource> {
    return this.patch(resource, {transitionId});
  }

  private prepareFormData(resource: Resource): FormData {
    let formData = new FormData();
    let resourceCopy = new Resource();
    let fileCounter = 0;

    for (let metadataId in resource.contents) {
      if (resource.contents[metadataId].length > 0) {
        resourceCopy.contents[metadataId] = resource.contents[metadataId].map(file => {
          if (!(file instanceof File)) {
            return file;
          }
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

  private postOne(entity: Resource) {
    return this.httpClient.post(this.oneEntityEndpoint(entity), this.toBackend(entity)).then(response => this.toEntity(response.content));
  }
}
