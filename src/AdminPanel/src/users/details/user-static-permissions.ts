import {bindable} from "aurelia-templating";
import {User} from "../user";
import {autoinject} from "aurelia-dependency-injection";
import {UserRepository} from "../user-repository";
import {Configure} from "aurelia-configuration";
import {computedFrom} from "aurelia-binding";
import {deepCopy} from "../../common/utils/object-utils";

@autoinject
export class UserStaticPermissions {
  @bindable user: User;

  saving = false;

  private staticPermissions: Array<string>;

  private selectedPermissions: Array<string> = [];

  constructor(private userRepository: UserRepository, config: Configure) {
    this.staticPermissions = config.get("static_permissions");
  }

  @computedFrom('selectedPermissions', 'selectedPermissions.length')
  get permissionsChanged() {
    return this.selectedPermissions.length != this.user.staticPermissions.length
      || this.selectedPermissions.filter(p => this.user.staticPermissions.indexOf(p) < 0).length > 0;
  }

  userChanged(user: User) {
    this.selectedPermissions = deepCopy(user.staticPermissions);
  }

  save() {
    this.saving = true;
    this.userRepository.updateStaticPermissions(this.user, this.selectedPermissions).then(user => {
      this.user = user;
    }).finally(() => this.saving = false);
  }
}
