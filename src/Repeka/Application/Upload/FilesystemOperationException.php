<?php
namespace Repeka\Application\Upload;

class FilesystemOperationException extends \RuntimeException {
    private $operation;
    private $arguments;

    public function __construct(string $method, array $arguments) {
        list($class, $this->operation) = explode('::', $method);
        $methodRef = new \ReflectionMethod($class, $this->operation);
        $parameterNames = array_map(
            function (\ReflectionParameter $parameter): string {
                return $parameter->getName();
            },
            $methodRef->getParameters()
        );
        $this->arguments = array_combine($parameterNames, $arguments);
    }

    public function getOperation(): string {
        return $this->operation;
    }

    public function getArguments(): array {
        return $this->arguments;
    }
}
