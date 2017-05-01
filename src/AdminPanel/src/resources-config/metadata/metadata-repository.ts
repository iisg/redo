import {HttpClient} from "aurelia-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {Metadata} from "./metadata";
import {ApiRepository} from "common/repository/api-repository";
import {cachedResponse, clearCachedResponse} from "common/repository/cached-response";

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

  public saveChild(parentId: number, newChildMetadata?: Metadata, baseId?: number) {
    return this.httpClient.post(this.oneEntityEndpoint(parentId) + '/metadata', {newChildMetadata, baseId})
      .then(response => this.toEntity(response.content));
  }

  public updateOrder(metadataList: Array<Metadata>): Promise<boolean> {
    return this.httpClient.put(this.endpoint, metadataList.map(metadata => metadata.id))
      .then(response => {
        clearCachedResponse(this.getList);
        return response.content;
      });
  }

  public update(updatedMetadata: Metadata): Promise<Metadata> {
    return this.patch(updatedMetadata, {
      label: updatedMetadata.label,
      description: updatedMetadata.description,
      placeholder: updatedMetadata.placeholder,
    }).then(metadata => clearCachedResponse(this.getList) || metadata);
  }

  public toEntity(data: Object): Metadata {
    return $.extend(new Metadata(), data);
  }

  public getBase(metadata: Metadata): Promise<Metadata> {
    return this.getList().then(metadataList => {
      return metadataList.filter(base => metadata.baseId == base.id)[0];
    });
  }
}
