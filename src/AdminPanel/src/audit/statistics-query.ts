import {DeduplicatingHttpClient} from "../common/http-client/deduplicating-http-client";
import {EntitySerializer} from "../common/dto/entity-serializer";
import {cachedResponse, forSeconds} from "../common/repository/cached-response";
import {suppressError as suppressErrorHeader} from "common/http-client/headers";
import {StatisticsBucket} from "audit/statistics/statistics-bucket";

export class StatisticsQuery {
  protected params: any = {};

  constructor(private httpClient: DeduplicatingHttpClient, private endpoint: string, private entitySerializer: EntitySerializer) {
  }

  public filterByDateFrom(dateFrom: string): this {
    this.params.dateFrom = dateFrom;
    return this;
  }

  public filterByDateTo(dateTo: string): this {
    this.params.dateTo = dateTo;
    return this;
  }

  public filterByResourceId(resourceId: number): this {
    this.params.resourceId = resourceId;
    return this;
  }

  public filterByResourceKinds(resourceKinds: string[]): this {
    this.params.resourceKinds = resourceKinds;
    return this;
  }

  public filterByResourceContents(resourceContents: NumberMap<string>): this {
    this.params.resourceContents = JSON.stringify(resourceContents);
    return this;
  }

  public aggregateBy(aggregation: string): this {
    this.params.aggregation = aggregation;
    return this;
  }

  public get(): Promise<StatisticsBucket[]> {
    return this.makeRequest(this.params);
  }

  public getParams() {
    return this.params;
  }

  @cachedResponse(forSeconds(10))
  private makeRequest(params): Promise<StatisticsBucket[]> {
    return this.httpClient.createRequest(`${this.endpoint}/statistics`)
      .asGet()
      .withParams(params)
      .withHeader(suppressErrorHeader.name, suppressErrorHeader.value)
      .send()
      .then(response => {
        const promises = response.content.map(s => this.entitySerializer.deserialize<StatisticsBucket>(StatisticsBucket, s));
        return Promise.all(promises) as any as Promise<StatisticsBucket[]>;
      });
  }
}
