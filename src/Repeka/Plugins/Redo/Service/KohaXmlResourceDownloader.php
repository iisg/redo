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
            return mb_convert_encoding(
                $result,
                'UTF-8',
                mb_detect_encoding($result, 'UTF-8, ISO-8859-1', true)
            );
        }
    }

    private function getUrl(string $id): string {
        return $this->url . $id;
    }
}
