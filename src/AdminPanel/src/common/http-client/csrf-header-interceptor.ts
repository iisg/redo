import {Interceptor, HttpResponseMessage, RequestMessage} from "aurelia-http-client";

export class CsrfHeaderInterceptor implements Interceptor {
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
