<?php

declare(strict_types=1);

namespace Siel\AcumulusMa2\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Model\Calculation;
use Siel\Acumulus\Data\DataType;
use Siel\Acumulus\Data\Invoice;
use Siel\Acumulus\Data\Line;
use Siel\Acumulus\Data\LineType;
use Siel\Acumulus\Data\VatRateSource;
use Siel\Acumulus\Helpers\Number;
use Siel\Acumulus\Invoice\Source;
use Siel\Acumulus\Magento\Collectors\LineCollector;
use Siel\Acumulus\Meta;
use Siel\AcumulusMa2\Helper\Data;

use function array_key_exists;

/**
 * Siel Acumulus invoice collect after observer reacts on our own "invoice collect after"
 * event to add support for specific modules that we do not want in our base
 * code.
 *
 * Modules supported:
 * - Pay Checkout
 * - Sisow
 * - Magecomp Payment Fee
 * - Fooman Surcharge
 *
 * @noinspection PhpUnused  Observers are instantiated by the event handler
 */
class AcumulusInvoiceCollectAfter implements ObserverInterface
{
    protected ScopeConfigInterface $scopeConfig;
    protected Calculation $taxCalculation;
    private Data $helper;

    public function __construct(ScopeConfigInterface $scopeConfig, Calculation $taxCalculation, Data $helper)
    {
        $this->scopeConfig = $scopeConfig;
        $this->taxCalculation = $taxCalculation;
        $this->helper = $helper;
    }

    /**
     * Processes the event triggered before an invoice will be sent to Acumulus.
     *
     * The EventObserver contains the data properties as specified by
     * {@see \Siel\Acumulus\Magento\Helpers\Event::triggerInvoiceCollectAfter()}, being:
     * {@see \Siel\Acumulus\Data\Invoice} invoice
     *   The invoice in Acumulus format after being "collected", but before being
     *   "completed".
     * {@see \Siel\Acumulus\Invoice\Source} invoiceSource
     *   Wrapper around the original Magento order or refund for which the
     *   invoice has been created.
     * {@see \Siel\Acumulus\Invoice\Result} localResult
     *   Any local error or warning messages that were created locally.
     */
    public function execute(Observer $observer): void
    {
        /** @var \Siel\Acumulus\Data\Invoice $invoice */
        $invoice = $observer->getDataByKey('invoice');
        /** @var \Siel\Acumulus\Invoice\Source $invoiceSource */
        $invoiceSource = $observer->getDataByKey('invoiceSource');

        $this->supportPaycheckout($invoice, $invoiceSource);
        $this->supportSisow($invoice, $invoiceSource);
        $this->supportMagecompPaymentfee($invoice, $invoiceSource);
        $this->supportFoomanSurchargePayment($invoice, $invoiceSource);
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
     */
    protected function supportPaycheckout(Invoice $invoice, Source $invoiceSource): void
    {
        $basePayCheckoutSurchargeAmount = $invoiceSource->getShopObject()->getBasePaycheckoutSurchargeAmount();
        if (!empty($basePayCheckoutSurchargeAmount) && !Number::isZero($basePayCheckoutSurchargeAmount)) {
            $sign = $invoiceSource->getSign();
            $paymentEx = $sign * $invoiceSource->getShopObject()->getBasePaycheckoutSurchargeAmount();
            $paymentVat = $sign * $invoiceSource->getShopObject()->getBasePaycheckoutSurchargeTaxAmount();
            $paymentInc = $paymentEx + $paymentVat;

            $line = $this->createLine();
            $line->product = $this->helper->t('payment_costs');
            $line->quantity = 1;
            $line->unitPrice = $paymentEx;
            $line->metadataSet(Meta::UnitPriceInc, $paymentInc);
            LineCollector::addVatRangeTags($line, $paymentVat, $paymentEx);
            $line->metadataAdd(Meta::FieldsCalculated, Meta::UnitPriceInc);

            $invoice->addLine($line);
            $this->addToInvoiceTotals($invoice, $paymentInc, $paymentVat);
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
     */
    protected function supportSisow(Invoice $invoice, Source $invoiceSource): void
    {
        if ($invoiceSource->getType() === Source::Order) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $invoiceSource->getShopObject();
            $payment = $order->getPayment();
            $additionalInfo = $payment->getAdditionalInformation();
            if (array_key_exists('sisow', $additionalInfo) && !Number::isZero($additionalInfo['sisow'])) {
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
                /** @noinspection PhpUndefinedMethodInspection */
                $request->setProductClassId($taxClass);
                $paymentVatRate = $this->taxCalculation->getRate($request);
                $paymentVat = $paymentEx * ($paymentVatRate / 100.0);
                $paymentInc = $paymentEx + $paymentVat;

                $line = $this->createLine();
                $line->product = $this->helper->t('payment_costs');
                $line->quantity = 1;
                $line->unitPrice = $paymentEx;
                $line->metadataSet(Meta::UnitPriceInc, $paymentInc);
                $line->vatRate = $paymentVatRate;
                $line->metadataSet(Meta::VatRateSource, VatRateSource::Exact);
                $line->metadataSet(Meta::VatAmount, $paymentVat);
                $line->metadataAdd(Meta::FieldsCalculated, Meta::UnitPriceInc);
                $line->metadataAdd(Meta::FieldsCalculated, Meta::VatAmount);

                $invoice->addLine($line);
                $this->addToInvoiceTotals($invoice, $paymentInc, $paymentVat);
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
     * The module adds these to orders AND credit-memos.
     *
     * Looking at their code it seems that they do add their fee and tax to the
     * totals, so code as in paycheckout and sisow support to change our invoice
     * totals are not necessary. TBC!
     *
     * @see https://magecomp.com/magento-2-payment-fee.html
     */
    protected function supportMagecompPaymentfee(Invoice $invoice, Source $invoiceSource): void
    {
        $baseMcPaymentFeeAmount = $invoiceSource->getShopObject()->getBaseMcPaymentfeeAmount();
        if (!empty($baseMcPaymentFeeAmount) && !Number::isZero($baseMcPaymentFeeAmount)) {
            $sign = $invoiceSource->getSign();
            $paymentEx = $sign * $invoiceSource->getShopObject()->getBaseMcPaymentfeeAmount();
            $paymentVat = $sign * $invoiceSource->getShopObject()->getBaseMcPaymentfeeTaxAmount();
            $paymentInc = $paymentEx + $paymentVat;
            $description = $invoiceSource->getShopObject()->getBaseMcPaymentfeeDescription();

            $invoice->addLine($this->getPaymentFeeLine($paymentEx, $paymentInc, $paymentVat, $description));
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
     */
    protected function supportFoomanSurchargePayment(Invoice $invoice, Source $invoiceSource): void
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
        $baseAmount = $invoiceTotal->getBaseAmount();
        if (!empty($baseAmount) && !Number::isZero($baseAmount)) {
            $sign = $invoiceSource->getSign();
            $paymentEx = $sign * $invoiceTotal->getBaseAmount();
            $paymentVat = $sign * $invoiceTotal->getBaseTaxAmount();
            $paymentInc = $paymentEx + $paymentVat;
            $description = $invoiceTotal->getLabel();

            $invoice->addLine($this->getPaymentFeeLine($paymentEx, $paymentInc, $paymentVat, $description));
        }
    }

    /**
     * Creates a payment fee line with the given details.
     */
    protected function getPaymentFeeLine(float $paymentEx, float $paymentInc, float $paymentVat, string $description): Line
    {
        $line = $this->createLine();
        $line->product = $description ?: $this->helper->t('payment_costs');
        $line->quantity = 1;
        $line->unitPrice = $paymentEx;
        $line->metadataSet(Meta::UnitPriceInc, $paymentInc);
        LineCollector::addVatRangeTags($line, $paymentVat, $paymentEx);
        $line->metadataAdd(Meta::FieldsCalculated, Meta::UnitPriceInc);

        return $line;
    }

    private function createLine(): Line
    {
        /** @var Line $line */
        $line = $this->helper->getAcumulusContainer()->createAcumulusObject(DataType::Line);
        $line->metadataSet(Meta::SubType, LineType::PaymentFee);
        return $line;
    }

    /**
     * Adds the amounts to the {@see \Siel\Acumulus\Magento\Invoice\Source::getTotals() invoice totals}.
     */
    private function addToInvoiceTotals(Invoice $invoice, float|int $paymentInc, float|int $paymentVat): void
    {
        $invoice->metadataSet(Meta::InvoiceAmountInc, $invoice->metadataGet(Meta::InvoiceAmountInc) + $paymentInc);
        $invoice->metadataSet(Meta::InvoiceVatAmount, $invoice->metadataGet(Meta::InvoiceAmountInc) + $paymentVat);
    }
}
