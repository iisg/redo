import {autoinject} from "aurelia-dependency-injection";
import {TaskFinder} from "./task-finder";
import {ComponentAttached} from "aurelia-templating";
import {ResourceKindRepository} from "../resources-config/resource-kind/resource-kind-repository";
import {getMergedBriefMetadata} from "../common/utils/metadata-utils";
import {Metadata} from "../resources-config/metadata/metadata";
import {TasksCollection} from "./tasks-collection";
import {computedFrom} from "aurelia-binding";

@autoinject
export class Tasks implements ComponentAttached {
  tasksCollection: TasksCollection[];
  briefMetadata: AnyMap<Metadata[]> = {};

  constructor(private taskFinder: TaskFinder, private resourceKindRepository: ResourceKindRepository) {
  }

  attached() {
    this.taskFinder.getList().then(async(tasksCollection) => {
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
}
