import {HttpResponseMessage, Interceptor} from "aurelia-http-client";

export class RedirectToLoginIfUnauthenticatedInterceptor implements Interceptor {
  responseError(response: HttpResponseMessage): HttpResponseMessage {
    if (response.statusCode == 401 && !window.location.pathname.startsWith('/login')) {
      window.location.href = '/login';
    }
    throw response;
  }
}
