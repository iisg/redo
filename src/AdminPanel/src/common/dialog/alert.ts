import swal, {SweetAlertOptions} from "sweetalert2";
import {I18N} from "aurelia-i18n";
import {autoinject} from "aurelia-dependency-injection";

export type AlertType = 'error' | 'info' | 'question' | 'success' | 'warning';

export type ButtonClass = 'danger' | 'default' | 'info' |  'primary' | 'success' | 'warning';

const alertTypeToButtonClass: StringMap<ButtonClass> = { // AlertType => ButtonClass
  'error': 'danger',
  'info': 'info',
  'question': 'primary',
  'success': 'success',
  'warning': 'warning',
};

@autoinject
export class Alert {
  constructor(private i18n: I18N) {
    const sweetAlertDefaults: SweetAlertOptions = {
      type: 'info',
      showConfirmButton: true,
      showCancelButton: false,
      buttonsStyling: false,
      reverseButtons: true,
      // can't set button labels yet because I18N may not have been configured
    };
    swal.setDefaults(sweetAlertDefaults);
  }

  show(options: AlertOptions, title: string, text: string = undefined): Promise<string> {
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
      text: text,
      confirmButtonClass: `${commonCssClasses} btn-${options.confirmButtonClass}`,
      cancelButtonClass: `${commonCssClasses} btn-${options.cancelButtonClass}`,
    } as SweetAlertOptions;
    if (options.showCancelButton === undefined) {
      overrides.showCancelButton = (options.type == 'question');
    }
    const mergedOptions: SweetAlertOptions = $.extend(defaultButtonLabels, options, overrides);
    return swal(mergedOptions);
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
}
