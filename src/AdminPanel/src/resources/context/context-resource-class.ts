import {EventAggregator} from 'aurelia-event-aggregator';
import {autoinject} from 'aurelia-dependency-injection';

@autoinject
export class ContextResourceClass {
  public static readonly CHANGE_EVENT: string = 'resourceClassChanged';

  private currentResourceClass: string;
  private unsetOnNewRoute: boolean = true;

  constructor(private eventAggregator: EventAggregator) {
    this.eventAggregator.subscribe('router:navigation:processing', () => this.prepareToUnset());
    this.eventAggregator.subscribe('router:navigation:success', () => this.unsetIfNecessary());
  }

  setCurrent(newResourceClass: string): void {
    this.unsetOnNewRoute = false;
    this.publishIfChanged(newResourceClass);
  }

  private prepareToUnset(): void {
    this.unsetOnNewRoute = true;
  }

  private unsetIfNecessary(): void {
    if (this.unsetOnNewRoute) {
      this.publishIfChanged(undefined);
    }
  }

  private publishIfChanged(newResourceClass: string): void {
    if (this.currentResourceClass != newResourceClass) {
      this.eventAggregator.publish(ContextResourceClass.CHANGE_EVENT, {newResourceClass: newResourceClass});
      this.currentResourceClass = newResourceClass;
    }
  }
}

export interface ResourceClassChangeEvent {
  newResourceClass: string;
}
