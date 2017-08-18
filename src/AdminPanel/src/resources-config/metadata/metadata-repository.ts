import {HttpClient} from "aurelia-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {Metadata} from "./metadata";
import {ApiRepository} from "common/repository/api-repository";
import {cachedResponse, clearCachedResponse} from "common/repository/cached-response";
import {deepCopy} from "common/utils/object-utils";

@autoinject
export class MetadataRepository extends ApiRepository<Metadata> {
  constructor(httpClient: HttpClient) {
    super(httpClient, 'metadata');
  }

  @cachedResponse()
  public getList(): Promise<Metadata[]> {
    return super.getList();
  }

  public getChildren(parentId: number): Promise<Metadata[]> {
    return this.httpClient.get(this.oneEntityEndpoint(parentId) + '/metadata')
      .then(response => Promise.all(response.content.map(item => this.toEntity(item))));
  }

  public saveChild(parentId: number, newChildMetadata: Metadata, baseId?: number) {
    let postChild = this.httpClient.post(
      this.oneEntityEndpoint(parentId) + '/metadata',
      {newChildMetadata: this.toBackend(newChildMetadata), baseId}
    ).then(response => this.toEntity(response.content));
    return baseId
      ? newChildMetadata.clearInheritedValues(this, baseId).then(() => postChild)
      : postChild;
  }

  public updateOrder(metadataList: Array<Metadata>): Promise<boolean> {
    return this.httpClient.put(this.endpoint, metadataList.map(metadata => metadata.id))
      .then(response => {
        clearCachedResponse(this.getList);
        return response.content;
      });
  }

  public update(updatedMetadata: Metadata): Promise<Metadata> {
    updatedMetadata = this.toBackend(updatedMetadata) as Metadata;
    return this.patch(updatedMetadata, {
      label: updatedMetadata.label,
      description: updatedMetadata.description,
      placeholder: updatedMetadata.placeholder,
      constraints: updatedMetadata.constraints,
      shownInBrief: updatedMetadata.shownInBrief,
    }).then(metadata => clearCachedResponse(this.getList) || metadata);
  }

  public toEntity(data: Object): Metadata {
    return Metadata.clone(data);
  }

  public getBase(metadata: Metadata): Promise<Metadata> {
    return this.getList().then(metadataList => {
      return metadataList.filter(base => metadata.baseId == base.id)[0];
    });
  }

  public toBackend(entity: Metadata): Object {
    entity = deepCopy(entity);
    this.removeExcessiveConstraints(entity);
    this.replaceEntitiesWithIds(entity);
    return entity;
  }

  private removeExcessiveConstraints(entity: Metadata) {
    if (entity.control != 'relationship') {
      delete entity.constraints['resourceKind'];
    }
  }

  private replaceEntitiesWithIds(entity: Metadata) {
    if (entity.constraints.hasOwnProperty('resourceKind')) {
      entity.constraints.resourceKind = (entity.constraints.resourceKind as any[]).map(this.mapToIdIfPossible);
    }
  }

  private mapToIdIfPossible(object: Object): number {
    return object.hasOwnProperty('id') ? object['id'] : object;
  }
}
