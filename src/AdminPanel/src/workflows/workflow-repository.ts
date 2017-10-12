import {ApiRepository} from "common/repository/api-repository";
import {autoinject} from "aurelia-dependency-injection";
import {HttpClient} from "aurelia-http-client";
import {Workflow} from "./workflow";
import {workflowPlaceToEntity, workflowPlaceToBackend} from "./workflow-place-converters";
import {Metadata} from "../resources-config/metadata/metadata";

@autoinject
export class WorkflowRepository extends ApiRepository<Workflow> {
  constructor(httpClient: HttpClient) {
    super(httpClient, 'workflows');
  }

  public toEntity(data: Object): Workflow {
    data['places'] = data['places'].map(workflowPlaceToEntity);
    return $.extend(new Workflow(), data);
  }

  protected toBackend(entity: Workflow): Object {
    const backendEntity = super.toBackend(entity);
    backendEntity['places'] = backendEntity['places'].map(workflowPlaceToBackend);
    return backendEntity;
  }

  public simulate(workflow: Workflow, fromState?: string[], transition?: string): Promise<any> {
    return this.httpClient.post(this.endpoint + '/simulation', {
      workflow: this.toBackend(workflow),
      fromState: fromState,
      transition: transition
    }).then(response => response.content);
  }

  update(workflow: Workflow): Promise<Workflow> {
    return this.put(workflow);
  }

  getByAssigneeMetadata(metadata: Metadata): Promise<Workflow[]> {
    const endpoint = `${this.endpoint}?assigneeMetadata=${metadata.id}`;
    return this.httpClient.get(endpoint).then(response => this.responseToEntities(response));
  }
}
