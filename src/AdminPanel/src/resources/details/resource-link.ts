import {bindable} from "aurelia-templating";
import {ResourceRepository} from "../resource-repository";
import {Resource} from "../resource";
import {autoinject} from "aurelia-dependency-injection";
import {User} from "users/user";
import {SystemResourceKinds} from "resources-config/resource-kind/system-resource-kinds";
import {UserRepository} from "users/user-repository";

@autoinject
export class ResourceLink {
  @bindable id: number;

  routerParams = {id: undefined};
  resource: Resource;
  relatedUser: User;
  loading: boolean = false;
  isUserLink: boolean = false;

  constructor(private resourceRepository: ResourceRepository, private userRepository: UserRepository) {
  }

  idChanged(): void {
    this.loading = true;
    this.resourceRepository.get(this.id, true)
      .catch(() => Promise.resolve(undefined))
      .then(resource => this.onResourceFetched(resource))
      .finally(() => this.loading = false);
  }

  private onResourceFetched(resource: Resource): Promise<void>|void {
    this.resource = resource;
    if (resource.kind.id == SystemResourceKinds.USER_ID) {
      this.isUserLink = true;
      return this.fetchRelatedUser(resource).then(user => {
        this.relatedUser = user;
        this.routerParams.id = user.id;
      });
    } else {
      this.isUserLink = false;
      this.routerParams.id = this.id;
    }
  }

  private fetchRelatedUser(resource: Resource): Promise<User> {
    return this.userRepository.getRelatedUser(resource);
  }
}
