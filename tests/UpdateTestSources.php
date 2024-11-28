<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 */

declare(strict_types=1);

namespace Siel\AcumulusMa2\tests;

use Siel\Acumulus\Tests\Magento\AcumulusTestUtils;

use function dirname;
use function is_array;
use function is_int;

function getRootPath(): string
{
    // Set the admin path, unaware that our plugin may be symlinked.
    $rootPath = dirname(__DIR__, 4);

    // if our component is symlinked, we need to redefine $rootPath. Try to find it by
    // looking at the --bootstrap option as passed to phpunit or at the script name passed
    // to PHP CLI
    global $argv;
    if (is_array($argv)) {
        $i = array_search('--bootstrap', $argv, true);
        // if we found --bootstrap, the value is in the next entry.
        if (is_int($i) && $i < count($argv) - 1) {
            $bootstrapFile = $argv[$i + 1];
        } elseif (count($argv) === 1 && str_contains($argv[0], 'vendor')) {
            $bootstrapFile = $argv[0];
        }
        if (isset($bootstrapFile)) {
            $rootPath = substr($bootstrapFile, 0, strpos($bootstrapFile, 'vendor') - 1);
        }
    }
    return $rootPath;
}

require_once getRootPath() . '/vendor/autoload.php';

/**
 * UpdateTestSources updates {type}{id}.json test data based on regexp search and replace.
 */
class UpdateTestSources
{
    use AcumulusTestUtils;

    public function execute(): void
    {
        $this->updateTestSources();
    }
}

(new UpdateTestSources())->execute();
