import {computedFrom, observable} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {bindable} from "aurelia-templating";
import {twoWay} from "common/components/binding-mode";
import {InCurrentLanguageValueConverter} from 'resources-config/multilingual-field/in-current-language';
import {AuditListFilters} from "../audit-list-filters";
import {AuditSettings} from "./audit-settings";
import {parseQueryString} from "aurelia-path";

@autoinject
export class AuditSettingsChooser {
  @bindable(twoWay) filters: AuditListFilters;
  @observable selectedUrl: string;
  @bindable(twoWay) auditSettings: AuditSettings[];
  private urlNamesByIds: StringMap<string>;
  private urlParamsByIds: StringMap<string>;

  constructor(private inCurrentLanguage: InCurrentLanguageValueConverter, private i18n: I18N) {
  }

  attached() {
    let urlNamesByIds = {};
    let urlParamsByIds = {};

    this.auditSettings.forEach((value) => {
      urlNamesByIds[value.id] = this.inCurrentLanguage.toView(value.label);
      urlParamsByIds[value.id] = value.url;
    });
    this.urlNamesByIds = urlNamesByIds;
    this.urlParamsByIds = urlParamsByIds;
  }

  urlName(urlId: string) {
    return this.urlNamesByIds[urlId] || this.i18n.tr('Saved settings') + " " + urlId;
  }

  @computedFrom('urlNamesByIds')
  get urlNames(): string[] {
    return this.urlNamesByIds && Object.keys(this.urlNamesByIds);
  }

  selectedUrlChanged() {
    if (this.selectedUrl) {
      let resourceId = this.filters.resourceId;
      let resultsPerPage = this.filters.resultsPerPage;
      let params: any = parseQueryString(this.urlParamsByIds[this.selectedUrl]);
      if (resourceId != undefined) {
        params.id = resourceId;
      }
      this.filters.resultsPerPage = resultsPerPage || AuditListFilters.DEFAULT_RESULTS_PER_PAGE;
      this.filters.currentPageNumber = 1;
      this.filters = AuditListFilters.fromParams(params);
    }
  }

}
