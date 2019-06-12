import {bindable} from "aurelia-templating";
import * as moment from "moment";
import {DateMode, inputDateConfig} from "../../resources/controls/input/flexible-date-input/flexible-date-config";
import {StatisticsBucket} from "audit/statistics/statistics-bucket";
import {computedFrom} from "aurelia-binding";
import {unique} from "common/utils/array-utils";
import {BindingSignaler} from "aurelia-templating-resources";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class StatisticsTable {
  @bindable allBuckets: StatisticsBucket[];
  @bindable eventGroup: string;
  @bindable aggregation: string;

  constructor(private bindingSignaler: BindingSignaler) {
  }

  dateToMonth(date: string): string {
    return moment(date).format(inputDateConfig[DateMode.MONTH].format);
  }

  allBucketsChanged() {
    setTimeout(() => this.bindingSignaler.signal('buckets-updated'));
  }

  @computedFrom('allBuckets', 'eventGroup')
  get buckets(): StatisticsBucket[] {
    return this.allBuckets.filter(bucket => bucket.eventGroup == this.eventGroup);
  }

  @computedFrom('buckets')
  get aggregations(): Date[] {
    return unique(this.buckets.map(s => s.bucket), (a, b) => a.getTime() == b.getTime());
  }

  @computedFrom('buckets')
  get eventNames(): string[] {
    return unique(this.buckets.map(s => s.eventName));
  }

  count(aggregation: Date, eventName: string): number {
    return this.buckets
      .filter(b => !eventName || b.eventName == eventName)
      .filter(b => !aggregation || b.bucket.getTime() == aggregation.getTime())
      .map(b => b.count)
      .reduce((v, a) => v + a, 0);
  }

  @computedFrom('buckets')
  get totalCount(): number {
    return this.count(undefined, undefined);
  }

  bucketLabel(aggregation: Date): string {
    return this.buckets.find(b => b.bucket.getTime() == aggregation.getTime()).bucketLabel;
  }
}
