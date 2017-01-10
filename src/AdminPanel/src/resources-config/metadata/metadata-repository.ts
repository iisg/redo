import {HttpClient, HttpResponseMessage} from "aurelia-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {Metadata} from "./metadata";
import {ApiRepository} from "../../common/repository/api-repository";

@autoinject
export class MetadataRepository extends ApiRepository<Metadata> {
  constructor(httpClient: HttpClient) {
    super(httpClient, 'metadata');
  }

  public updateOrder(metadataList: Array<Metadata>): Promise<HttpResponseMessage> {
    return this.httpClient.put(this.endpoint, metadataList.map(metadata => metadata.id));
  }

  public update(updatedMetadata: Metadata): Promise<Metadata> {
    return this.patch(updatedMetadata, {
      label: updatedMetadata.label,
      description: updatedMetadata.description,
      placeholder: updatedMetadata.placeholder,
    });
  }

  public toEntity(data: Object): Metadata {
    return $.extend(new Metadata(), data);
  }
}
