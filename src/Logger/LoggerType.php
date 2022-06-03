<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Logger;

enum LoggerType: string
{
    case FILE = 'file';
    case STREAM = 'stream';
}
