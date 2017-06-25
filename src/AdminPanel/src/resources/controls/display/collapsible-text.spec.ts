import {CollapsibleText} from "./collapsible-text";
describe('collapsible-text', () => {
  function createInstance(text: string, maxLength: number): CollapsibleText {
    const instance = new CollapsibleText();
    instance.text = text;
    instance.maxLength = maxLength;
    return instance;
  }

  it('always hides at least 10%', () => {
    const text = Array(1000).fill('a').join(' '); // text.length == 1999, text == 'a a a a...a a a'
    const instance = createInstance(text, 1998);
    expect(instance.amountHidden).toEqual('10%');
  });

  it('never says 100% hidden', () => {
    const text = Array(10000).fill('a').join(' '); // text.length == 19999, text == 'a a a a...a a a'
    const instance = createInstance(text, 4);
    expect(instance.collapsedText).toEqual('a…');
    expect(instance.amountHidden).toEqual('99%');
  });
});
