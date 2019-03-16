<?php
namespace Repeka\Plugins\Redo\Tests\Service;

use PHPUnit_Framework_TestCase;
use Repeka\Plugins\Redo\Service\KohaXmlResourceDownloader;

class KohaXmlResourceDownloaderTest extends PHPUnit_Framework_TestCase {

    public function testDownload() {
        $barcode = 100000301083;
        $downloader = new KohaXmlResourceDownloader(
            'http://koha.biblos.pk.edu.pl/cgi-bin/koha/opac-export-simple.pl?op=export&format=marcxml&skip_entity_encoding=1&barcode='
        );
        $result = $downloader->downloadById($barcode);
        $result = substr($result, 1023, 8); // "ogólnie"
        $this->assertEquals("UTF-8", mb_detect_encoding($result, "UTF-8"));
        $this->assertTrue(strpos($result, "ó") !== false);
    }
}
