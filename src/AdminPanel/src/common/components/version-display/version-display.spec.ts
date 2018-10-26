import {VersionDisplay} from "./version-display";

describe(VersionDisplay.name, () => {
  let versionDisplay: VersionDisplay;

  beforeEach(() => {
    window['FRONTEND_CONFIG'] = {application_version: '1.0'};
    versionDisplay = new VersionDisplay();
  });

  it('assigns the application version to the view', () => {
    expect(versionDisplay.version).toEqual('1.0');
  });
});
