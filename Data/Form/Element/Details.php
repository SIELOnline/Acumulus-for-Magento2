<?php

declare(strict_types=1);

namespace Siel\AcumulusMa2\Data\Form\Element;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Escaper;

/**
 * Details form element for Acumulus forms in Magento.
 *
 * This class is a simple override of Magento's
 * {@see \Magento\Framework\Data\Form\Element\Fieldset} element. It just
 * changes <fieldset> into <details>, optionally adding the open attribute, and
 * <legend> into <summary>.
 */
class Details extends Fieldset
{
    /**
     * Override of the fieldset constructor to change a few things.
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->_renderer = null;
        $this->setType('details');
    }


    /**
     * Override to prevent a <div> around our <details>.
     */
    public function getDefaultHtml(): string
    {
        return $this->getElementHtml();
    }

    /**
     * Principal override: this performs the changes.
     */
    public function getElementHtml(): string
    {
        $open = !empty($this->_data['open']) ? ' open' : '';
        return str_replace(
            ['<fieldset', '</fieldset>', '<legend', '</legend>'],
            ["<details$open", '</details>', '<summary', '</summary>'],
            parent::getElementHtml()
        );
    }
}
