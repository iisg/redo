<?php
namespace Repeka\Application\ParamConverter\MetadataValueProcessor;

use Repeka\Application\Upload\FilesystemDriver;
use Repeka\Application\Upload\ResourceFilePathGenerator;
use Repeka\Domain\Entity\MetadataControl;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class FileMetadataValueProcessorStrategy implements MetadataValueProcessorStrategy {
    /** @var ResourceFilePathGenerator */
    private $pathGenerator;
    /** @var FilesystemDriver */
    private $filesystemDriver;

    public function __construct(ResourceFilePathGenerator $pathGenerator, FilesystemDriver $filesystemDriver) {
        $this->pathGenerator = $pathGenerator;
        $this->filesystemDriver = $filesystemDriver;
    }

    public function processValue($fileRef, Request $request) {
        /** @var UploadedFile $file */
        $file = $request->files->get($fileRef);
        if ($file) {
            $fileName = $file->getClientOriginalName();
            $tempFolder = $this->pathGenerator->getTemporaryPath();
            if (!$this->filesystemDriver->exists($tempFolder)) {
                $this->filesystemDriver->mkdirRecursive($tempFolder);
            }
            $storedFile = $file->move($tempFolder, $fileName);
            chmod($storedFile->getRealPath(), 0660); // make sure file isn't executable
            return $this->pathGenerator->getTemporaryFolderName() . '/' . $fileName;
        } else {
            return $fileRef;
        }
    }

    public function getSupportedControl(): MetadataControl {
        return MetadataControl::FILE();
    }
}
