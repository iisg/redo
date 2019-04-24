import {autoinject} from "aurelia-dependency-injection";
import {AuditEntryRepository} from "../audit-entry-repository";
import {bindable, ComponentAttached} from "aurelia-templating";
import {Router} from "aurelia-router";
import * as moment from "moment";
import {StatisticsCollection} from "./statistics-collection";
import {getQueryParameters} from "common/utils/url-utils";
import {changeHandler} from "common/components/binding-mode";
import {debounce} from "lodash";
import {computedFrom} from "aurelia-binding";

@autoinject
export class StatisticsView implements ComponentAttached {
  statisticsCollection: StatisticsCollection;
  @bindable(changeHandler('fetchStatistics')) dateFrom: string;
  @bindable(changeHandler('fetchStatistics')) dateTo: string;
  displayProgressBar = false;

  constructor(private auditEntryRepository: AuditEntryRepository, private router: Router) {
  }

  attached() {
    this.fetchStatistics();
  }

  activate() {
    this.onDateChange();
  }

  private formatDate(date): string {
    const localDate = (moment(date).add(-(moment().utcOffset()), 'm'));
    return moment.parseZone(localDate).utc().format('YYYY-MM-DDTHH:mm:ss');
  }

  private fetchStatistics = debounce(() => {
    if (this.dateFrom && this.dateTo) {
      const dateFrom = this.formatDate(moment(this.dateFrom).startOf('month').toDate());
      const dateTo = this.formatDate(moment(this.dateTo).endOf('month').toDate());
      this.displayProgressBar = true;
      this.auditEntryRepository.getStatisticsQuery()
        .filterByDateFrom(dateFrom)
        .filterByDateTo(dateTo)
        .get()
        .then(response => this.statisticsCollection = response)
        .finally(() => this.displayProgressBar = false);
    }
  }, 50);

  onDateChange() {
    const queryParameters = getQueryParameters(this.router);
    const parameters = {};
    parameters['tab'] = queryParameters['tab'];
    if (this.dateFrom) {
      parameters['dateFrom'] = queryParameters['dateFrom'];
    }
    if (this.dateTo) {
      parameters['dateTo'] = queryParameters['dateTo'];
    }
    this.router.navigateToRoute('audit', parameters, {trigger: false, replace: true});
    this.fetchStatistics();
  }

  @computedFrom('statisticsCollection')
  get isAnyStatistic(): boolean {
    return this.statisticsCollection
      && !!this.statisticsCollection.statistics.filter(v => v.statisticsEntries.length).length;
  }
}
