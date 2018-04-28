import {HttpClient, HttpResponseMessage, RequestBuilder} from "aurelia-http-client";
import {cachedResponse, untilPromiseCompleted} from "../repository/cached-response";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class DeduplicatingHttpClient {
  constructor(private httpClient: HttpClient) {
  }

  /**
   * This method is NOT deduplicated because it uses a builder which is heavily based on callbacks, which in turn can't be serialized.
   *
   * It's there just as a shorthand to avoid the need for injection of original HttpClient. Avoid using it by adding additional arguments
   * to methods of this class: eg. params aren't supported for PUT by original HttpClient, but this client does allow for them.
   *
   * If adding params doesn't make much sense (eg. when they are non-intuitive or rarely used, like adding extra headers), make sure
   * that methods using createRequest() are cached themselves.
   */
  createRequest(url: string): RequestBuilder {
    return this.httpClient.createRequest(url);
  }

  @cachedResponse(untilPromiseCompleted)
  delete(url: string): Promise<HttpResponseMessage> {
    return this.httpClient.delete(url);
  }

  @cachedResponse(untilPromiseCompleted)
  get(url: string, params?: Object): Promise<HttpResponseMessage> {
    return this.httpClient.get(url, params);
  }

  @cachedResponse(untilPromiseCompleted)
  head(url: string): Promise<HttpResponseMessage> {
    return this.httpClient.head(url);
  }

  @cachedResponse(untilPromiseCompleted)
  options(url: string): Promise<HttpResponseMessage> {
    return this.httpClient.options(url);
  }

  @cachedResponse(untilPromiseCompleted)
  put(url: string, content: any, params?: Object): Promise<HttpResponseMessage> {
    const request = this.httpClient.createRequest(url)
      .asPut()
      .withContent(content);
    if (params !== undefined) {
      request.withParams(params);
    }
    return request.send();
  }

  // don't deduplicate - it's not idempotent!
  patch(url: string, content: any): Promise<HttpResponseMessage> {
    return this.httpClient.patch(url, content);
  }

  // don't deduplicate - it's not idempotent!
  post(url: string, content: any): Promise<HttpResponseMessage> {
    return this.httpClient.post(url, content);
  }
}
