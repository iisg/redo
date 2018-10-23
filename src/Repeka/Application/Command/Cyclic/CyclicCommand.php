<?php
namespace Repeka\Application\Command\Cyclic;

interface CyclicCommand {
    public function getName();

    public function getIntervalInMinutes(): int;
}
