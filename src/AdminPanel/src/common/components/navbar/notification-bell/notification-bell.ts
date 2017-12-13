import {TaskFinder} from "../../../../tasks/task-finder";
import {autoinject} from "aurelia-dependency-injection";
import {containerless} from "aurelia-templating";

@autoinject
@containerless
export class NotificationBell {
  numberOfCurrentTasks: number;

  constructor(private taskFinder: TaskFinder) {
    this.fetchTasks();
    setInterval(() => this.fetchTasks(), 60000);
  }

  fetchTasks() {
    this.taskFinder.getList().then(tasks => {
      this.numberOfCurrentTasks = tasks.filter(resource => Object.keys(resource.transitionAssigneeMetadata).length > 0).length;
    });
  }
}
