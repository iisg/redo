import {Resource} from "resources/resource";
import {automapped, map} from "common/dto/decorators";
import {PageResult} from "resources/page-result";
import {PageResultResourceMapper} from "tasks/task-collection-mapping";

@automapped
export class TaskCollection {
  static NAME = 'TaskCollection';

  @map resourceClass: string;
  @map taskStatus: TaskStatus;
  @map(PageResultResourceMapper) tasks: PageResult<Resource> = new PageResult();
}

export enum TaskStatus {
  OWN = 'own',
  POSSIBLE = 'possible'
}