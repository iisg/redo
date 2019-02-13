import {computedFrom} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {bindable} from "aurelia-templating";
import {twoWay} from "../../common/components/binding-mode";
import {ResourceLabelValueConverter} from "../../resources/details/resource-label-value-converter";
import {UserRepository} from "../../users/user-repository";

@autoinject
export class AuditUserChooser {
  @bindable(twoWay) selectedUsersIds: string[];
  private usersLabelsByIds: StringMap<string>;

  constructor(private userRepository: UserRepository,
              private resourceLabelValueConverter: ResourceLabelValueConverter,
              private i18n: I18N) {
  }

  async attached() {
    await this.userRepository.getList().then(values => {
      let usersLabelsByIds = {};
      values.forEach(value => usersLabelsByIds[value.id] = this.resourceLabelValueConverter.toView(value.userData));
      this.usersLabelsByIds = usersLabelsByIds;
    });
  }

  userLabel(userId: string) {
    return this.usersLabelsByIds[userId] || this.i18n.tr('User') + " " + userId;
  }

  @computedFrom('usersLabelsByIds')
  get usersIds(): string[] {
    return this.usersLabelsByIds && Object.keys(this.usersLabelsByIds);
  }

}
