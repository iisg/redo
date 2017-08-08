import {trimToLengthBetweenWords, firstLineWithContent, stringToHtml} from "./string-utils";

describe('string-utils', () => {
  describe('trimToLengthBetweenWords', () => {
    it('returns short strings untouched', () => {
      const result = trimToLengthBetweenWords('12 45', 10);
      expect(result).toEqual('12 45');
    });

    it('returns exact strings untouched', () => {
      const result = trimToLengthBetweenWords('123 567 90x', 11);
      expect(result).toEqual('123 567 90x');
    });

    it('hard-trims very long single words', () => {
      const result = trimToLengthBetweenWords('Llanfairpwllgwyngyll', 10);
      expect(result).toEqual('Llanfairp…');
    });

    it('soft-trims long phrases', () => {
      const result = trimToLengthBetweenWords('Lorem ipsum dolor sit amet', 15);
      expect(result).toEqual('Lorem ipsum…');
    });

    it('soft-trims long phrases at word boundaries', () => {
      const result = trimToLengthBetweenWords('Lorem ipsum dolor sit amet', 11);
      expect(result).toEqual('Lorem…');
    });

    it('soft-trims long phrases after whitespace', () => {
      const result = trimToLengthBetweenWords('Lorem ipsum dolor sit amet', 12);
      expect(result).toEqual('Lorem ipsum…');
    });

    it('collapses whitespace', () => {
      const result = trimToLengthBetweenWords('    Lorem    ipsum dolor sit amet', 12);
      expect(result).toEqual('Lorem ipsum…');
    });

    it('trims single-letter phrases', () => {
      const result = trimToLengthBetweenWords('a a a a a a a a a a', 2);
      expect(result).toEqual('a…');
    });
  });

  describe('firstLineWithContent', () => {
    it('returns everything for one-line input', () => {
      const result = firstLineWithContent('lorem ipsum');
      expect(result).toEqual('lorem ipsum');
    });

    it('skips leading empty lines', () => {
      const result = firstLineWithContent('\n\n\r\r\n\n\r\ntest\ntext\n');
      expect(result).toEqual('test');
    });

    it('skips leading empty lines and returns sole content line', () => {
      const result = firstLineWithContent('\n\r\ntest');
      expect(result).toEqual('test');
    });

    it('returns only first line', () => {
      const result = firstLineWithContent('one\ntwo\n\nthree\n');
      expect(result).toEqual('one');
    });

    it('skips blank characters in leading lines', () => {
      const result = firstLineWithContent('\n \n\t\ntest');
      expect(result).toEqual('test');
    });

    it('handles empty input', () => {
      const result = firstLineWithContent('');
      expect(result).toEqual('');
    });

    it('handles blank input', () => {
      const result = firstLineWithContent(' ');
      expect(result).toEqual('');
    });
  });

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
