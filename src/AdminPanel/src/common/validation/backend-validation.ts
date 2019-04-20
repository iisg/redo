import {HttpClient} from "aurelia-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {suppressError as suppressErrorHeader} from "common/http-client/headers";
import {Metadata} from "../../resources-config/metadata/metadata";
import {Resource} from "../../resources/resource";
import {EntitySerializer} from "../dto/entity-serializer";

@autoinject
export class BackendValidation {
  constructor(private httpClient: HttpClient, private entitySerializer: EntitySerializer) {
  }

  getResult(constraintName: string, value: any, metadata: Metadata, resource: Resource): Promise<boolean> {
    const serializedResourceContents = this.entitySerializer.serialize(resource)['contents'];
    const response: Promise<any> = this.httpClient.createRequest('validation')
      .asPost()
      .withContent({constraintName, value, metadataId: metadata.id, resourceId: resource.id, resourceContents: serializedResourceContents})
      .withHeader(suppressErrorHeader.name, suppressErrorHeader.value)
      .send();
    return response
      .then(() => true)
      .catch(() => false);
  }
}
