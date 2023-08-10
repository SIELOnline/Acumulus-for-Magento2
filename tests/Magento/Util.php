<?php

declare(strict_types=1);

namespace Siel\AcumulusMa2\tests\Magento;

use DateTime;

/**
 * Utils contains test utility functions.
 */
class Util
{
    public static function eTime(string $location = '', bool $doEcho = true): string
    {
        $line = getTime() . ' ' . $location . PHP_EOL;
        if ($doEcho) {
            echo $line;
        }
        return $line;
    }

    public static function getTime(): string
    {
        $now = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
        return $now->format('H:i:s.u');
    }

}
