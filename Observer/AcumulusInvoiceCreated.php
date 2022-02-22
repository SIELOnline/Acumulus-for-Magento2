<?php

namespace Siel\AcumulusMa2\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Model\Calculation;
use Siel\Acumulus\Helpers\Number;
use Siel\Acumulus\Invoice\Creator;
use Siel\Acumulus\Invoice\Source;
use Siel\Acumulus\Meta;
use Siel\Acumulus\Tag;
use Siel\AcumulusMa2\Helper\Data;

/** @noinspection PhpUnused
 *
 * Siel Acumulus invoice created observer reacts on our own "invoice create"
 * event to add support for specific modules that we do not want in our base
 * code.
 *
 * Modules supported:
 * - Pay Checkout
 * - Sisow
 * - Magecomp Payment Fee
 * - Fooman Surcharge
 */
class AcumulusInvoiceCreated implements ObserverInterface
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Tax\Model\Calculation
     */
    protected $taxCalculation;

    /**
     * @var \Siel\AcumulusMa2\Helper\Data
     */
    private $helper;

    /**
     * SalesOrderSaveAfter constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Siel\AcumulusMa2\Helper\Data $helper
     */
    public function __construct(ScopeConfigInterface $scopeConfig, Calculation $taxCalculation, Data $helper)
    {
        $this->scopeConfig = $scopeConfig;
        $this->taxCalculation = $taxCalculation;
        $this->helper = $helper;
    }

    /**
     * Processes the event triggered before an invoice will be sent to Acumulus.
     *
     * The EventObserver contains the following data properties:
     * array|null invoice
     *   The invoice in Acumulus format as will be sent to Acumulus or null if
     *   another observer already decided that the invoice should not be sent to
     *   Acumulus.
     * \Siel\Acumulus\Invoice\Source invoiceSource
     *   Wrapper around the original Magento order or refund for which the
     *   invoice has been created.
     * \Siel\Acumulus\Invoice\Result localResult
     *   Any local error or warning messages that were created locally.
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var array $invoice */
        $invoice = $observer->getDataByKey('invoice');
        /** @var \Siel\Acumulus\Invoice\Source $invoiceSource */
        $invoiceSource = $observer->getDataByKey('source');

        $this->supportPaycheckout($invoice, $invoiceSource);
        $this->supportSisow($invoice, $invoiceSource);
        $this->supportMagecompPaymentfee($invoice, $invoiceSource);
        $this->supportFoomanSurchargePayment($invoice, $invoiceSource);

        // Pass changes back to Acumulus.
        $observer->setData('invoice', $invoice);
    }

    /**
     * Adds support for the paycheckout module.
     *
     * The paycheckout module allows to add a payment fee to an order. If it
     * does so, the amount is stored in the columns:
     * - base_paycheckout_surcharge_amount
     * - base_paycheckout_surcharge_tax_amount
     * which the module has added to the sales_order and sales_creditmemo
     * tables.
     *
     * @see https://www.paycheckout.com/magento-payment-provider
     *
     * @param array $invoice
     * @param \Siel\Acumulus\Invoice\Source $invoiceSource
     */
    protected function supportPaycheckout(array &$invoice, Source $invoiceSource)
    {
        if ((float) $invoiceSource->getSource()->getBasePaycheckoutSurchargeAmount() !== 0.0) {
            $sign = $invoiceSource->getSign();
            $paymentEx = (float) $sign * $invoiceSource->getSource()->getBasePaycheckoutSurchargeAmount();
            $paymentVat = (float) $sign * $invoiceSource->getSource()->getBasePaycheckoutSurchargeTaxAmount();
            $paymentInc = $paymentEx + $paymentVat;
            $line = [
                Tag::Product => $this->helper->t('payment_costs'),
                Tag::Quantity => 1,
                Tag::UnitPrice => $paymentEx,
                Meta::UnitPriceInc => $paymentInc,
            ];
            $line += Creator::getVatRangeTags($paymentVat, $paymentEx);
            $line += [
                Meta::FieldsCalculated => [Meta::UnitPriceInc],
                Meta::LineType => Creator::LineType_PaymentFee,
            ];
            $invoice['customer']['invoice']['line'][] = $line;

            // Add these amounts to the invoice totals.
            // @see \Siel\Acumulus\Magento\Invoice\Source\getAvailableTotals()
            $invoice['customer']['invoice'][Meta::InvoiceAmountInc] += $paymentInc;
            $invoice['customer']['invoice'][Meta::InvoiceVatAmount] += $paymentVat;
        }
    }

    /**
     * Adds support for the sisow module.
     *
     * The sisow module allows to define a payment fee per payment method. If a
     * fee has been defined and applied to an order it is stored in the Payment
     * object.
     *
     * Unfortunately:
     * - No tax info is stored with that, so we retrieve the current tax rate
     *   and hope it was the same at the time the order for which an invoice is
     *   now being created was ordered.
     * - There's no payment object on credit memos, so we can't support that.
     *
     * @see https://www.sisow.nl/implementatie-plugin
     *
     * @param array $invoice
     * @param \Siel\Acumulus\Invoice\Source $invoiceSource
     */
    protected function supportSisow(array &$invoice, Source $invoiceSource)
    {
        if ($invoiceSource->getType() === Source::Order) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $invoiceSource->getSource();
            $payment = $order->getPayment();
            $additionalInfo = $payment->getAdditionalInformation();
            if (array_key_exists('sisow', $additionalInfo) && (float) $additionalInfo['sisow'] != 0.0) {
                $paymentEx = (float) $additionalInfo['sisow'];

                // Get vat.
                // @todo: $order->getCustomerClassId() can that be set?
                /** @noinspection PhpUndefinedMethodInspection */
                $request = $this->taxCalculation->getRateRequest(
                    $order->getShippingAddress(),
                    $order->getBillingAddress(),
                    $order->getCustomerClassId(),
                    null,
                    $order->getCustomerId()
                );
                /** @noinspection PhpUndefinedMethodInspection */
                $taxClass = $this->scopeConfig->getValue('sisow/general/feetaxclass', ScopeInterface::SCOPE_STORE);
                $request->setProductClassId($taxClass);
                $paymentVatRate = $this->taxCalculation->getRate($request);
                $paymentVat = $paymentEx * ($paymentVatRate / 100.0);

                $paymentInc = $paymentEx + $paymentVat;
                $line = [
                    Tag::Product => $this->helper->t('payment_costs'),
                    Tag::Quantity => 1,
                    Tag::UnitPrice => $paymentEx,
                    Meta::UnitPriceInc => $paymentInc,
                    Tag::VatRate => $paymentVatRate,
                    Meta::VatRateSource => Creator::VatRateSource_Exact,
                    Meta::VatAmount => $paymentVat,
                    Meta::FieldsCalculated => [Meta::UnitPriceInc, Meta::VatAmount],
                    Meta::LineType => Creator::LineType_PaymentFee,
                ];
                $invoice['customer']['invoice']['line'][] = $line;

                // Add these amounts to the invoice totals.
                // @see \Siel\Acumulus\Magento\Invoice\Creator\getInvoiceTotals()
                $invoice['customer']['invoice'][Meta::InvoiceAmountInc] += $paymentInc;
                $invoice['customer']['invoice'][Meta::InvoiceVatAmount] += $paymentVat;
            }
        }
    }

    /**
     * Adds support for the magecomp paymentfee module.
     *
     * The magecomp paymentfee module allows to add a payment fee to an order.
     * If it does so, details are stored in columns/eav attributes:
     * - base_mc_paymentfee_amount
     * - base_mc_paymentfee_tax_amount
     * - mc_paymentfee_description
     * which the module adds to orders AND creditmemos.
     *
     * Looking at their code it seems that they do add their fee and tax to the
     * totals, so code as in paycheckout and sisow support to change our invoice
     * totals are not necessary. TBC!
     *
     * @see https://magecomp.com/magento-2-payment-fee.html
     *
     * @param array $invoice
     * @param \Siel\Acumulus\Invoice\Source $invoiceSource
     */
    protected function supportMagecompPaymentfee(array &$invoice, Source $invoiceSource)
    {
        if (!Number::isZero($invoiceSource->getSource()->getBaseMcPaymentfeeAmount())) {
            $sign = $invoiceSource->getSign();
            $paymentEx = (float) $sign * $invoiceSource->getSource()->getBaseMcPaymentfeeAmount();
            $paymentVat = (float) $sign * $invoiceSource->getSource()->getBaseMcPaymentfeeTaxAmount();
            $paymentInc = $paymentEx + $paymentVat;
            $description = $invoiceSource->getSource()->getBaseMcPaymentfeeDescription();
            $invoice['customer']['invoice']['line'][] =
                $this->getPaymentFeeLine($paymentEx, $paymentInc, $paymentVat, $description);
        }
    }

    /**
     * Adds support for the Fooman surcharge payment module.
     *
     * The Fooman surcharge payment module allows to add a payment fee to an
     * order and (a refund of it to) a credit memo.
     * If it does so, details are stored in the table
     * fooman_totals_quote_address, accessed via the
     * Fooman\Totals\Model\ResourceModel\OrderTotal resource model.
     *
     * @see https://store.fooman.co.nz/magento-extension-payment-surcharge-m2.html
     *
     * @param array $invoice
     * @param \Siel\Acumulus\Invoice\Source $invoiceSource
     */
    protected function supportFoomanSurchargePayment(array &$invoice, Source $invoiceSource)
    {
        // Is the module enabled?
        if (!class_exists('Fooman\Totals\Model\OrderTotal')) {
            return;
        }

        // Get the Fooman total object for the invoice source.
        /** @var \Fooman\Totals\Model\OrderTotal $invoiceTotal */
        if ($invoiceSource->getType() === Source::Order) {
            /** @noinspection PhpFullyQualifiedNameUsageInspection */
            $invoiceTotal = ObjectManager::getInstance()->create(\Fooman\Totals\Model\OrderTotal::class);
            $field = 'order_id';
        } else { //if ($invoiceSource->getType() === Source::CreditNote)
            /** @noinspection PhpFullyQualifiedNameUsageInspection */
            $invoiceTotal = ObjectManager::getInstance()->create(\Fooman\Totals\Model\CreditmemoTotal::class);
            $field = 'creditmemo_id';
        }
        $invoiceTotal->getResource()->load($invoiceTotal, $invoiceSource->getId(), $field);

        // Does the total object exist and does it contain a non-zero amount?
        if (!Number::isZero($invoiceTotal->getBaseAmount())) {
            $sign = $invoiceSource->getSign();
            $paymentEx = (float) $sign * $invoiceTotal->getBaseAmount();
            $paymentVat = (float) $sign * $invoiceTotal->getBaseTaxAmount();
            $paymentInc = $paymentEx + $paymentVat;
            $description = $invoiceTotal->getLabel();
            $invoice['customer']['invoice']['line'][] =
                $this->getPaymentFeeLine($paymentEx, $paymentInc, $paymentVat, $description);
        }
    }

    /**
     * Creates a payment fee line with the given details.
     *
     * @param float $paymentEx
     * @param float $paymentInc
     * @param float $paymentVat
     * @param string $description
     *
     * @return array
     */
    protected function getPaymentFeeLine($paymentEx, $paymentInc, $paymentVat, $description)
    {
        $line = [
            Tag::Product => $description ?: $this->helper->t('payment_costs'),
            Tag::Quantity => 1,
            Tag::UnitPrice => $paymentEx,
            Meta::UnitPriceInc => $paymentInc,
        ];
        $line += Creator::getVatRangeTags($paymentVat, $paymentEx);
        $line += [
            Meta::FieldsCalculated => [Meta::UnitPriceInc],
            Meta::LineType => Creator::LineType_PaymentFee,
        ];

        return $line;
    }
}
