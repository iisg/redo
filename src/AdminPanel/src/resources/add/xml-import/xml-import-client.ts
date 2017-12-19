import {autoinject} from "aurelia-dependency-injection";
import {HttpClient} from "aurelia-http-client";
import {suppressError as suppressErrorHeader} from "common/http-client/headers";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";

@autoinject
export class XmlImportClient {
  constructor(private httpClient: HttpClient) {
  }

  public getMetadataValues(id: string, config: string, resourceKind: ResourceKind): Promise<ImportResult> {
    return this.httpClient
      .createRequest(`xml-import/${id}`)
      .asPost()
      .withContent({config, resourceKind: resourceKind.id})
      .withHeader(suppressErrorHeader.name, suppressErrorHeader.value)
      .send()
      .then(response => response.content);
  }
}

export interface ImportResult {
  acceptedValues: StringMap<string[]>;
  unfitTypeValues: StringMap<string[]>;
  invalidMetadataKeys: string[];
}
