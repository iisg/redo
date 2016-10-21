<?php
namespace Repeka\CoreModule\UserInterface\DependencyInjection;

class Extension extends BaseExtension {
    const ALIAS = 'core_module';

    public function getAlias() {
        return self::ALIAS;
    }
}
