import {Resource} from "../resources/resource";

export class CurrentTasksValueConverter implements ToViewValueConverter {
  toView(tasks: Resource[]): any {
    return tasks.filter(resource => Object.keys(resource.transitionAssigneeMetadata).length > 0);
  }
}
