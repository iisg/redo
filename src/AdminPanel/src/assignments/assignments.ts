import {autoinject} from "aurelia-dependency-injection";
import {AssignmentFinder} from "./assignment-finder";
import {ComponentAttached} from "aurelia-templating";
import {Resource} from "../resources/resource";

@autoinject
export class Assignments implements ComponentAttached {
  assignments: Resource[];

  constructor(private assignmentFinder: AssignmentFinder) {
  }

  async attached() {
    this.assignments = await this.assignmentFinder.getList();
  }
}
