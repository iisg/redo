import {Aurelia} from "aurelia-framework";
import {HttpClient, Interceptor, HttpResponseMessage} from "aurelia-http-client";
import {MetricsSenderInterceptor} from "../common/metrics/metrics-sender-interceptor";

export function configure(aurelia: Aurelia) {
  let client: HttpClient = aurelia.container.get(HttpClient);
  client.configure((config) => {
    config
      .withBaseUrl("/api/")
      .withHeader('Accept', 'application/json')
      .withHeader('X-Requested-With', 'XMLHttpRequest')
      .withInterceptor(new RedirectToLoginIfUnauthenticatedInterceptor())
      .withInterceptor(new MetricsSenderInterceptor());
  });
}

class RedirectToLoginIfUnauthenticatedInterceptor implements Interceptor {
  responseError(error: HttpResponseMessage): HttpResponseMessage {
    if (error.statusCode == 401) {
      window.location.href = '/login';
    }
    throw error;
  }
}
