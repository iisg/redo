import {CurrentUserFetcher} from "../../users/current/current-user-fetcher";
import {autoinject, Container} from "aurelia-dependency-injection";
import {Interceptor, HttpResponseMessage} from "aurelia-http-client";
import {I18N} from "aurelia-i18n";
import {Alert, AlertOptions} from "../dialog/alert";

@autoinject
export class GlobalExceptionInterceptor implements Interceptor {
  constructor(private i18n: I18N, private container: Container, private alert: Alert) {
  }

  responseError(response: HttpResponseMessage): HttpResponseMessage {
    const prodAlert: AlertOptions = {
      type: 'error',
    };
    const devAlert: AlertOptions = {
      type: 'error',
      showCancelButton: true,
      confirmButtonText: this.i18n.tr("Show Profiler"),
      cancelButtonText: this.i18n.tr("Close"),
    };

    const title = this.i18n.tr("Error {{code}}", {code: response.statusCode});
    const text = this.getErrorMessage(response);
    const isDebug = response.headers.has('X-Debug-Token-Link');

    if (response.statusCode >= 400 && response.statusCode != 401) {
      if (this.container.get(CurrentUserFetcher.CURRENT_USER_KEY).id) {
        const alertOptions = isDebug ? devAlert : prodAlert;
        this.alert.show(alertOptions, title, text).then(() => {
          if (isDebug) {
            window.open(response.headers.get('X-Debug-Token-Link'));
          }
        });
      }
    }

    throw response;
  }

  getErrorMessage(response: HttpResponseMessage): string {
    const errorCause: string = response.content.errorMessageId || 'generic';
    let params: any = response.content.params;
    return this.i18n.tr(`exceptions::${errorCause}`, {replace: params});
  }
}
