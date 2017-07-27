import {ApiRepository} from "./api-repository";

export abstract class ResourceClassApiRepository<T> extends ApiRepository<T> {

  public getListByClass(resourceClass: string): Promise<T[]> {
    return this.httpClient
      .createRequest(this.endpoint)
      .asGet()
      .withParams({resourceClass})
      .send()
      .then(response => this.responseToEntities(response));
  }
}
