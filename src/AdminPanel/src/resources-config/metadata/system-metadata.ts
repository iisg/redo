import {Metadata, MetadataConstraints} from "./metadata";
import {SystemResourceKinds} from "../resource-kind/system-resource-kinds";
import {MetadataControl} from "./metadata-control";

export class SystemMetadata {
  // Do not move these to Metadata class. It won't work. TypeScript, yay.
  static readonly PARENT: Metadata = $.extend(new Metadata(), {
    id: -1,
    control: MetadataControl.RELATIONSHIP,
    baseId: -1,
    constraints: new MetadataConstraints({maxCount: 1}),
  });
  static readonly USERNAME: Metadata = $.extend(new Metadata(), {
    id: -2,
    control: MetadataControl.TEXT,
    baseId: -2,
    constraints: new MetadataConstraints({maxCount: 1}),
  });
  static readonly GROUP_MEMBER: Metadata = $.extend(new Metadata(), {
    id: -3,
    control: MetadataControl.RELATIONSHIP,
    baseId: -3,
    constraints: new MetadataConstraints({resourceKind: [SystemResourceKinds.USER_ID]}),
  });
  static readonly REPRODUCTOR: Metadata = $.extend(new Metadata(), {
    id: -4,
    control: MetadataControl.RELATIONSHIP,
    baseId: -4,
    constraints: new MetadataConstraints({maxCount: 1}),
  });
  static readonly RESOURCE_LABEL: Metadata = $.extend(new Metadata(), {
    id: -5,
    control: MetadataControl.TEXT,
    baseId: -5,
    shownInBrief: true,
    copyToChildResource: false,
    displayStrategy: '#{{ r.id }}',
  });
  static readonly VISIBILITY: Metadata = $.extend(new Metadata(), {
    id: -6,
    control: MetadataControl.RELATIONSHIP,
    baseId: -6,
  });
  static readonly TEASER_VISIBILITY: Metadata = $.extend(new Metadata(), {
    id: -7,
    control: MetadataControl.RELATIONSHIP,
    baseId: -7,
  });
  static readonly RESOURCE_LABEL_LANGUAGE: Metadata = $.extend(new Metadata(), {
    id: -8,
    control: MetadataControl.SYSTEM_LANGUAGE,
    baseId: -8,
    shownInBrief: false,
    copyToChildResource: false,
    parentId: -5
  });
}
