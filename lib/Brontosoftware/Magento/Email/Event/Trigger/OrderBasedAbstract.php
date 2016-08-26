<?php
/**
 * This file was generated by the ConvertToLegacy class in bronto-legacy.
 * The purpose of the conversion was to maintain PSR-0 compliance while
 * the main development focuses on modern styles found in PSR-4.
 *
 * For the original:
 * @see src/Bronto/Magento/Email/Event/Trigger/OrderBasedAbstract.php
 */

abstract class Brontosoftware_Magento_Email_Event_Trigger_OrderBasedAbstract extends Brontosoftware_Magento_Email_Event_Trigger_SourceAbstract
{
    const XML_PATH_PRICE_DISPLAY = 'tax/sales_display/price';
    const XML_PATH_SUBTOTAL_DISPLAY = 'tax/sales_display/subtotal';

    protected $_addressRender;

    /**
     * @param Brontosoftware_Magento_Email_SettingsInterface $settings
     * @param Brontosoftware_Magento_Core_Directory_CurrencyManagerInterface $currencies
     * @param Brontosoftware_Magento_Email_TriggerInterface $trigger
     * @param Brontosoftware_Magento_Order_SettingsInterface $helper
     * @param Brontosoftware_Magento_Core_Config_ScopedInterface $config
     * @param Brontosoftware_Magento_Core_Sales_AddressRenderInterface $addressRender
     * @param array $message
     */
    public function __construct(
        Brontosoftware_Magento_Email_SettingsInterface $settings,
        Brontosoftware_Magento_Core_Directory_CurrencyManagerInterface $currencies,
        Brontosoftware_Magento_Email_TriggerInterface $trigger,
        Brontosoftware_Magento_Order_SettingsInterface $helper,
        Brontosoftware_Magento_Core_Config_ScopedInterface $config,
        Brontosoftware_Magento_Core_Sales_AddressRenderInterface $addressRender,
        array $message
    ) {
        parent::__construct(
            $settings,
            $currencies,
            $trigger,
            $helper,
            $config,
            $message);
        $this->_addressRender = $addressRender;
    }

    /**
     * Gets standard API fields for order based emails
     *
     * @param mixed $order
     * @param mixed $store
     * @return array
     */
    protected function _createOrderFields($order, $store)
    {
        $subtotalDisplay = (int)$this->_config->getValue(self::XML_PATH_SUBTOTAL_DISPLAY, 'store', $store);
        $this->_setCurrency($order->getOrderCurrencyCode());
        $subtotal = $this->_formatPrice($order->getSubtotal());
        $fields = array();
        $fields[] = $this->_createField('subtotal', $subtotal);
        $fields[] = $this->_createField('subtotalExclTax', $subtotal);
        if ($subtotalDisplay != 1) {
            $fields[] = $this->_createField('subtotalInclTax', $this->_formatPrice($order->getSubtotalInclTax()));
        } else {
            $fields[] = $this->_createField('subtotalInclTax', $subtotal);
        }

        $shipAddress = 'N/A';
        $shipDescription = 'N/A';
        if ($order->getIsNotVirtual()) {
            $shipAddress = $this->_addressRender->format($order->getShippingAddress(), 'html');
            $shipDescription = $order->getShippingDescription();
        }
        $customerName = $order->getCustomerIsGuest() ?
            $order->getBillingAddress()->getName() :
            $order->getCustomerName();

        $fields[] = $this->_createField('grandTotal', $this->_formatPrice($order->getGrandTotal()));
        $fields[] = $this->_createField('orderIncrementId', $order->getIncrementId());
        $fields[] = $this->_createField('orderCreatedAt', $order->getCreatedAtFormated('long'));
        $fields[] = $this->_createField('orderBillingAddress', $this->_addressRender->format($order->getBillingAddress(), 'html'));
        $fields[] = $this->_createField('orderShippingAddress', $shipAddress);
        $fields[] = $this->_createField('orderShippingDesc', $shipDescription);
        $fields[] = $this->_createField('orderCustomerName', $customerName);
        $fields[] = $this->_createField('orderStatusLabel', $order->getStatusLabel());
        return $fields;
    }
}
