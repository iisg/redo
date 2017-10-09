import {Metadata} from "./metadata";

export class SystemMetadata {
  // Do not move these to Metadata class. It won't work. TypeScript, yay.
  static readonly PARENT: Metadata = $.extend(new Metadata(), {id: -1, control: 'relationship', baseId: -1});
  static readonly USERNAME: Metadata = $.extend(new Metadata(), {id: -2, control: 'string', baseId: -2});
}
