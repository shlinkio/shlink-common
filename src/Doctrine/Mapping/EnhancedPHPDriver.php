<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Doctrine\Mapping;

use Doctrine\Persistence\Mapping\Driver\PHPDriver;

class EnhancedPHPDriver extends PHPDriver
{
    private array $emConfig;
    private bool $loadMappingsUsingFunctionalStyle;

    public function __construct($locator, array $emConfig, bool $loadMappingsUsingFunctionalStyle = false) // phpcs:ignore
    {
        parent::__construct($locator);
        $this->emConfig = $emConfig;
        $this->loadMappingsUsingFunctionalStyle = $loadMappingsUsingFunctionalStyle;
    }

    protected function loadMappingFile($file): array // phpcs:ignore
    {
        $metadata = $this->metadata;
        $emConfig = $this->emConfig;
        $result = include $file;

        if ($this->loadMappingsUsingFunctionalStyle) {
            $result($metadata, $emConfig);
        }

        return [$metadata->getName() => $metadata];
    }
}
