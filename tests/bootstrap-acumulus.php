<?php
/**
 * Copied (and edited) from /dev/tests/integration/framework/bootstrap.php.
 *
 * @noinspection PhpMultipleClassDeclarationsInspection
 * @noinspection PhpIncludeInspection
 * @noinspection UntrustedInclusionInspection
 */

declare(strict_types=1);

use Magento\Framework\App\Utility\Files;
use Magento\Framework\Autoload\AutoloaderRegistry;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\DirSearch;
use Magento\Framework\Logger\Handler\Debug;
use Magento\Framework\Logger\Handler\System;
use Magento\Framework\Profiler\Driver\Standard;
use Magento\Framework\Shell;
use Magento\Framework\Shell\CommandRenderer;
use Magento\Framework\View\Design\Theme\ThemePackageList;
use Magento\TestFramework\Application;
use Magento\TestFramework\Bootstrap\DocBlock;
use Magento\TestFramework\Bootstrap\Environment;
use Magento\TestFramework\Bootstrap\MemoryFactory;
use Magento\TestFramework\Bootstrap\Settings;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Monolog\Logger;

$magentoRoot = '/var/www/html';
$testBaseDir = "$magentoRoot/dev/tests/integration";
$testFrameworkDir = "$testBaseDir/framework";
$acumulusTestDir = "$magentoRoot/vendor/siel/acumulus-ma2/tests";

// This is more to prevent incorrect PhpUndefinedConstantInspection warnings by PhpStorm.
// It should exist.
if (!defined('TESTS_INSTALL_CONFIG_FILE')) {
    define('TESTS_INSTALL_CONFIG_FILE', 'etc/install-config-mysql.php');
}

require_once "$magentoRoot/app/bootstrap.php";
require_once "$testFrameworkDir/autoload.php";

error_reporting(E_ALL);

if (!defined('TESTS_TEMP_DIR')) {
    define('TESTS_TEMP_DIR', "$testBaseDir/tmp");
}

try {
    setCustomErrorHandler();

    // Link our 'install-config-mysql.php' file to the one in "$testBasedir/etc".
    if (file_exists($testBaseDir . '/' . TESTS_INSTALL_CONFIG_FILE)) {
        unlink($testBaseDir . '/' . TESTS_INSTALL_CONFIG_FILE);
    }
    copy($acumulusTestDir . '/' . TESTS_INSTALL_CONFIG_FILE, $testBaseDir . '/' . TESTS_INSTALL_CONFIG_FILE);

    /* Bootstrap the application */
    $settings = new Settings($testBaseDir, get_defined_constants());

    require_once "$testFrameworkDir/deployTestModules.php";

    if ($settings->get('TESTS_EXTRA_VERBOSE_LOG')) {
        $filesystem = new \Magento\Framework\Filesystem\Driver\File();
        $exceptionHandler = new \Magento\Framework\Logger\Handler\Exception($filesystem);
        $loggerHandlers = [
            'system' => new System($filesystem, $exceptionHandler),
            'debug' => new Debug($filesystem),
        ];
        $shell = new Shell(
            new CommandRenderer(),
            new Logger('main', $loggerHandlers)
        );
    } else {
        $shell = new Shell(new CommandRenderer());
    }
    $installConfigFile = $settings->getAsConfigFile('TESTS_INSTALL_CONFIG_FILE');
    $globalConfigFile = $settings->getAsConfigFile('TESTS_GLOBAL_CONFIG_FILE');
    $installDir = TESTS_TEMP_DIR . '/sandbox-acumulus';

    $application = new Application(
        $shell,
        $installDir,
        $installConfigFile,
        $globalConfigFile,
        $settings->get('TESTS_GLOBAL_CONFIG_DIR'),
        $settings->get('TESTS_MAGENTO_MODE'),
        AutoloaderRegistry::getAutoloader(),
        true,
        null
    );
    $bootstrap = new \Magento\TestFramework\Bootstrap(
        $settings,
        new Environment(),
        new DocBlock("$testBaseDir/testsuite"),
        new \Magento\TestFramework\Bootstrap\Profiler(new Standard()),
        $shell,
        $application,
        new MemoryFactory($shell)
    );
    $bootstrap->runBootstrap();
    $application->initialize();
    Bootstrap::setInstance(new Bootstrap($bootstrap));

    $dirSearch = Bootstrap::getObjectManager()->create(DirSearch::class);
    $themePackageList = Bootstrap::getObjectManager()->create(ThemePackageList::class);
    Files::setInstance(
        new Magento\Framework\App\Utility\Files(
            new ComponentRegistrar(),
            $dirSearch,
            $themePackageList
        )
    );
    $overrideConfig = Bootstrap::getObjectManager()->create(Magento\TestFramework\Workaround\Override\Config::class);
    $overrideConfig->init();
    Magento\TestFramework\Workaround\Override\Config::setInstance($overrideConfig);
    Magento\TestFramework\Workaround\Override\Fixture\Resolver::setInstance(new Resolver($overrideConfig));
    Magento\TestFramework\Fixture\DataFixtureStorageManager::setStorage(new Magento\TestFramework\Fixture\DataFixtureStorage());
    /* Unset declared global variables to release the PHPUnit from maintaining their values between tests */
    unset($magentoRoot, $testBaseDir, $testFrameworkDir, $settings, $shell, $application, $bootstrap, $overrideConfig);
} catch (Exception $e) {
    //phpcs:ignore Magento2.Security.LanguageConstruct
    echo $e . PHP_EOL;
    // phpcs:ignore Magento2.Security.LanguageConstruct.ExitUsage
    exit(1);
}

/**
 * Set custom error handler
 */
function setCustomErrorHandler(): void
{
    set_error_handler(
        static function ($errNo, $errStr, $errFile, $errLine) {
            $errLevel = error_reporting();
            if (($errLevel & $errNo) !== 0) {
                $errorNames = [
                    E_ERROR => 'Error',
                    E_WARNING => 'Warning',
                    E_PARSE => 'Parse',
                    E_NOTICE => 'Notice',
                    E_CORE_ERROR => 'Core Error',
                    E_CORE_WARNING => 'Core Warning',
                    E_COMPILE_ERROR => 'Compile Error',
                    E_COMPILE_WARNING => 'Compile Warning',
                    E_USER_ERROR => 'User Error',
                    E_USER_WARNING => 'User Warning',
                    E_USER_NOTICE => 'User Notice',
                    E_STRICT => 'Strict',
                    E_RECOVERABLE_ERROR => 'Recoverable Error',
                    E_DEPRECATED => 'Deprecated',
                    E_USER_DEPRECATED => 'User Deprecated',
                ];

                $errName = $errorNames[$errNo] ?? '';

                throw new \PHPUnit\Framework\Exception(
                    sprintf('%s: %s in %s:%s.', $errName, $errStr, $errFile, $errLine),
                    $errNo
                );
            }
        }
    );
}
