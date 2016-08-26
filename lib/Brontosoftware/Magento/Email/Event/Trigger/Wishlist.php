<?php
/**
 * This file was generated by the ConvertToLegacy class in bronto-legacy.
 * The purpose of the conversion was to maintain PSR-0 compliance while
 * the main development focuses on modern styles found in PSR-4.
 *
 * For the original:
 * @see src/Bronto/Magento/Email/Event/Trigger/Wishlist.php
 */

class Brontosoftware_Magento_Email_Event_Trigger_Wishlist extends Brontosoftware_Magento_Email_Event_Trigger_SourceAbstract
{
    protected $_integration;
    protected $_customerRepo;
    protected $_productRepo;
    protected $_logger;

    /**
     * @param Brontosoftware_Magento_Core_Log_LoggerInterface $logger
     * @param Brontosoftware_Magento_Integration_CartSettingsInterface $integration
     * @param Brontosoftware_Magento_Core_Customer_CacheInterface $customerRepo
     * @param Brontosoftware_Magento_Core_Catalog_ProductCacheInterface $productRepo
     * @param Brontosoftware_Magento_Email_SettingsInterface $settings
     * @param Brontosoftware_Magento_Core_Directory_CurrencyManagerInterface $currencies
     * @param Brontosoftware_Magento_Email_TriggerInterface $trigger
     * @param Brontosoftware_Magento_Order_SettingsInterface $helper
     * @param Brontosoftware_Magento_Core_Config_ScopedInterface $config
     * @param array $message
     */
    public function __construct(
        Brontosoftware_Magento_Core_Log_LoggerInterface $logger,
        Brontosoftware_Magento_Integration_CartSettingsInterface $integration,
        Brontosoftware_Magento_Core_Customer_CacheInterface $customerRepo,
        Brontosoftware_Magento_Core_Catalog_ProductCacheInterface $productRepo,
        Brontosoftware_Magento_Email_SettingsInterface $settings,
        Brontosoftware_Magento_Core_Directory_CurrencyManagerInterface $currencies,
        Brontosoftware_Magento_Email_TriggerInterface $trigger,
        Brontosoftware_Magento_Order_SettingsInterface $helper,
        Brontosoftware_Magento_Core_Config_ScopedInterface $config,
        array $message
    ) {
        parent::__construct($settings, $currencies, $trigger, $helper, $config, $message);
        $this->_integration = $integration;
        $this->_customerRepo = $customerRepo;
        $this->_logger = $logger;
        $this->_productRepo = $productRepo;
    }

    /**
     * @see parent
     */
    public function transform($wishlist)
    {
        $customer = $this->_customerRepo->getById($wishlist->getCustomerId());
        $store = $customer->getStore();
        $delivery = $this->_createDelivery($customer->getEmail(), $store,
            !isset($this->_message['previousMessage']) ?
                $this->_message['sendType'] :
                'triggered');
        $index = 1;
        $fields = $this->_extraFields(array('wishlist' => $wishlist));
        $this->_setCurrency($store->getDefaultCurrencyCode());
        foreach ($wishlist->getItemCollection() as $item) {
            try {
                $product = $this->_productRepo->getById($item->getProductId(), $store->getId());
                $fields[] = $this->_createField("productId_{$index}", $product->getId());
                $fields[] = $this->_createField("productName_{$index}", $product->getName());
                $fields[] = $this->_createField("productSku_{$index}", $product->getSku());
                $fields[] = $this->_createField("productDescription_{$index}", $this->_productRepo->getDescription($product, $this->_helper->getDescriptionAttribute('store', $store)));
                $fields[] = $this->_createField("productUrl_{$index}", $item->getProductUrl());
                $fields[] = $this->_createField("productQty_{$index}", $item->getQty());
                $fields[] = $this->_createField("productPrice_{$index}", $this->_formatPrice($product->getPrice()));
                $fields[] = $this->_createField("productTotal_{$index}", $this->_formatPrice($product->getPrice() * $item->getQty()));
                $fields[] = $this->_createField("productImgUrl_{$index}", $this->_productRepo->getImage($product, $this->_helper->getImageAttribute('store', $store)));
            } catch (Exception $e) {
                $this->_logger->critical($e);
            }
            $index++;
        }
        $fields[] = $this->_createField('quoteURL', $this->_integration->getRedirectUrl($wishlist->getId(), $store, 'wishlist'));
        $delivery['fields'] = $fields;
        return $delivery;
    }
}
