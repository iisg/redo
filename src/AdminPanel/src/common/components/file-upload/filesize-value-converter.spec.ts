import {FilesizeValueConverter} from "./filesize-value-converter";

describe('filesize value converter', () => {

  let valueConverter: FilesizeValueConverter;
  beforeEach(() => valueConverter = new FilesizeValueConverter());

  it('should return nothing when there is no filesize', () => {
    expect(valueConverter.toView('text')).toBe('-');
  });

  it('should round the filesize based on the configured precision', () => {
    const size = 1024 + 512;
    expect(valueConverter.toView(size)).toBe('1.5 kB');
    expect(valueConverter.toView(size, 2)).toBe('1.50 kB');
  });

  it('should recognize bytes', () => {
    expect(valueConverter.toView(1, 0)).toBe('1 b');
  });

  it('should recognize KiloBytes', () => {
    expect(valueConverter.toView(Math.pow(1024, 1), 0)).toBe('1 kB');
  });

  it('should recognize MegaBytes', () => {
    expect(valueConverter.toView(Math.pow(1024, 2), 0)).toBe('1 MB');
  });

  it('should recognize GigaBytes', () => {
    expect(valueConverter.toView(Math.pow(1024, 3), 0)).toBe('1 GB');
  });

  it('should recognize TeraBytes', () => {
    expect(valueConverter.toView(Math.pow(1024, 4), 0)).toBe('1 TB');
  });

  it('should recognize PetaBytes', () => {
    expect(valueConverter.toView(Math.pow(1024, 5), 0)).toBe('1 PB');
  });
});
