<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Doctrine\Mapping;

use Doctrine\Persistence\Mapping\Driver\PHPDriver;

class EnhancedPHPDriver extends PHPDriver
{
    public function __construct( // phpcs:ignore
        $locator,
        private array $emConfig,
        private bool $loadMappingsUsingFunctionalStyle = false,
    ) {
        parent::__construct($locator);
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
