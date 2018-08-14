declare namespace JQueryUI {
  interface EffectOptions { }
}

declare namespace Fancytree {
  // currently no support for extensions: https://github.com/mar10/fancytree/issues/836

  // from https://github.com/mar10/fancytree/wiki/ExtGlyph
  interface GlyphExtensionOptions {
    icon?: any;
    glyph?: {
      preset: "awesome3" | "awesome4" | "bootstrap3"
      map?: {
        _addClass?: string
        checkbox?: string
        checkboxSelected?: string
        checkboxUnknown?: string
        dragHelper?: string
        dropMarker?: string
        error?: string
        expanderClosed?: string | {html}
        expanderLazy?: string
        expanderOpen?: string
        loading?: string
        nodata?: string
        noExpander?: string
        radio?: string
        radioSelected?: string
        // Default node icons.
        // (Use tree.options.icon callback to define custom icons based on node data)
        doc?: string
        docOpen?: string
        folder?: string
        folderOpen?: string
      }
    }
  }
  interface NodeTypesExtensionOptions {
    types: any
  }
}