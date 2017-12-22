import {HttpClient} from "aurelia-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {suppressError as suppressErrorHeader} from "common/http-client/headers";

@autoinject
export class BackendValidation {
  constructor(private httpClient: HttpClient) {
  }

  getResult(validationEndpoint: string, value): Promise<boolean> {
    const response: Promise<any> = this.httpClient.createRequest(this.validationEndpoint(validationEndpoint))
      .asPost()
      .withContent(value)
      .withHeader(suppressErrorHeader.name, suppressErrorHeader.value)
      .send();
    return response
      .then(() => true)
      .catch(() => false);
  }

  private validationEndpoint(validationEndpoint: string) {
    return `validation/${validationEndpoint}`;
  }
}
