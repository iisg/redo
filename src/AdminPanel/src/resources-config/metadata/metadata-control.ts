export enum MetadataControl {
  TEXT = 'text',
  TEXTAREA = 'textarea',
  INTEGER = 'integer',
  DATE = 'date',
  BOOLEAN = 'boolean',
  RELATIONSHIP = 'relationship',
  FILE = 'file',
  DISPLAY_STRATEGY = 'display-strategy',
}

export const filterableControls = [
  MetadataControl.TEXT,
  MetadataControl.TEXTAREA,
  MetadataControl.INTEGER,
  MetadataControl.RELATIONSHIP,
  MetadataControl.DISPLAY_STRATEGY
];
