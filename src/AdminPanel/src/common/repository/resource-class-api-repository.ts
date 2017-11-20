import {ApiRepository} from "./api-repository";

export abstract class ResourceClassApiRepository<T> extends ApiRepository<T> {
  public getListByClass(resourceClass: string): Promise<T[]> {
    return this.httpClient.get(this.endpoint, {resourceClass})
      .then(response => this.responseToEntities(response));
  }
}
