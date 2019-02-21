import {autoinject, lazy} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {TemplatingEngine} from "aurelia-templating";
import swal, {SweetAlertOptions} from "sweetalert2";
import {stringToHtml} from "../utils/string-utils";

export type AlertType = 'error' | 'info' | 'question' | 'success' | 'warning';

export type ButtonClass = '' | 'blue' | 'orange' | 'red';

const buttonClassByAlertType: StringMap<ButtonClass> = { // AlertType => ButtonClass
  'error': 'red',
  'info': '',
  'question': '',
  'success': '',
  'warning': 'orange',
};

@autoinject
export class Alert {
  private readonly AURELIALIZABLE_CLASS = 'alert-aurelializable';

  constructor(private i18n: I18N, @lazy(TemplatingEngine) private templatingEngine: () => TemplatingEngine) {
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

  showHtml(options: AlertOptions, title: string, html?: string): Promise<string> {
    const defaultButtonLabels: SweetAlertOptions = {
      confirmButtonText: this.i18n.tr('OK'),
      cancelButtonText: this.i18n.tr('Cancel'),
    };

    if (options.confirmButtonClass === undefined) {
      options.confirmButtonClass = buttonClassByAlertType[options.type];
    }
    if (options.cancelButtonClass === undefined) {
      options.cancelButtonClass = '';
    }

    const commonButtonClass = 'toggle-button';
    let overrides: SweetAlertOptions = {
      titleText: title,
      html: html,
      confirmButtonClass: `${commonButtonClass} ${options.confirmButtonClass}`,
      cancelButtonClass: `${commonButtonClass} ${options.cancelButtonClass}`,
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

  show(options: AlertOptions, title: string, text?: string): Promise<string> {
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
