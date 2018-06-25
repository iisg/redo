import {Resource} from "../resource";
import {User} from "../../users/user";
import {inject} from "aurelia-dependency-injection";
import {CurrentUserFetcher} from "../../users/current/current-user-fetcher";
import {SystemMetadata} from "../../resources-config/metadata/system-metadata";
import {intersection} from "lodash";

@inject(CurrentUserFetcher.CURRENT_USER_KEY)
export class CurrentUserIsReproductorValueConverter implements ToViewValueConverter {
  constructor(private currentUser: User) {
  }

  toView(resource: Resource): boolean {
    const reproductorIds = resource.contents[SystemMetadata.REPRODUCTOR.id].map(v => v.value);
    const userIds = this.currentUser.userData.contents[SystemMetadata.GROUP_MEMBER.id].map(v => v.value);
    userIds.push(this.currentUser.userData.id);
    return intersection(reproductorIds, userIds).length > 0;
  }
}
