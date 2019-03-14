<?php
namespace Repeka\Application\DependencyInjection;

interface WithBundleDependencies {
    public function getDependentBundles(): array;
}
