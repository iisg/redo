import {autoinject} from "aurelia-dependency-injection";
import {HttpClient} from "aurelia-http-client";
import {suppressError as suppressErrorHeader} from "common/http-client/headers";

@autoinject
export class XmlImportClient {
  constructor(private httpClient: HttpClient) {
  }

  public get(id: string): Promise<XMLDocument> {
    return this.httpClient
      .createRequest(`xmlImport/${id}`)
      .asGet()
      .withHeader(suppressErrorHeader.name, suppressErrorHeader.value)
      .send()
      .then(response => $.parseXML(response.content));
  }
}
