# Repeka OCR Plugin

## Installation

1. Place the plugin sources in `src/Repeka/Plugins/Ocr`.
1. Add the bundle reference in the `src/AppKernel.php`
    ```
    public function registerBundles() {
       // ...
       new Repeka\Plugins\Ocr\RepekaOcrPluginBundle(),
       // ...  
    }
    ```
1. Add routing config in the `app/config/routing.yml`
    ```
    ocr:
      resource: "@RepekaOcrPluginBundle/Controller"
      type: annotation
      prefix: /api/plugins/ocr
    ```

## Configuration

TODO

## Behavior

On every configured resource workflow place, the plugin will send all
files from given metadata kind to the OCR Server.

It then expects a response with OCRed files sent to
`REPEKA_ISNTANCE_ADDRESS/api/plugins/ocr/resources/{id}`. The request
should contain all files that needs to be added to the resource.

TODO currently to simulate it just configure the `metadataToOcr` and 
`metadataForResult` in GUI to some text metadata and go to the 
`http://repeka.local/api/plugins/ocr/resources/ID` to see the magic.
