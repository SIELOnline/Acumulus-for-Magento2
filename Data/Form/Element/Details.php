<?php

namespace Siel\AcumulusMa2\Data\Form\Element;

use Magento\Framework\Data\Form\Element\Fieldset;

/**
 * Details form element for Magento forms
 *
 * This class is a simple override of Magento's own fieldset element. It just
 * changes <fieldset> into <details>, optionally adding the open attribute, and
 * <legend> into <summary>.
 */
class Details extends Fieldset
{
    /**
     * Override to prevent a <div> around our <details>.
     */
    public function getDefaultHtml()
    {
        return $this->getElementHtml();
    }

    /**
     * Principal override: this performs the changes.
     */
    public function getElementHtml()
    {
        $open = !empty($this->_data['open']) ? ' open' : '';
        $html = parent::getElementHtml();
        $html = str_replace(
            ['<fieldset', '</fieldset>', '<legend', '</legend>'],
            ["<details$open", '</details>', '<summary', '</summary>'],
            $html
        );
        return $html;
    }

    /**
     * Sneaky override to prevent using a renderer instead of our override.
     *
     * This method is called directly after the constructor, but overriding the
     * constructor seems much brittler.
     */
    public function setId($id)
    {
        $this->_renderer = null;
        return parent::setId($id);
    }
}
