import {About} from "./about";

describe('About', () => {
  let about;

  beforeEach(() => {
    about = new About();
  });

  it('specifies the text', () => {
    expect(about.text).toEqual("This is it.");
  });
});
