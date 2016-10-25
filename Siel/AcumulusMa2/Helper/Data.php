<?php
namespace Siel\AcumulusMa2\Helper;

use Magento\Framework\App\Helper\Context;
use Siel\Acumulus\Magento2\Helpers\Registry;
use Siel\Acumulus\Shop\Config;

/**
 * Acumulus helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /** @var \Siel\Acumulus\Shop\Config */
    protected static $acumulusConfig = null;

    /**
     * Siel_Acumulus_Helper_Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
        $this->init();
    }

    /**
     * Helper method that initializes our environment:
     * - autoloader for the library part.
     * - translator
     * - acumulusConfig
     */
    protected function init()
    {
        if (static::$acumulusConfig === null) {
            static::$acumulusConfig = new Config('Magento2', Registry::getInstance()->getLocale());
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
    public function t($key)
    {
        return static::$acumulusConfig->getTranslator()->get($key);
    }

    /**
     * Returns the configuration settings object central to this extension.
     *
     * @return \Siel\Acumulus\Shop\Config
     *   The Acumulus config.
     */
    public function getAcumulusConfig()
    {
        $this->init();
        return static::$acumulusConfig;
    }
}
