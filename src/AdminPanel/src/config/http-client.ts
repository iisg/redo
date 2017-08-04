import {Aurelia} from "aurelia-framework";
import {HttpClient} from "aurelia-http-client";
import {MetricsSenderInterceptor} from "common/metrics/metrics-sender-interceptor";
import {GlobalExceptionInterceptor} from "common/http-client/global-exception-interceptor";
import {CsrfHeaderInterceptor} from "common/http-client/csrf-header-interceptor";
import {RedirectToLoginIfUnauthenticatedInterceptor} from "common/http-client/redirect-to-login-if-unauthenticated-interceptor";

export function configure(aurelia: Aurelia) {
  const client: HttpClient = aurelia.container.get(HttpClient);
  const globalExceptionInterceptor = aurelia.container.get(GlobalExceptionInterceptor);
  client.configure((config) => {
    config
      .withBaseUrl("/api/")
      .withHeader('Accept', 'application/json')
      .withHeader('X-Requested-With', 'XMLHttpRequest')
      .withInterceptor(new RedirectToLoginIfUnauthenticatedInterceptor())
      .withInterceptor(new MetricsSenderInterceptor())
      .withInterceptor(new CsrfHeaderInterceptor())
      .withInterceptor(globalExceptionInterceptor);
  });
}
