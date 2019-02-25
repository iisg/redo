import {DeduplicatingHttpClient} from "../common/http-client/deduplicating-http-client";
import {EntitySerializer} from "../common/dto/entity-serializer";
import {cachedResponse, forSeconds} from "../common/repository/cached-response";
import {StatisticsCollection} from "./statistics/statistics-collection";

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

  public get(): Promise<StatisticsCollection> {
    return this.makeRequest(this.params);
  }

  @cachedResponse(forSeconds(10))
  private makeRequest(params): Promise<StatisticsCollection> {
    return this.httpClient.createRequest(`${this.endpoint}/statistics`)
      .asGet()
      .withParams(params)
      .send()
      .then(response => {
        return this.entitySerializer.deserialize<StatisticsCollection>(StatisticsCollection, response.content);
      });
  }
}
