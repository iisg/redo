import {HttpClient} from "aurelia-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {suppressError as suppressErrorHeader} from "common/http-client/headers";
import {Metadata} from "../../resources-config/metadata/metadata";
import {Resource} from "../../resources/resource";
import {EntitySerializer} from "../dto/entity-serializer";
import {debouncePromise} from "../utils/function-utils";

@autoinject
export class BackendValidation {
  private validationQueue = [];

  constructor(private httpClient: HttpClient, private entitySerializer: EntitySerializer) {
  }

  validate(constraintName: string, value: any, metadata: Metadata, resource: Resource): Promise<boolean> {
    const serializedResourceContents = this.entitySerializer.serialize(resource)['contents'];
    const index = this.validationQueue.length;
    this.validationQueue.push({
      constraintName,
      value,
      metadataId: metadata.id,
      resourceId: resource.id,
      kindId: resource.kind.id,
      resourceContents: serializedResourceContents
    });
    return this.validateRequest().then(validationResults => {
      return validationResults[index] && validationResults[index].valid;
    });
  }

  private validateRequest = debouncePromise(() => {
    if (this.validationQueue.length) {
      const response: Promise<any> = this.httpClient.createRequest('validation')
        .asPost()
        .withContent(this.validationQueue)
        .withHeader(suppressErrorHeader.name, suppressErrorHeader.value)
        .send()
        .catch(r => r)
        .then(r => r.content);
      this.validationQueue = [];
      return response;
    }
  }, 100);
}
