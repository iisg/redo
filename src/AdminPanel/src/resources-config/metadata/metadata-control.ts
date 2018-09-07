export enum MetadataControl {
  TEXT = 'text',
  TEXTAREA = 'textarea',
  WYSIWYG_EDITOR = 'wysiwyg-editor',
  INTEGER = 'integer',
  FLEXIBLE_DATE = 'flexible-date',
  TIMESTAMP = 'timestamp',
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
