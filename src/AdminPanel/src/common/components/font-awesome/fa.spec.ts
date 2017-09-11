import {Fa} from "./fa";

describe(Fa.name, () => {
  it('sets name', () => {
    const fa = new Fa();
    fa.nameChanged('test');
    expect(fa.name).toBe('test');
    expect(fa['fw']).toBe(false);
    expect(fa['spin']).toBe(false);
  });

  it('sets options', () => {
    const fa = new Fa();
    fa.nameChanged('test fw spin');
    expect(fa.name).toBe('test');
    expect(fa['fw']).toBe(true);
    expect(fa['spin']).toBe(true);
  });

  it("doesn't retain options after disabling", () => {
    const fa = new Fa();
    fa.nameChanged('test fw');
    fa.nameChanged('test');
    expect(fa['fw']).toBe(false);
  });

  it('overrides name-options with dedicated options', () => {
    const fa = new Fa();
    fa.nameChanged('test fw');
    fa.optionsChanges('spin');
    expect(fa.name).toBe('test');
    expect(fa['fw']).toBe(false);
    expect(fa['spin']).toBe(true);
  });

  it('handles prefixes', () => {
    const fa = new Fa();
    fa.nameChanged('fa-test fa-fw');
    expect(fa.name).toBe('test');
    expect(fa['fw']).toBe(true);
    fa.optionsChanges('fa-spin');
    expect(fa['fw']).toBe(false);
    expect(fa['spin']).toBe(true);
  });
});
