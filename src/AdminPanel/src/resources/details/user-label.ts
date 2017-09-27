import {User} from "users/user";
import {I18N} from "aurelia-i18n";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class UserLabelValueConverter implements ToViewValueConverter {
  constructor(private i18n: I18N) {
  }

  toView(user: User): string {
    const translatedPrefix = this.i18n.tr('User');
    return (user != undefined)
      ? `${translatedPrefix} '${user.username}'`
      : '';
  }
}
