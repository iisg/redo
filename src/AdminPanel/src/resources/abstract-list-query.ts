import {EntitySerializer} from "common/dto/entity-serializer";
import {DeduplicatingHttpClient} from "common/http-client/deduplicating-http-client";
import {PageResult} from "./page-result";
import {cachedResponse, forSeconds} from "../common/repository/cached-response";

export abstract class AbstractListQuery<T> {
  protected params: any = {};

  constructor(protected httpClient: DeduplicatingHttpClient,
              protected endpoint: string,
              protected entitySerializer: EntitySerializer,
              private entityArrayType: string) {
  }

  public setPage(page: number): this {
    this.params.page = page;
    return this;
  }

  public setResultsPerPage(resultsPerPage: number): this {
    this.params.resultsPerPage = resultsPerPage;
    return this;
  }

  public get(): Promise<PageResult<T>> {
    return this.makeRequest(this.params);
  }

  @cachedResponse(forSeconds(10))
  private makeRequest(params): Promise<PageResult<T>> {
    return this.httpClient.get(this.endpoint, params)
      .then(response => {
        const total = +response.headers.get('pk_total');
        const page = +response.headers.get('pk_page');
        return this.entitySerializer.deserialize<PageResult<T>>(this.entityArrayType, response.content).then(resources => {
          resources.total = total;
          resources.page = page;
          return resources;
        });
      });
  }
}
