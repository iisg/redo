import {SizeFormatValueConverter} from "./size-format";

describe(SizeFormatValueConverter.name, () => {
  let sizeFormat: SizeFormatValueConverter;

  beforeEach(() => {
    sizeFormat = new SizeFormatValueConverter();
  });

  it("returns '' when infinity", () => {
    let formatted = sizeFormat.toView(Infinity);
    expect(formatted).toEqual('');
  });

  it("returns size in bytes", () => {
    let size = 1;
    let formatted = sizeFormat.toView(size);
    expect(formatted).toEqual('1 bytes');
  });

  it("returns size in KiloBytes", () => {
    let size = 1024;
    let formatted = sizeFormat.toView(size);
    expect(formatted).toEqual('1 KB');
  });

  it("returns size in MegaBytes", () => {
    let size = Math.pow(1024, 2);
    let formatted = sizeFormat.toView(size);
    expect(formatted).toEqual('1 MB');
  });

  it("returns size in GigaBytes", () => {
    let size = Math.pow(1024, 3);
    let formatted = sizeFormat.toView(size);
    expect(formatted).toEqual('1 GB');
  });

  it("returns size in TeraBytes", () => {
    let size = Math.pow(1024, 4);
    let formatted = sizeFormat.toView(size);
    expect(formatted).toEqual('1 TB');
  });

  it("returns '' when precision is less than 0", () => {
    let size = 1024 + 512;
    let formatted = sizeFormat.toView(size, -1);
    expect(formatted).toEqual('');
  });

  it("returns size with precision == 2", () => {
    let size = 1024 + 512;
    let formatted = sizeFormat.toView(size, 2);
    expect(formatted).toEqual('1.50 KB');
  });
});
