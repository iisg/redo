import {HttpClient} from "aurelia-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {Metadata} from "./metadata";
import {ApiRepository} from "../../common/repository/api-repository";

@autoinject
export class MetadataRepository extends ApiRepository<Metadata> {
  constructor(httpClient: HttpClient) {
    super(httpClient, 'metadata');
  }

  public toEntity(data: Object): Metadata {
    return $.extend(new Metadata(), data);
  }
}
