<?php
namespace Repeka\Tests\Integration\FullTextSearch;

use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

class FtsSearchCommandIntegrationTest extends IntegrationTestCase {

    use FixtureHelpers;

    private $phpBookResource;
    private $phpAndMySQLBookResource;

    public function setUp() {
        parent::setUp();
        $this->loadAllFixtures();
        $this->executeCommand('repeka:fts:initialize');
        $this->phpBookResource = $this->findResourceByContents(['Tytul' => 'PHP - to można leczyć!']);
        $this->phpAndMySQLBookResource = $this->findResourceByContents(['Tytuł' => 'PHP i MySQL']);
    }

    public function testSearchWhenDifferentNumberOfArgumentsThanValues() {
        $this->expectException(\InvalidArgumentException::class);
        $this->executeCommand('repeka:fts:search' . ' -m name -w aaa -m sdfds');
    }

    public function testSearchWithOneArg() {
        $output = $this->executeCommand('repeka:fts:search' . ' -m tytul -w php');
        $this->assertContains('2 results', $output);
        $this->assertContains('Id: ' . $this->phpAndMySQLBookResource->getId(), $output);
        $this->assertContains('Id: ' . $this->phpBookResource->getId(), $output);
    }

    public function testSearchWithMultiArg() {
        $output = $this->executeCommand('repeka:fts:search' . ' -m tytul -w php -m opis -w Błędy');
        $this->assertContains('1 results', $output);
        $this->assertContains('Id: ' . $this->phpAndMySQLBookResource->getId(), $output);
    }

    public function testSearchSubmetadata() {
        $output = $this->executeCommand('repeka:fts:search' . ' -m url_label -w więcej');
        $this->assertContains('1 results', $output);
        $this->assertContains('Id: ' . $this->phpBookResource->getId(), $output);
    }

    public function testSearchWithSetSize() {
        $output = $this->executeCommand('repeka:fts:search' . ' -m tytul -w php -l 1');
        $this->assertContains('1 results', $output);
        $this->assertContains('Id: ' . $this->phpAndMySQLBookResource->getId(), $output);
    }

    public function testSearchWithSetOffset() {
        $output = $this->executeCommand('repeka:fts:search' . ' -m tytul -w php -o 1');
        $this->assertContains('1 results', $output);
        $this->assertContains('Id: ' . $this->phpBookResource->getId(), $output);
    }

    public function testSearchShowFullTextSearch() {
        $output = $this->executeCommand('repeka:fts:search' . " -m tytul -w 'php MySQL'");
        // mimo ze tylko jeden zasob posiada oba slowa w tytule to są dwa wyniki
        $this->assertContains('2 results', $output);
        $this->assertContains('Id: ' . $this->phpAndMySQLBookResource->getId(), $output);
        $this->assertContains('Id: ' . $this->phpBookResource->getId(), $output);
    }
}
