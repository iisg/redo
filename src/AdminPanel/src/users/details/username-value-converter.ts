import {User} from "users/user";
import {SystemMetadata} from "resources-config/metadata/system-metadata";

export class UsernameValueConverter implements ToViewValueConverter {
  toView(user: User): string {
    return (user != undefined)
      ? user.userData.contents[SystemMetadata.USERNAME.id][0]['value']
      : '?';
  }
}
