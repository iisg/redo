import {autoinject} from "aurelia-dependency-injection";
import {Metadata} from "./metadata";
import {DeduplicatingHttpClient} from "common/http-client/deduplicating-http-client";
import {EntitySerializer} from "common/dto/entity-serializer";
import {MetadataListQuery} from "./metadata-list-query";
import {ApiRepository} from "../../common/repository/api-repository";
import {cachedResponse, forOneMinute} from "../../common/repository/cached-response";

@autoinject
export class MetadataRepository extends ApiRepository<Metadata> {
  constructor(httpClient: DeduplicatingHttpClient, entitySerializer: EntitySerializer) {
    super(httpClient, entitySerializer, Metadata, 'metadata');
  }

  public getListQuery(): MetadataListQuery {
    return new MetadataListQuery(this.httpClient, this.endpoint, this.entitySerializer);
  }

  public saveChild(parentId: number, newChildMetadata: Metadata, baseId?: number) {
    let postChild = this.httpClient.post(
      this.oneEntityEndpoint(parentId) + '/metadata',
      {newChildMetadata: this.toBackend(newChildMetadata), baseId}
    ).then(response => this.toEntity(response.content));
    return baseId
      ? newChildMetadata.clearInheritedValues(this).then(() => postChild)
      : postChild;
  }

  public updateOrder(metadataList: Array<Metadata>, resourceClass: string): Promise<boolean> {
    return this.httpClient.put(this.endpoint, metadataList.map(metadata => metadata.id), {resourceClass})
      .then(response => response.content);
  }

  public update(updatedMetadata: Metadata): Promise<Metadata> {
    updatedMetadata = this.toBackend(updatedMetadata) as Metadata;
    return this.patch(updatedMetadata, {
      label: updatedMetadata.label,
      description: updatedMetadata.description,
      placeholder: updatedMetadata.placeholder,
      constraints: updatedMetadata.constraints,
      shownInBrief: updatedMetadata.shownInBrief,
    });
  }

  @cachedResponse(forOneMinute())
  public getHierarchy(id: number): Promise<Metadata[]> {
    return this.httpClient.get(this.oneEntityEndpoint(id) + '/hierarchy').then(response => this.responseToEntities(response));
  }
}
