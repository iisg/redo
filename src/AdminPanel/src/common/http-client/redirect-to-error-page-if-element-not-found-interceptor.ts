import {Container} from 'aurelia-dependency-injection';
import {HttpResponseMessage, Interceptor} from "aurelia-http-client";
import {Router} from "aurelia-router";
import * as headers from "./headers";

export class RedirectToErrorPageIfElementNotFoundInterceptor implements Interceptor {
  responseError(response: HttpResponseMessage): HttpResponseMessage {
    const suppressError = response.requestMessage.headers.get(headers.suppressError.name) == headers.suppressError.value;
    if (!suppressError && response.statusCode == 404) {
      const router: Router = Container.instance.get(Router);
      const initialFragment = (router.history as any).fragment;
      router.navigate('not-found', {replace: true});
      router.navigate(initialFragment, {trigger: false, replace: true});
    }
    throw response;
  }
}
