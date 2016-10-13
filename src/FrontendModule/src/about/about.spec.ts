import {About} from "./about";
import {Configure} from "aurelia-configuration";

describe('About', () => {
  let about: About;
  let config: Configure;

  beforeEach(() => {
    config = new Configure();
    spyOn(config, 'get').and.returnValue('1.0');
    about = new About(config);
  });

  it('assigns the application version to the view', () => {
    expect(about.version).toEqual('1.0');
  });
});
