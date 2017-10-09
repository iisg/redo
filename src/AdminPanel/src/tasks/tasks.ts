import {autoinject} from "aurelia-dependency-injection";
import {TaskFinder} from "./task-finder";
import {ComponentAttached} from "aurelia-templating";
import {Resource} from "../resources/resource";

@autoinject
export class Tasks implements ComponentAttached {
  tasks: Resource[];

  constructor(private taskFinder: TaskFinder) {
  }

  async attached() {
    this.tasks = await this.taskFinder.getList();
  }
}
