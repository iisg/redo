import {safeJsonParse} from "common/utils/object-utils";
import {StatisticsQuery} from "audit/statistics-query";
import {DateRangeConfig} from "audit/audit-components/filters/date-range-config";

export class StatisticsFilters {
  dateFrom: string;
  dateTo: string;
  resourceKinds: string[] = [];
  resourceContents: NumberMap<string>;
  resourceId: number;
  aggregation: string;
  onChange: VoidFunction = () => undefined;

  toParams(): StringMap<any> {
    const params: StringMap<any> = {};
    if (this.dateFrom) {
      params['dateFrom'] = this.dateFrom;
    }
    if (this.dateTo) {
      params['dateTo'] = this.dateTo;
    }
    if (this.resourceKinds.length) {
      params['resourceKinds'] = this.resourceKinds.join(',');
    }
    if (this.resourceContents) {
      params['resourceContents'] = JSON.stringify(this.resourceContents);
    }
    if (this.resourceId) {
      params['id'] = this.resourceId;
    }
    if (this.aggregation) {
      params['aggregation'] = this.aggregation;
    }
    params['tab'] = 'statistics';
    return params;
  }

  buildQuery(query: StatisticsQuery): StatisticsQuery {
    if (this.resourceId) {
      query = query.filterByResourceId(this.resourceId);
    }
    if (this.dateFrom) {
      query = query.filterByDateFrom(this.dateFrom);
    }
    if (this.dateTo) {
      query = query.filterByDateTo(this.dateTo);
    }
    if (this.aggregation) {
      query = query.aggregateBy(this.aggregation);
    }
    return query
      .filterByResourceContents(this.resourceContents)
      .filterByResourceKinds(this.resourceKinds);
  }

  static fromParams(params: StringMap<any>): StatisticsFilters {
    const filters = new StatisticsFilters();
    filters.dateFrom = DateRangeConfig.isDateValid(params['dateFrom']) ? params['dateFrom'] : "";
    filters.dateTo = DateRangeConfig.isDateValid(params['dateTo']) ? params['dateTo'] : "";
    filters.resourceKinds = (params['resourceKinds'] || '').split(',').filter(resourceKind => !!resourceKind.trim());
    filters.resourceContents = safeJsonParse(params['resourceContents']);
    filters.resourceId = +params['id'];
    filters.aggregation = params['aggregation'];
    return filters;
  }
}
