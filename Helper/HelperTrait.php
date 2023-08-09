<?php

declare(strict_types=1);

namespace Siel\AcumulusMa2\Helper;

use Siel\Acumulus\Helpers\Container;
use Siel\Acumulus\Helpers\Form;

use function strlen;

/**
 * Trait for accessing the Acumulus helper (form (type), container, translations)
 */
trait HelperTrait
{
    private Data $helper;
    private string $formType = '';
    private ?Form $acumulusForm = null;

    /**
     * @param \Siel\AcumulusMa2\Helper\Data $helper
     *
     * @return void
     */
    public function setHelper(Data $helper): void
    {
        $this->helper = $helper;
    }

    /**
     * Returns the Acumulus container.
     *
     * @return \Siel\Acumulus\Helpers\Container
     */
    public function getAcumulusContainer(): Container
    {
        return $this->helper->getAcumulusContainer();
    }

    /**
     * Returns an Acumulus form.
     *
     * @return \Siel\Acumulus\Helpers\Form
     */
    public function getAcumulusForm(): Form
    {
        if ($this->acumulusForm === null) {
            $this->acumulusForm = $this->getAcumulusContainer()->getForm($this->getFormType());
        }
        return $this->acumulusForm;
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
    protected function t(string $key): string
    {
        return $this->helper->t($key);
    }

    /**
     * Determines and sets the form type based on the FQ class name.
     *
     * This can be called for controller or block form classes. So we do away
     * with the common parts, and then we will see what remains:
     * - All class names should start with:
     *   Siel\AcumulusMa2\(Controller|Block)\Adminhtml
     * - And may end with: Interceptor.
     * - Controllers may end with a nondescript 'Index'.
     * - Forms may end with a nondescript 'Form' or the last part may end with.
     *   'Form'.
     */
    protected function setFormType(): void
    {
        $classType = 'class';
        $class = static::class;
        $classParts = explode('\\', $class);
        if (end($classParts) === 'Interceptor') {
            array_pop($classParts);
        }
        if ($classParts[0] === 'Siel') {
            array_shift($classParts);
        }
        if ($classParts[0] === 'AcumulusMa2') {
            array_shift($classParts);
        }
        if ($classParts[0] === 'Controller' || $classParts[0] === 'Block') {
            $classType = $classParts[0];
            array_shift($classParts);
        }
        if ($classParts[0] === 'Adminhtml') {
            array_shift($classParts);
        }
        if (end($classParts) === 'Index' || end($classParts) === 'Form') {
            array_pop($classParts);
        } elseif (str_ends_with(end($classParts), 'Form')) {
            $classParts[] = substr(array_pop($classParts), 0, -strlen('Form'));
        }

        $class = implode('\\', $classParts);
        switch ($class) {
            case 'Register':
            case 'Activate':
            case 'Config':
            case 'Batch':
                $this->formType = strtolower($class);
                break;
            case 'Config\Advanced':
                $this->formType = 'advanced';
                break;
            case 'Order\Status':
                $this->formType = 'invoice';
                break;
            case 'Plugin\Rate':
                $this->formType = 'rate';
                break;
            default:
                $this->getAcumulusContainer()->getLog()->error("Unknown $classType type $class");
                break;
        }
    }

    /**
     * Returns the form type for the current controller or form.
     *
     * @return string
     */
    public function getFormType(): string
    {
        return $this->formType;
    }
}
