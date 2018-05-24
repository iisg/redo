import {Resource} from "../resources/resource";

export class CurrentTasksValueConverter implements ToViewValueConverter {
  toView(tasks: Resource[]): any {
    return tasks.filter(t => this.isMyCurrentTask(t));
  }

  protected isMyCurrentTask(task: Resource) {
    for (let metadataId in task.transitionAssigneeMetadata) {
      const applicableTransitions = task.transitionAssigneeMetadata[metadataId].filter(t => task.canApplyTransition(t));
      if (applicableTransitions.length > 0) {
        return true;
      }
    }
    return false;
  }
}
