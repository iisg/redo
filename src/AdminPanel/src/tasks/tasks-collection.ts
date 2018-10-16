import {Resource} from "resources/resource";
import {automapped, map} from "common/dto/decorators";

@automapped
export class TasksCollection {
  static NAME = 'TasksCollection';

  @map resourceClass: string;
  @map('Resource[]') myTasks: Resource[] = [];
  @map('Resource[]') possibleTasks: Resource[] = [];
}
