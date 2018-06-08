import {Resource} from "../resources/resource";
import {map, automapped} from "../common/dto/decorators";

@automapped
export class TasksCollection {
  static NAME = 'TasksCollection';

  @map resourceClass: string;
  @map('Resource[]') myTasks: Resource[] = [];
}
