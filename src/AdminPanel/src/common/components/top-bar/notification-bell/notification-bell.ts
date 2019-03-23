import {TaskRepository} from "tasks/task-repository";
import {autoinject} from "aurelia-dependency-injection";
import {containerless} from "aurelia-templating";
import {TaskCollection, TaskStatus} from "tasks/task-collection";

@autoinject
@containerless
export class NotificationBell {
  numberOfCurrentTasks: number = 0;

  constructor(private taskRepository: TaskRepository) {
    this.fetchTasks();
    setInterval(() => this.fetchTasks(), 60000);
  }

  fetchTasks() {
    this.taskRepository.getList().then((taskCollections: TaskCollection[]) => {
      this.numberOfCurrentTasks = taskCollections
        .filter(taskCollection => taskCollection.taskStatus == TaskStatus.OWN)
        .map(taskCollection => taskCollection.tasks.filter(resource => !!Object.keys(resource.transitionAssigneeMetadata).length).length)
        .reduce((total, length) => total + length, 0);
    });
  }
}
