<?php

declare(strict_types=1);

namespace Siel\AcumulusMa2\Data\Form\Element;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;

use function sprintf;

/**
 * Collection form element for our forms.
 *
 * This class is a simple override of
 * {@see \Magento\Framework\Data\Form\Element\AbstractElement}. It allows to
 * render a set of "subfields" without their labels, descriptions and form
 * element wrapper tags.
 */
class Collection extends AbstractElement
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
        $this->setType('collection');
        $this->_renderer = null;
    }

    /**
     * Override to get the grandparent version.
     *
     * A fieldset will render separate form elements within its fieldset tags,
     * but we want a light rendering version of that: only the html elements,
     * no labels, description, wrappers. The {@see AbstractElement} does so.
     * For the element part it calls {@see getElementHtml()}, so we override that
     * as well to render our collection
     */
//    public function getDefaultHtml()
//    {
//        return AbstractElement::getDefaultHtml();
//    }

    /**
     * Principal override: this performs the changes.
     */
    public function getElementHtml(): string
    {
        $html = '';

        $html .= $this->getBeforeElementHtml();
        $html .= sprintf('<div id="%s" class="control-value admin__field-value">', $this->getHtmlId());
        $html .= $this->_elementsToHtml($this->getElements());
        $html .= '</div>';
        $html .= $this->getAfterElementHtml();

        return $html;
    }

    /**
     * Returns the child elements as an HTML string.
     *
     * This override prevents wrappers and labels being rendered.
     *
     * @noinspection PhpUndefinedMethodInspection  magic getters and setters for
     *   before and afterElementHtml, noSpan, label, and text.
     */
    protected function _elementsToHtml($elements): string
    {
        $html = '';

        foreach ($elements as $element) {
            /** @var AbstractElement $element */
            $this->_renderer = null;
            $element->setBeforeElementHtml(null);
            $element->setAfterElementHtml(null);
            $element->setNoSpan(true);
            $element->setLabel(null);
            if ($element->getType() === 'note') {
                $html .= $element->getText();
            } else {
                $html .= $element->getDefaultHtml();
            }
        }

        return $html;
    }
}
