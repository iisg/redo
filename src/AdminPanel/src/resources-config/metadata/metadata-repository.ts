import {autoinject} from "aurelia-dependency-injection";
import {Metadata} from "./metadata";
import {cachedResponse, clearCachedResponse, forSeconds, getCachedArgumentsHash} from "common/repository/cached-response";
import {ResourceClassApiRepository} from "common/repository/resource-class-api-repository";
import {DeduplicatingHttpClient} from "common/http-client/deduplicating-http-client";
import {EntitySerializer} from "common/dto/entity-serializer";

@autoinject
export class MetadataRepository extends ResourceClassApiRepository<Metadata> {
  constructor(httpClient: DeduplicatingHttpClient, entitySerializer: EntitySerializer) {
    super(httpClient, entitySerializer, Metadata, 'metadata');
  }

  @cachedResponse(forSeconds())
  public getListByClass(resourceClass: string): Promise<Metadata[]> {
    return super.getListByClass(resourceClass);
  }

  public post(metadata: Metadata): Promise<Metadata> {
    return super.post(metadata).then(metadata => {
      clearCachedResponse(this.getListByClass, getCachedArgumentsHash([metadata.resourceClass]));
      return metadata;
    });
  }

  public getByParent(parent: Metadata): Promise<Metadata[]> {
    return this.httpClient.get(this.oneEntityEndpoint(parent.id) + '/metadata')
      .then(response => this.responseToEntities(response));
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

  public updateOrder(metadataList: Array<Metadata>, resourceClass: string): Promise<boolean> {
    return this.httpClient.put(this.endpoint, metadataList.map(metadata => metadata.id), {resourceClass})
      .then(response => {
        clearCachedResponse(this.getListByClass);
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
    }).then(metadata => clearCachedResponse(this.getListByClass, getCachedArgumentsHash([metadata.resourceClass])) || metadata);
  }

  public getBase(metadata: Metadata): Promise<Metadata> {
    if (metadata.baseId < 0) {
      return new Promise(resolve => resolve(metadata));
    }
    return this.getListByClass(metadata.resourceClass).then(metadataList => {
      return metadataList.filter(base => metadata.baseId == base.id)[0];
    });
  }

  public remove(metadata: Metadata): Promise<any> {
    return super.remove(metadata).then(() => clearCachedResponse(this.getListByClass, getCachedArgumentsHash([metadata.resourceClass])));
  }
}
