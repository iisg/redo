import {Audit} from "./audit-components/audit";
import {autoinject} from "aurelia-dependency-injection";
import {DetailsViewTabs} from "../resources-config/metadata/details/details-view-tabs";
import {I18N} from "aurelia-i18n";
import {Router} from "aurelia-router";
import {EventAggregator} from "aurelia-event-aggregator";

@autoinject
export class AuditPage {
  auditTabs: DetailsViewTabs;

  constructor(private i18n: I18N, private router: Router, private eventAggregator: EventAggregator) {
    this.auditTabs = new DetailsViewTabs(this.eventAggregator, () => this.updateUrl());
  }

  activate(parameters: any) {
    this.buildTabs(parameters.tab);
  }

  private buildTabs(activeTabId: string) {
    this.auditTabs
      .clear()
      .addTab('audit', this.i18n.tr('Audit'))
      .addTab('statistics', this.i18n.tr('Statistics'));
    this.auditTabs.activateTab(activeTabId);
  }

  private updateUrl() {
    const parameters = {};
    parameters['tab'] = this.auditTabs.activeTabId;
    this.router.navigateToRoute('audit', parameters, {trigger: false, replace: true});
  }
}
