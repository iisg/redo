import {CurrentUserFetcher} from "users/current/current-user-fetcher";
import {autoinject, Container} from "aurelia-dependency-injection";
import {Interceptor, HttpResponseMessage} from "aurelia-http-client";
import {I18N} from "aurelia-i18n";
import {Alert, AlertOptions} from "../dialog/alert";
import * as headers from "./headers";

@autoinject
export class GlobalExceptionInterceptor implements Interceptor {
  constructor(private i18n: I18N, private container: Container, private alert: Alert) {
  }

  responseError(response: HttpResponseMessage): HttpResponseMessage {
    const options = this.getAlertOptions(response);
    const title = this.getErrorTitle(response);
    const html = this.getErrorMessage(response);
    const isDebug = response.headers.has(headers.debugTokenLink.name);
    const suppressError = response.requestMessage.headers.get(headers.suppressError.name) == headers.suppressError.value;

    if (!suppressError
      && response.statusCode >= 400
      && response.statusCode != 401
      && this.userAuthenticated) {
      this.alert.showHtml(options, title, html).then(() => {
        if (isDebug) {
          window.open(response.headers.get(headers.debugTokenLink.name));
        }
      });
    }

    throw response;
  }

  private getAlertOptions(response: HttpResponseMessage): AlertOptions {
    let prodAlert: AlertOptions = {
      type: 'error',
    };
    const responseContent: ExceptionContent = response.content;
    if (responseContent.errorMessageId == 'invalidCommand') {
      const aureliaAdditions: AlertOptions = {
        aurelialize: true,
        aureliaContext: {violations: this.groupViolationsByField(responseContent)},
      };
      prodAlert = $.extend(prodAlert, aureliaAdditions);
    }
    const isDebug = response.headers.has(headers.debugTokenLink.name);
    if (!isDebug) {
      return prodAlert;
    } else {
      const debugAdditions: AlertOptions = {
        showCancelButton: true,
        confirmButtonText: this.i18n.tr("Show Profiler"),
        cancelButtonText: this.i18n.tr("Close"),
      };
      return $.extend(prodAlert, debugAdditions);
    }
  }

  private getErrorTitle(response: HttpResponseMessage): string {
    const responseContent: ExceptionContent = response.content;
    return (responseContent.errorMessageId == 'invalidCommand')
      ? this.i18n.tr('exceptions::invalidCommand')
      : this.i18n.tr("Error {{code}}", {code: response.statusCode});
  }

  private getErrorMessage(response: HttpResponseMessage): string {
    const responseContent: ExceptionContent = response.content;
    const errorMessageId: string = responseContent.errorMessageId || 'generic';
    let params: any = responseContent.params || {};
    if (params.hasOwnProperty('entityName')) {
      params['entityName'] = this.i18n.tr('entityTypes::' + params['entityName'].toLowerCase(), {context: 'genitive'});
    }
    return (responseContent.errorMessageId == 'invalidCommand')
      ? '<invalid-command-message violations-by-field.bind="violations"></invalid-command-message>'
      : this.i18n.tr(`exceptions::${errorMessageId}`, {replace: params});
  }

  private groupViolationsByField(responseContent: ExceptionContent): StringMap<string[]> {
    let violationsByField: StringMap<string[]> = {};
    for (const violation of responseContent.params.violations) {
      if (!violationsByField.hasOwnProperty(violation.field)) {
        violationsByField[violation.field] = [];
      }
      violationsByField[violation.field].push(violation.message);
    }
    return violationsByField;
  }

  private get userAuthenticated() {
    return this.container.get(CurrentUserFetcher.CURRENT_USER_KEY).id;
  }
}

interface ExceptionContent {
  errorMessageId: string;
  params: CommandParams;
}

interface CommandParams extends InvalidCommandParams {
  command: string;
}

interface InvalidCommandParams {
  violations: Violation[];
}

interface Violation {
  message: string; // eg. "resource has no children."
  field: string;   // eg. "resource"
  rule: string;    // eg. "resourceHasNoChildrenRule"
}
