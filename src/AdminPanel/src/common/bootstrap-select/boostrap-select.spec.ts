import {BootstrapSelect} from "./bootstrap-select";

describe(BootstrapSelect.name, () => {
  it('finds index of element in list of elements', () => {
    const needle = {id: 2};
    const haystack = [{id: 1}, {id: 2}, {id: 3}];
    expect(BootstrapSelect.findValueIndex(needle, haystack));
  });

  it('finds index of ID in list of IDs', () => {
    const needle = 2;
    const haystack = [1, 2, 3];
    expect(BootstrapSelect.findValueIndex(needle, haystack));
  });

  it('finds index of element in list of IDs', () => {
    const needle = {id: 2};
    const haystack = [1, 2, 3];
    expect(BootstrapSelect.findValueIndex(needle, haystack));
  });

  it('finds index of ID in list of elements', () => {
    const needle = 2;
    const haystack = [{id: 1}, {id: 2}, {id: 3}];
    expect(BootstrapSelect.findValueIndex(needle, haystack));
  });
});
