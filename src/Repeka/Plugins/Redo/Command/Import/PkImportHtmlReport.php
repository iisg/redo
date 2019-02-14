<?php
namespace Repeka\Plugins\Redo\Command\Import;

use Twig_Environment;
use Twig_Loader_Filesystem;

class PkImportHtmlReport {
    private const TEMPLATE_FILE = './pk-import-report-template.twig';
    private const TEMPLATE_FILE_CSV = './pk-import-report-template-csv.twig';
    private const TIME_FORMAT = 'Y-m-d_H-i-s';

    private $outputFileName;
    private $outputFileNameCsv;
    private $dataArray = ['resources' => []];

    public function __construct(string $applicationUrl, string $xmlFileName) {
        $this->dataArray['application_url'] = $applicationUrl;
        $this->dataArray['date'] = date(PkImportHtmlReport::TIME_FORMAT);
        $this->dataArray['file'] = pathinfo($xmlFileName, PATHINFO_FILENAME);
        $this->outputFileName = sprintf(
            '%s/import/report-%s_%s.html',
            \AppKernel::VAR_PATH,
            $this->dataArray['file'],
            $this->dataArray['date']
        );
        $this->outputFileNameCsv = sprintf(
            '%s/import/report-%s_%s.csv',
            \AppKernel::VAR_PATH,
            $this->dataArray['file'],
            $this->dataArray['date']
        );
    }

    public function writeReport() {
        $loader = new Twig_Loader_Filesystem(__DIR__);
        $twig = new Twig_Environment($loader);
        if ($this->dataArray['resources']) {
            if (!file_exists($this->outputFileName)) {
                $fileData = $twig->render(PkImportHtmlReport::TEMPLATE_FILE, $this->dataArray);
                file_put_contents($this->outputFileName, $fileData);
            }
            if (!file_exists($this->outputFileNameCsv)) {
                $fileData = $twig->render(PkImportHtmlReport::TEMPLATE_FILE_CSV, $this->dataArray);
                file_put_contents($this->outputFileNameCsv, $fileData);
            }
        }
    }

    public function addResourceImportStatus($oldId, $resourceId, $status, $unfitTypeValues, $notUsedTerms) {
        $this->dataArray['resources'][] = [
            'oldId' => $oldId,
            'id' => $resourceId,
            'status' => $status,
            'unfitTypeValues' => $unfitTypeValues,
            'notUsedTerms' => $notUsedTerms,
        ];
    }

    public function setInvalidMetadataKeysInfo($invalidMetadataKeys) {
        $this->dataArray['invalidMetadataKeys'] = $invalidMetadataKeys;
    }

    public function setError($error) {
        $this->dataArray['error'] = $error;
    }

    public function getOutputFilename(): string {
        return $this->outputFileName;
    }
}
