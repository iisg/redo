import {HttpClient} from "aurelia-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {suppressError as suppressErrorHeader} from "common/http-client/headers";
import {BackendConstraintValidator} from "./rules/constraints/constraint-validator";

@autoinject
export class BackendValidation {
  constructor(private httpClient: HttpClient) {
  }

  getResult(validator: BackendConstraintValidator, content: Object): Promise<boolean> {
    return this.getPrevalidationResult(validator, content) || this.getQueryResult(validator, content);
  }

  private getPrevalidationResult(validator: BackendConstraintValidator, content: Object): Promise<boolean>|undefined {
    const prevalidationResult = validator.validateOnFrontend(content);
    if (prevalidationResult !== undefined) {
      if (typeof prevalidationResult == 'boolean') {
        return Promise.resolve(prevalidationResult);
      } else {
        throw new Error(
          `Unknown value returned from prevalidation method - it should be either true, false or undefined, got '${prevalidationResult}'`
        );
      }
    }
    return undefined;
  }

  private getQueryResult(validator: BackendConstraintValidator, content: Object): Promise<boolean> {
    const response: Promise<any> = this.httpClient.createRequest(this.validationEndpoint(validator.endpointName))
      .asPost()
      .withContent(content)
      .withHeader(suppressErrorHeader.name, suppressErrorHeader.value)
      .send();
    return response
      .then(() => true)
      .catch(() => false);
  }

  private validationEndpoint(validatorName: string) {
    return `validation/${validatorName}`;
  }
}
