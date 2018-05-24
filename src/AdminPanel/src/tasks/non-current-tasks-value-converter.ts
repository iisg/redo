import {Resource} from "../resources/resource";
import {CurrentTasksValueConverter} from "./current-tasks-value-converter";
import {valueConverter} from "aurelia-binding";

@valueConverter('nonCurrentTasks')
export class NonCurrentTasksValueConverter extends CurrentTasksValueConverter {
  toView(tasks: Resource[]): any {
    return tasks.filter(t => !this.isMyCurrentTask(t));
  }
}
