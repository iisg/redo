import {Interceptor, HttpResponseMessage} from "aurelia-http-client";

export class RedirectToLoginIfUnauthenticatedInterceptor implements Interceptor {
  responseError(error: HttpResponseMessage): HttpResponseMessage {
    if (error.statusCode == 401) {
      window.location.href = '/login';
    }
    throw error;
  }
}
