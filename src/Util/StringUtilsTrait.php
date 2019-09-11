<?php
declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Util;

use Ramsey\Uuid\Uuid;

use function random_int;
use function strlen;

trait StringUtilsTrait
{
    private function generateRandomString(int $length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    private function generateV4Uuid(): string
    {
        return Uuid::uuid4()->toString();
    }
}
