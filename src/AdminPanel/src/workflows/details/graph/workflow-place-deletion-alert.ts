import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {Alert, AlertOptions} from "../../../common/dialog/alert";
import {Resource} from "../../../resources/resource";

@autoinject
export class WorkflowPlaceDeletionAlert {

  private titleLabel: string = 'Pending resources in this state';
  private mainDescriptionLabel: string = 'First remove all resources from this state to delete state';
  private amountDescriptionLabel: string = 'Number of Resources in this state:';
  private exampleDescriptionLabel: string = 'Example resources in this state:';

  constructor(private i18n: I18N, private alert: Alert) {
  }

  public showWorkflowPlaceDeletionAlert(numberOfResources: number, exampleResources: Resource[]) {
    const title = this.i18n.tr(this.titleLabel);
    const mainDescription = `<h3>${this.i18n.tr(this.mainDescriptionLabel)}.</h3>`;
    const amountDescription = this.getAmountDescription(numberOfResources);
    const exampleLabelDescription = `<p class="list-title">${this.i18n.tr(this.exampleDescriptionLabel)}</p>`;

    const resourceLinks: string = this.getResourceLinksHtml(exampleResources.length);
    const alertOptions: AlertOptions = this.getAlertOptions(exampleResources);

    const message: string = this.getMessage(mainDescription, amountDescription, exampleLabelDescription, resourceLinks);

    this.alert.showHtml(alertOptions, title, message);
  }

  private getMessage(mainDescription: string, amountDescription: string, exampleLabelDescription: string, resourceLinks: string) {
    return `${mainDescription} ${amountDescription} ${exampleLabelDescription} <ul style="text-align: left">${resourceLinks}</ul>`;
  }

  private getAmountDescription(numberOfResources: number): string {
    return `<p>${this.i18n.tr(this.amountDescriptionLabel)} <strong>${numberOfResources}</strong>.</p>`;
  }

  private getAlertOptions(exampleResources: Resource[]): AlertOptions {
    let alertOptions: AlertOptions = {type: 'warning', aurelialize: true};
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
