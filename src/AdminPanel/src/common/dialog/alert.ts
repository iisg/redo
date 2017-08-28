import swal, {SweetAlertOptions} from "sweetalert2";
import {I18N} from "aurelia-i18n";
import {stringToHtml} from "../utils/string-utils";
import {TemplatingEngine} from "aurelia-templating";
import {inject, Lazy} from "aurelia-dependency-injection";

export type AlertType = 'error' | 'info' | 'question' | 'success' | 'warning';

export type ButtonClass = 'danger' | 'default' | 'info' |  'primary' | 'success' | 'warning';

const alertTypeToButtonClass: StringMap<ButtonClass> = { // AlertType => ButtonClass
  'error': 'danger',
  'info': 'info',
  'question': 'primary',
  'success': 'success',
  'warning': 'warning',
};

@inject(I18N, Lazy.of(TemplatingEngine)) // FIXME waiting for @autoinject fix: https://github.com/aurelia/dependency-injection/issues/153
// @autoinject
export class Alert {
  private readonly AURELIALIZABLE_CLASS = 'alert-aurelializable';

  constructor(private i18n: I18N, private templatingEngine: () => TemplatingEngine) {
    // TemplatingEngine cannot be injected before aurelia.start() is called in main.ts.
    // Alert is used in interceptors which are set up sooner, though, so we will lazy-load it later.
    const sweetAlertDefaults: SweetAlertOptions = {
      type: 'info',
      showConfirmButton: true,
      showCancelButton: false,
      buttonsStyling: false,
      reverseButtons: true,
      allowOutsideClick: false,
      // can't set button labels yet because I18N may not have been configured
    };
    swal.setDefaults(sweetAlertDefaults);
  }

  showHtml(options: AlertOptions, title: string, html: string = undefined): Promise<string> {
    const defaultButtonLabels: SweetAlertOptions = {
      confirmButtonText: this.i18n.tr('OK'),
      cancelButtonText: this.i18n.tr('Cancel'),
    };

    if (options.confirmButtonClass === undefined) {
      options.confirmButtonClass = alertTypeToButtonClass[options.type];
    }
    if (options.cancelButtonClass === undefined) {
      options.cancelButtonClass = 'default';
    }

    const commonCssClasses = 'btn btn-raised';
    let overrides: SweetAlertOptions = {
      titleText: title,
      html: html,
      confirmButtonClass: `${commonCssClasses} btn-${options.confirmButtonClass}`,
      cancelButtonClass: `${commonCssClasses} btn-${options.cancelButtonClass}`,
    } as SweetAlertOptions;
    if (options.showCancelButton === undefined) {
      overrides.showCancelButton = (options.type == 'question');
    }
    if (options.aurelialize) {
      overrides.onOpen = (modal: HTMLElement) => {
        this.aurelializeModal(modal, options.aureliaContext);
        if (options.onOpen != undefined) {
          options.onOpen(modal);
        }
      };
      const $e = $('<div>');
      $e.addClass(this.AURELIALIZABLE_CLASS);
      $e.html(html);
      overrides.html = $e;
    }

    const mergedOptions: SweetAlertOptions = $.extend(defaultButtonLabels, options, overrides);
    // SweetAlert complains about extra properties
    delete (mergedOptions as AlertOptions).aurelialize;
    delete (mergedOptions as AlertOptions).aureliaContext;
    return swal(mergedOptions);
  }

  show(options: AlertOptions, title: string, text: string = undefined): Promise<string> {
    const html = stringToHtml(text);
    return this.showHtml(options, title, html);
  }

  private aurelializeModal(modal: HTMLElement, bindingContext: any = {}): void {
    const element = $(modal).find('.' + this.AURELIALIZABLE_CLASS)[0];
    if (element == undefined) {
      return;
    }
    this.templatingEngine().enhance({element, bindingContext});
  }
}

export interface AlertOptions {
  type?: AlertType;
  showConfirmButton?: boolean;
  confirmButtonClass?: ButtonClass;
  confirmButtonText?: string;
  showCancelButton?: boolean;
  cancelButtonClass?: ButtonClass;
  cancelButtonText?: string;
  imageUrl?: string;
  aurelialize?: boolean;
  aureliaContext?: any;
  onOpen?: (modal: HTMLElement) => any;
}
