import {autoinject} from "aurelia-dependency-injection";
import {Workflow} from "./workflow";
import {Metadata} from "resources-config/metadata/metadata";
import {ResourceClassApiRepository} from "common/repository/resource-class-api-repository";
import {EntitySerializer} from "common/dto/entity-serializer";
import {DeduplicatingHttpClient} from "common/http-client/deduplicating-http-client";
import {cachedResponse, forSeconds} from "common/repository/cached-response";
import {WorkflowPlugin} from "./details/graph/workflow-plugin";

@autoinject
export class WorkflowRepository extends ResourceClassApiRepository<Workflow> {
  constructor(httpClient: DeduplicatingHttpClient, entitySerializer: EntitySerializer) {
    super(httpClient, entitySerializer, Workflow, 'workflows');
  }

  @cachedResponse(forSeconds(30))
  public get(id: number | string, suppressError: boolean = false): Promise<Workflow> {
    return super.get(id, suppressError);
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

  getPlugins(workflow: Workflow): Promise<WorkflowPlugin[]> {
    return this.httpClient.get(this.oneEntityEndpoint(workflow.id) + '/plugins').then(response => {
      return Promise.all(response.content.map(item => this.entitySerializer.deserialize(WorkflowPlugin, item)));
    }) as any as Promise<WorkflowPlugin[]>;
  }
}
