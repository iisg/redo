import {bindable} from "aurelia-templating";
import {ResourceRepository} from "../resource-repository";
import {Resource} from "../resource";
import {autoinject} from "aurelia-dependency-injection";
import {User} from "users/user";
import {SystemResourceKinds} from "resources-config/resource-kind/system-resource-kinds";
import {UserRepository} from "users/user-repository";
import {cachedResponse, forSeconds} from "../../common/repository/cached-response";

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
    if (this.id) {
      this.loading = true;
      this.fetchResource(this.id)
        .then(resource => this.onResourceFetched(resource))
        .finally(() => this.loading = false);
    }
  }

  @cachedResponse(forSeconds(30))
  private fetchResource(id: number): Promise<Resource> {
    return this.resourceRepository.get(id, true);
  }

  private onResourceFetched(resource: Resource): Promise<void> | void {
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
