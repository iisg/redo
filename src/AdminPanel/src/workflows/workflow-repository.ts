import {ApiRepository} from "common/repository/api-repository";
import {autoinject} from "aurelia-dependency-injection";
import {HttpClient} from "aurelia-http-client";
import {Workflow} from "./workflow";

@autoinject
export class WorkflowRepository extends ApiRepository<Workflow> {
  constructor(httpClient: HttpClient) {
    super(httpClient, 'workflows');
  }

  public toEntity(data: Object): Workflow {
    return $.extend(new Workflow(), data);
  }

  public simulate(workflow: Workflow, fromState?: Array<string>, transition?: string): Promise<any> {
    return this.httpClient.post(this.endpoint + '/simulation', {
      workflow: workflow,
      fromState: fromState,
      transition: transition
    }).then(response => response.content);
  }

  update(workflow: Workflow): Promise<Workflow> {
    return this.put(workflow);
  }
}
