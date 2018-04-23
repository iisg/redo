import {Resource} from "../../../resources/resource";
import * as Handlebars from "handlebars";
import {ResourceKind} from "../resource-kind";
import {autoinject} from "aurelia-dependency-injection";
import * as handlebarsHelpers from "./resource-display-strategy-handlebars-helpers";
import {DISPLAY_STRATEGIES} from "./display-strategies";

@autoinject
export class ResourceDisplayStrategyEvaluator {
  private defaultTemplate: HandlebarsTemplateDelegate;
  private handlebars: typeof Handlebars;

  constructor() {
    this.handlebars = Handlebars.create();
    for (let helper in handlebarsHelpers) {
      this.handlebars.registerHelper(helper, handlebarsHelpers[helper]);
    }
    this.defaultTemplate = this.handlebars.compile('#{{id}}');
  }

  public getDisplayValue(resource: Resource, strategyId: string): string {
    if (!resource) {
      return '';
    }
    const strategy = DISPLAY_STRATEGIES.indexOf(strategyId) >= 0 ? resource.kind.displayStrategies[strategyId] : strategyId;
    const template = this.compileTemplateOrDefault(strategy);
    return template(new ResourceDisplayStrategyTemplateData(resource));
  }

  public compileTemplate(template: string) {
    const compiled = this.handlebars.compile(template);
    compiled({}); // detects possible template runtime errors
    return compiled;
  }

  public compileTemplateOrDefault(template: string): HandlebarsTemplateDelegate {
    if (!template) {
      return this.defaultTemplate;
    } else {
      try {
        return this.compileTemplate(template);
      } catch (error) {
        console.warn(error); // tslint:disable-line
        return this.defaultTemplate;
      }
    }
  }
}

class ResourceDisplayStrategyTemplateData {
  private id: number;
  private kind: ResourceKind;

  constructor(resource: Resource) {
    this.id = resource.id;
    this.kind = resource.kind;
    for (let metadataId in resource.contents) {
      this["m" + metadataId] = resource.contents[metadataId];
    }
  }
}
