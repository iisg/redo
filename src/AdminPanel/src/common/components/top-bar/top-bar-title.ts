import {EventAggregator} from "aurelia-event-aggregator";
import {NavigationInstruction} from "aurelia-router";
import {inlineView} from "aurelia-templating";
import {ContextResourceClass, ResourceClassChangeEvent} from "resources/context/context-resource-class";
import {I18N} from "aurelia-i18n";
import {ResourceClassTranslationValueConverter} from "common/value-converters/resource-class-translation-value-converter";

@inlineView('<template><span>${title}</span></template>')
export class TopBarTitle {
  title: string;
  private resourceClass: string;
  private lastInstruction: NavigationInstruction;

  constructor(eventAggregator: EventAggregator,
              private i18n: I18N,
              private resourceClassTranslation: ResourceClassTranslationValueConverter) {
    eventAggregator.subscribe(ContextResourceClass.CHANGE_EVENT,
      (event: ResourceClassChangeEvent) => this.updateResourceClass(event));
    eventAggregator.subscribe('router:navigation:success',
      (event: { instruction: NavigationInstruction }) => this.updateInstruction(event.instruction));
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
      this.title = this.resourceClassTranslation.toView(configName, this.resourceClass);
    } else {
      const configTitle = this.lastInstruction.config.title;
      if (configTitle) {
        this.title = this.i18n.tr(`navigation::${configTitle}`);
      }
    }
  }
}
