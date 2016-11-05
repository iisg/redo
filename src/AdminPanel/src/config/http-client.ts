import {Aurelia} from "aurelia-framework";
import {HttpClient, HttpResponseMessage, Interceptor, RequestMessage} from "aurelia-http-client";
import {MetricsSenderInterceptor} from "../common/metrics/metrics-sender-interceptor";

export function configure(aurelia: Aurelia) {
  let client: HttpClient = aurelia.container.get(HttpClient);
  client.configure((config) => {
    config
      .withBaseUrl("/api/")
      .withHeader('Accept', 'application/json')
      .withHeader('X-Requested-With', 'XMLHttpRequest')
      .withInterceptor(new RedirectToLoginIfUnauthenticatedInterceptor())
      .withInterceptor(new MetricsSenderInterceptor())
      .withInterceptor(new CsrfHeaderInterceptor());
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

class CsrfHeaderInterceptor implements Interceptor {
  private static readonly TOKEN_HEADER = 'X-CSRF-Token';

  private latestCsrfToken: string;

  response(response: HttpResponseMessage): HttpResponseMessage {
    if (response.headers.has(CsrfHeaderInterceptor.TOKEN_HEADER)) {
      this.latestCsrfToken = response.headers.get(CsrfHeaderInterceptor.TOKEN_HEADER);
    }
    return response;
  }

  request(request: RequestMessage): RequestMessage {
    if (this.latestCsrfToken) {
      if (['POST', 'PUT', 'PATCH'].indexOf(request.method) >= 0) {
        request.headers.add(CsrfHeaderInterceptor.TOKEN_HEADER, this.latestCsrfToken);
      }
    }
    return request;
  }
}
