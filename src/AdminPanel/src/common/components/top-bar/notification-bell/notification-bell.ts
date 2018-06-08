import {TaskFinder} from "../../../../tasks/task-finder";
import {autoinject} from "aurelia-dependency-injection";
import {containerless} from "aurelia-templating";

@autoinject
@containerless
export class NotificationBell {
  numberOfCurrentTasks: number = 0;

  constructor(private taskFinder: TaskFinder) {
    this.fetchTasks();
    setInterval(() => this.fetchTasks(), 60000);
  }

  fetchTasks() {
    this.taskFinder.getList().then(tasksCollection => {
      for (let tasks of tasksCollection) {
        this.numberOfCurrentTasks += tasks.myTasks.filter(resource => !!Object.keys(resource.transitionAssigneeMetadata).length).length;
      }
    });
  }
}
