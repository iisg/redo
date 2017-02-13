import {Aurelia} from "aurelia-framework";
import {HttpClient, HttpResponseMessage, Interceptor, RequestMessage} from "aurelia-http-client";
import {MetricsSenderInterceptor} from "../common/metrics/metrics-sender-interceptor";
import * as swal from 'sweetalert2';
import {I18N} from "aurelia-i18n";
import {autoinject} from "aurelia-dependency-injection";

export function configure(aurelia: Aurelia) {
  let client: HttpClient = aurelia.container.get(HttpClient);
  let globalExceptionInterceptor = aurelia.container.get(GlobalExceptionInterceptor);
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

@autoinject
class GlobalExceptionInterceptor implements Interceptor {

  constructor(private i18n: I18N) {
  }

  responseError(response: HttpResponseMessage): HttpResponseMessage {
    let prodAlert = {
      title: this.i18n.tr("Error") + ' ' + response.content.status,
      text: this.i18n.tr("exceptions::" + response.content.message),
      type: "error",
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "OK",
    };

    let devAlert = {
      showCancelButton: true,
      confirmButtonText: this.i18n.tr("Show Profiler"),
      cancelButtonText: this.i18n.tr("Close"),
    };

    let isDebug = response.headers.has('X-Debug-Token-Link');

    if (response.statusCode >= 400 && response.statusCode != 401) {
      swal(isDebug ? $.extend({}, prodAlert, devAlert) : prodAlert).then(() => {
        if (isDebug) {
          window.open(response.headers.get('X-Debug-Token-Link'));
        }
      });
    }

    throw response;
  }
}
