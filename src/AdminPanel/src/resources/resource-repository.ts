import {HttpClient} from "aurelia-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {Resource} from "./resource";
import {ResourceKindRepository} from "resources-config/resource-kind/resource-kind-repository";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {workflowPlaceToEntity} from "workflows/workflow-place-converters";
import {ResourceListQuery} from "./resource-list-query";
import {ApiRepository} from "../common/repository/api-repository";

@autoinject
export class ResourceRepository extends ApiRepository<Resource> {
  constructor(httpClient: HttpClient, private resourceKindRepository: ResourceKindRepository) {
    super(httpClient, 'resources');
  }

  protected toBackend(resource: Resource): Object {
    return this.prepareFormData(resource);
  }

  public getListQuery(): ResourceListQuery {
    return new ResourceListQuery(this.httpClient, this);
  }

  public getByParent(parent: Resource): Promise<Resource[]> {
    const parentId: number = (parent == undefined) ? 0 : parent.id;
    const endpoint = this.endpoint + `/${parentId}/resources`;
    return this.httpClient.get(endpoint).then(response => this.responseToEntities(response));
  }

  public toEntity(data: Object): Promise<Resource> {
    return this.resourceKindRepository.getResourceKind(data['kindId']).then(resourceKind => {
      delete data['kindId'];
      let resource: Resource = $.extend(new Resource(), data);
      resource.kind = resourceKind;
      resource.currentPlaces = (data['currentPlaces'] || []).map(workflowPlaceToEntity);
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
        resourceCopy.contents[metadataId] = resource.contents[metadataId].map(item => {
          if (!(item instanceof File)) {
            return item;
          }
          fileCounter++;
          return this.wrapFileWithFormData(formData, item, resource.kind, metadataId, fileCounter);
        });
      } else {
        resourceCopy.contents[metadataId] = [];
      }
    }
    formData.append('id', resource.id);
    formData.append('kind_id', resource.kind.id);
    formData.append('resourceClass', resource.resourceClass);
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
