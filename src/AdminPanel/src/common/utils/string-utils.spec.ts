import {stringToHtml} from "./string-utils";

describe('string-utils', () => {
  describe('stringToHtml', () => {
    it('leaves regular text unchanged', () => {
      expect(stringToHtml('lorem ipsum')).toEqual('lorem ipsum');
    });

    it('escapes entities', () => {
      expect(stringToHtml('<h1>foo</h1>')).toEqual('&lt;h1&gt;foo&lt;/h1&gt;');
    });

    it('replaces newlines with breaks', () => {
      expect(stringToHtml('a\nb\rc\n\rd\r\ne')).toEqual('a<br>b<br>c<br><br>d<br><br>e');
    });
  });
});
