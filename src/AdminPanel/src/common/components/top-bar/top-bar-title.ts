import {EventAggregator} from "aurelia-event-aggregator";
import {NavigationInstruction} from "aurelia-router";
import {inlineView} from "aurelia-templating";
import {ResourceClassChangeEvent, ContextResourceClass} from "resources/context/context-resource-class";

@inlineView('<template><span>${title | t}</span></template>')
export class TopBarTitle {
  title: string;
  private resourceClass: string;
  private lastInstruction: NavigationInstruction;

  constructor(eventAggregator: EventAggregator) {

    eventAggregator.subscribe(ContextResourceClass.CHANGE_EVENT,
      (event: ResourceClassChangeEvent) => this.updateResourceClass(event));
    eventAggregator.subscribe('router:navigation:success',
      (event: {instruction: NavigationInstruction}) => this.updateInstruction(event.instruction));
  }

  private updateResourceClass(event: ResourceClassChangeEvent): void {
    this.resourceClass = event.newResourceClass;
    this.updateTitle();
  }

  private updateInstruction(instruction: NavigationInstruction): void {
    this.lastInstruction = instruction;
    this.updateTitle();
  }

  private updateTitle(): void {
    if (!this.lastInstruction) {
      return;
    }
    const configName = this.lastInstruction.config.name;
    if (this.resourceClass) {
      this.title = `resource_classes::${this.resourceClass}//${configName}`;
    }
    else {
      const configTitle = this.lastInstruction.config.title;
      this.title = `navigation::${configTitle}`;
    }
  }
}