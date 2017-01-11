import {HttpClient} from "aurelia-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {Metadata} from "./metadata";
import {ApiRepository} from "../../common/repository/api-repository";
import {cachedResponse, clearCachedResponse} from "../../common/repository/cached-response";

@autoinject
export class MetadataRepository extends ApiRepository<Metadata> {
  constructor(httpClient: HttpClient) {
    super(httpClient, 'metadata');
  }

  @cachedResponse()
  public getList(): Promise<Metadata[]> {
    return super.getList();
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
}
