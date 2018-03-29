import {Alert, AlertOptions} from "../../../common/dialog/alert";
import {Resource} from "../../../resources/resource";
import {I18N} from "aurelia-i18n";
import {InCurrentLanguageValueConverter} from "../../../resources-config/multilingual-field/in-current-language";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class WorkflowPlaceDeletionAlert {

  private titleLabel: string = 'Pending resources in this state';
  private mainDescriptionLabel: string = 'First remove all resources from this state to delete state';
  private amountDescriptionLabel: string = 'Number of Resources in this state:';
  private exampleDescriptionLabel: string = 'Example resources in this state:';

  constructor(private i18n: I18N,
              private inCurrentLanguage: InCurrentLanguageValueConverter,
              private alert: Alert) {
  }

  public showWorkflowPlaceDeletionAlert(numberOfResources: number, exampleResources: Resource[]) {
    const title = this.i18n.tr(this.titleLabel);
    const mainDescription = `<h3>${this.i18n.tr(this.mainDescriptionLabel)}</h3>`;
    const amountDescription = this.getAmountDescription(numberOfResources);
    const exampleLabelDescription = `<h4>${this.i18n.tr(this.exampleDescriptionLabel)}</h4>`;

    const resourceLinks: string = this.getResourceLinksHtml(exampleResources.length);
    const alertOptions: AlertOptions = this.getalertOptions(exampleResources);

    const message: string = this.getMessage(mainDescription, amountDescription, exampleLabelDescription, resourceLinks);

    this.alert.showHtml(alertOptions, title, message);
  }

  private getMessage(mainDescription: string, amountDescription: string, exampleLabelDescription: string, resourceLinks: string) {
    return `${mainDescription} ${amountDescription} ${exampleLabelDescription} <ul style="text-align: left">${resourceLinks}</ul>`;
  }

  private getAmountDescription(numberOfResources: number): string {
    return `<h4>${this.i18n.tr(this.amountDescriptionLabel)}<strong>${numberOfResources}</strong></h4>`;

  }

  private getalertOptions(exampleResources: Resource[]): AlertOptions {
    let alertOptions: AlertOptions = {type: 'warning', aurelialize: true};
    alertOptions.type = 'warning';
    alertOptions.aurelialize = true;
    let aureliaContent: StringMap<number> = {};
    for (let i = 0; i < exampleResources.length; i++) {
      aureliaContent['id' + i] = exampleResources[i].id;
    }
    alertOptions.aureliaContext = aureliaContent;

    return alertOptions;

  }

  private getResourceLinksHtml(numberOfResources: number) {
    let resourceLinks: string = "";
    for (let i = 0; i < numberOfResources; i++) {
      resourceLinks += `<li><resource-link id.bind="id${i}"></resource-link></li>`;
    }
    return resourceLinks;
  }
}