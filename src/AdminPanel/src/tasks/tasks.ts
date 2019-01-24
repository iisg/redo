import {computedFrom} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {ComponentAttached} from "aurelia-templating";
import {getMergedBriefMetadata} from "../common/utils/metadata-utils";
import {Metadata} from "../resources-config/metadata/metadata";
import {ResourceKindRepository} from "../resources-config/resource-kind/resource-kind-repository";
import {TaskFinder} from "./task-finder";
import {TasksCollection} from "./tasks-collection";

@autoinject
export class Tasks implements ComponentAttached {
  tasksCollection: TasksCollection[];
  briefMetadata: AnyMap<Metadata[]> = {};

  constructor(private taskFinder: TaskFinder, private resourceKindRepository: ResourceKindRepository) {
  }

  attached() {
    this.taskFinder.getList().then(async (tasksCollection) => {
      this.tasksCollection = tasksCollection;
      for (let tasks of tasksCollection) {
        this.briefMetadata[tasks.resourceClass] = getMergedBriefMetadata(
          await this.resourceKindRepository.getListQuery().filterByResourceClasses(tasks.resourceClass).get()
        );
      }
    });
  }

  @computedFrom('tasksCollection', 'tasksCollection.length')
  get myTasksExists(): boolean {
    return !!this.tasksCollection.filter(tasks => tasks.myTasks.length > 0).length;
  }

  @computedFrom('tasksCollection', 'tasksCollection.length')
  get possibleTasksExists(): boolean {
    return !!this.tasksCollection.filter(tasks => tasks.possibleTasks.length > 0).length;
  }
}
