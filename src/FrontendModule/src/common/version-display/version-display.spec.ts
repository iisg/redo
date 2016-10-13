import {Configure} from "aurelia-configuration";
import {VersionDisplay} from "./version-display";

describe('VersionDisplay', () => {
  let versionDisplay: VersionDisplay;
  let config: Configure;

  beforeEach(() => {
    config = new Configure(undefined);
    spyOn(config, 'get').and.returnValue('1.0');
    versionDisplay = new VersionDisplay(config);
  });

  it('assigns the application version to the view', () => {
    expect(versionDisplay.version).toEqual('1.0');
  });
});
