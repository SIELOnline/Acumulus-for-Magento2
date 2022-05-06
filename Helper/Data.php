<?php
namespace Siel\AcumulusMa2\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Siel\Acumulus\Helpers\Container;
use Siel\Acumulus\Magento\Helpers\Registry;

/**
 * Acumulus helper
 */
class Data extends AbstractHelper
{
    private static ?Container $acumulusContainer = null;

    /**
     * Helper method that initializes our environment:
     * - autoloader for the library part.
     * - translator
     * - acumulusConfig
     */
    private function init()
    {
        if (static::$acumulusContainer === null) {
            static::$acumulusContainer = new Container('Magento', Registry::getInstance()->getLocale());
        }
    }

    /**
     * Helper method to translate strings.
     *
     * @param string $key
     *  The key to get a translation for.
     *
     * @return string
     *   The translation for the given key or the key itself if no translation
     *   could be found.
     */
    public function t(string $key): string
    {
        $this->init();
        return static::$acumulusContainer->getTranslator()->get($key);
    }

    /**
     * Returns the container object central to this extension.
     *
     * @return \Siel\Acumulus\Helpers\Container
     *   The Acumulus container.
     */
    public function getAcumulusContainer(): Container
    {
        $this->init();
        return static::$acumulusContainer;
    }
}
