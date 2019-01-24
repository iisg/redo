<?php
namespace Repeka\Plugins\Redo\Service;

class KohaXmlResourceDownloader {
    /** @var string */
    private $url;

    public function __construct(string $url) {
        $this->url = $url;
    }

    public function downloadById(string $id): ?string {
        $resourceUrl = $this->getUrl($id);
        $result = @file_get_contents($resourceUrl);
        if ($result === false) {
            return null;
        } else {
            return $result;
        }
    }

    private function getUrl(string $id): string {
        return $this->url . $id;
    }
}
