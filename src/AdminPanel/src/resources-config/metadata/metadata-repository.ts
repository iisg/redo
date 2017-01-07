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

  public toEntity(data: Object): Metadata {
    return $.extend(new Metadata(), data);
  }
}
