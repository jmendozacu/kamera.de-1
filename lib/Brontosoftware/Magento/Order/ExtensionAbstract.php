<?php
/**
 * This file was generated by the ConvertToLegacy class in bronto-legacy.
 * The purpose of the conversion was to maintain PSR-0 compliance while
 * the main development focuses on modern styles found in PSR-4.
 *
 * For the original:
 * @see src/Bronto/Magento/Order/ExtensionAbstract.php
 */

abstract class Brontosoftware_Magento_Order_ExtensionAbstract extends Brontosoftware_Magento_Connector_Discovery_AdvancedExtensionAbstract implements Brontosoftware_Magento_Connector_Discovery_GroupInterface, Brontosoftware_Magento_Connector_Discovery_TransformEventInterface
{
    protected $_attributes;
    protected $_statuses;
    protected $_orderRepo;

    /**
     * @param Brontosoftware_Magento_Core_Catalog_ProductAttributeCacheInterface $attributes
     * @param Brontosoftware_Magento_Core_Sales_OrderStatusesInterface $statuses
     * @param Brontosoftware_Magento_Core_Sales_OrderCacheInterface $orderRepo
     * @param Brontosoftware_Magento_Core_App_EmulationInterface $appEmulation
     * @param Brontosoftware_Magento_Core_Store_ManagerInterface $storeManager
     * @param Brontosoftware_Magento_Connector_QueueManagerInterface $queueManager
     * @param Brontosoftware_Magento_Connector_SettingsInterface $connectorSettings
     * @param Brontosoftware_Magento_Connector_Event_HelperInterface $helper
     * @param Brontosoftware_Magento_Connector_Event_PlatformInterface $platform
     * @param Brontosoftware_Magento_Connector_Event_SourceInterface $source
     */
    public function __construct(
        Brontosoftware_Magento_Core_Catalog_ProductAttributeCacheInterface $attributes,
        Brontosoftware_Magento_Core_Sales_OrderStatusesInterface $statuses,
        Brontosoftware_Magento_Core_Sales_OrderCacheInterface $orderRepo,
        Brontosoftware_Magento_Core_App_EmulationInterface $appEmulation,
        Brontosoftware_Magento_Core_Store_ManagerInterface $storeManager,
        Brontosoftware_Magento_Connector_QueueManagerInterface $queueManager,
        Brontosoftware_Magento_Connector_SettingsInterface $connectorSettings,
        Brontosoftware_Magento_Connector_Event_HelperInterface $helper,
        Brontosoftware_Magento_Connector_Event_PlatformInterface $platform,
        Brontosoftware_Magento_Connector_Event_SourceInterface $source
    ) {
        parent::__construct(
            $appEmulation,
            $storeManager,
            $queueManager,
            $connectorSettings,
            $helper,
            $platform,
            $source);
        $this->_attributes = $attributes;
        $this->_statuses = $statuses;
        $this->_orderRepo = $orderRepo;
    }

    /**
     * @see parent
     */
    public function getSortOrder()
    {
        return 15;
    }

    /**
     * @see parent
     */
    public function getEndpointId()
    {
        return 'order';
    }

    /**
     * @see parent
     */
    public function getEndpointName()
    {
        return $this->translate('Orders');
    }

    /**
     * @see parent
     */
    public function getEndpointIcon()
    {
        return 'mage-icon-orders';
    }

    /**
     * @see parent
     */
    public function transformEvent($observer)
    {
        $data = array();
        $transform = $observer->getTransform();
        $event = $transform->getContext();
        $order = $this->_orderRepo->getById($event['id']);
        if ($order && $this->_source->action($order)) {
            if (array_key_exists('tid', $event)) {
                $order->setBrontoTid($event['tid']);
            }
            $data = $this->_source->transform($order);
        }
        $transform->setOrder($data);
    }

    /**
     * @see parent
     */
    public function gatherEndpoints($observer)
    {
        $observer->getDiscovery()->addGroupHelper($this);
    }

    /**
     * @see parent
     */
    public function endpointInfo($observer)
    {
        $observer->getEndpoint()->addExtension(array(
            'id' => 'settings',
            'name' => $this->translate('Settings'),
            'fields' => array(
                array(
                    'id' => 'enabled',
                    'name' => $this->translate('Enabled'),
                    'type' => 'boolean',
                    'required' => true,
                    'typeProperties' => array( 'default' => false )
                ),
                array(
                    'id' => 'status',
                    'name' => $this->translate('Bronto Order Status'),
                    'type' => 'select',
                    'required' => true,
                    'requiredFeatures' => array( 'enableOrderService' => true ),
                    'typeProperties' => array(
                        'default' => 'PROCESSED',
                        'options' => array(
                            array(
                                'id' => 'PENDING',
                                'name' => $this->translate('Pending'),
                            ),
                            array(
                                'id' => 'PROCESSED',
                                'name' => $this->translate('Processed'),
                            )
                        )
                    ),
                ),
                array(
                    'id' => 'import_status',
                    'name' => $this->translate('Orders to Import'),
                    'type' => 'select',
                    'required' => true,
                    'typeProperties' => array(
                        'default' => array('pending', 'complete', 'processing'),
                        'options' => $this->_statuses->getOptionArray(),
                        'multiple' => true
                    ),
                ),
                array(
                    'id' => 'delete_status',
                    'name' => $this->translate('Orders to Delete'),
                    'type' => 'select',
                    'required' => true,
                    'typeProperties' => array(
                        'default' => array('holded', 'canceled', 'closed'),
                        'options' => $this->_statuses->getOptionArray(),
                        'multiple' => true
                    ),
                ),
                array(
                    'id' => 'price',
                    'name' => $this->translate('Product Price'),
                    'type' => 'select',
                    'required' => true,
                    'typeProperties' => array(
                        'default' => 'display',
                        'options' => array(
                            array( 'id' => 'display', 'name' => $this->translate('Display') ),
                            array( 'id' => 'base', 'name' => $this->translate('Base') )
                        )
                    )
                ),
                array(
                    'id' => 'description',
                    'name' => $this->translate('Product Description'),
                    'type' => 'select',
                    'required' => true,
                    'typeProperties' => array(
                        'default' => 'description',
                        'options' => array(
                            array(
                                'id' => 'short_description',
                                'name' => $this->translate('Short Description'),
                            ),
                            array(
                                'id' => 'description',
                                'name' => $this->translate('Description'),
                            )
                        )
                    )
                ),
                array(
                    'id' => 'include_discount',
                    'name' => $this->translate('Include Discount'),
                    'type' => 'boolean',
                    'requiredFeatures' => array( 'enableOrderService' => false ),
                    'typeProperties' => array(
                        'default' => false
                    )
                ),
                array(
                    'id' => 'include_tax',
                    'name' => $this->translate('Include Tax'),
                    'type' => 'boolean',
                    'requiredFeatures' => array( 'enableOrderService' => false ),
                    'typeProperties' => array(
                        'default' => false
                    )
                ),
                array(
                    'id' => 'include_shipping',
                    'name' => $this->translate('Include Shipping'),
                    'type' => 'boolean',
                    'requiredFeatures' => array( 'enableOrderService' => false ),
                    'typeProperties' => array(
                        'default' => false
                    )
                ),
                array(
                    'id' => 'image_type',
                    'name' => $this->translate('Image View Type'),
                    'type' => 'select',
                    'required' => true,
                    'typeProperties' => array(
                        'default' => 'image',
                        'options' => array(
                            array(
                                'id' => 'image',
                                'name' => $this->translate('Base Image'),
                            ),
                            array(
                                'id' => 'small_image',
                                'name' => $this->translate('Small Image'),
                            ),
                            array(
                                'id' => 'thumbnail',
                                'name' => $this->translate('Thumbnail')
                            )
                        )
                    )
                ),
                array(
                    'id' => 'other_field',
                    'name' => $this->translate('Other Field'),
                    'type' => 'select',
                    'requiredFeatures' => array( 'enableOrderService' => true ),
                    'typeProperties' => array(
                        'options' => $this->_attributes->getOptionArray()
                    )
                )
            )
        ));
    }

    /**
     * @see parent
     */
    public function advancedAdditional($observer)
    {
        $observer->getEndpoint()->addOptionToScript('test', 'jobName', array(
            'id' => 'test_' . $this->getEndpointId() . '_new',
            'name' => $this->translate('Order')
        ));

        $observer->getEndpoint()->addFieldToScript('test', array(
            'id' => 'customerOrderId',
            'name' => $this->translate('Order ID'),
            'type' => 'text',
            'position' => 10,
            'depends' => array(
                array(
                    'id' => 'jobName',
                    'values' => array('test_' . $this->getEndpointId() . '_new')
                )
            )
        ));

        $observer->getEndpoint()->addOptionToScript('historical', 'jobName', array(
            'id' => $this->getEndpointId(),
            'name' => $this->getEndpointName()
        ));

        $observer->getEndpoint()->addFieldToScript('historical', array(
            'id' => 'maintainConversions',
            'name' => $this->translate('Maintain Conversions'),
            'type' => 'boolean',
            'required' => true,
            'position' => 5,
            'typeProperties' => array( 'default' => false ),
            'requiredFeatures' => array( 'enableOrderService' => true ),
            'depends' => array(
                array(
                    'id' => 'jobName',
                    'values' => array($this->getEndpointId())
                )
            )
        ));

        $observer->getEndpoint()->addOptionToScript('event', 'moduleSettings', array(
            'id' => $this->getEndpointId(),
            'name' => $this->getEndpointName()
        ));

        if ($observer->getRegistration()->getEnvironment() == 'SANDBOX') {
            $observer->getEndpoint()->addFieldToScript('historical', array(
                'id' => 'performDelete',
                'name' => $this->translate('Delete Orders from Bronto'),
                'type' => 'boolean',
                'position' => 6,
                'required' => true,
                'typeProperties' => array( 'default' => false ),
                'depends' => array(
                    array( 'id' => 'jobName', 'values' => array($this->getEndpointId()) )
                )
            ));
        }
    }

    /**
     * @see parent
     */
    protected function _historicalAction($data, $object)
    {
        $action = parent::_historicalAction($data, $object);
        if (array_key_exists('options', $data)) {
            if (array_key_exists('maintainConversions', $data['options'])) {
                $maintainConversions = $data['options']['maintainConversions'];
                if ($maintainConversions && $action == 'add') {
                    $action = 'replace';
                }
            }
            if (array_key_exists('performDelete', $data['options'])) {
                if ($data['options']['performDelete']) {
                    $action = 'delete';
                }
            }
        }
        return $action;
    }

    /**
     * @see parent
     */
    protected function _sendTest($registration, $data)
    {
        $orders = array();
        if (array_key_exists('customerOrderId', $data)) {
            $customerOrderId = $data['customerOrderId'];
            $orders = $this->_attachScopeFilter($data, $this->_collection())
                ->addFieldToFilter('increment_id', array('eq' => $customerOrderId));
        }
        return $orders;
    }

    /**
     * @see parent
     */
    protected function _sendHistorical($registration, $data)
    {
        $orders = $this->_collection();
        if (array_key_exists('startTime', $data)) {
            $startTime = $data['startTime'];
            if ($startTime) {
                $orders->addFieldToFilter('created_at', array('gt' => $startTime));
            }
        }
        if (array_key_exists('endTime', $data)) {
            $endTime = $data['endTime'];
            if ($endTime) {
                $orders->addFieldToFilter('created_at', array('lt' => $endTime));
            }
        }
        return $this->_attachScopeFilter($data['options'], $orders);
    }

    /**
     * Attaches store scope filters as needed
     *
     * @param $data
     * @param mixed $orders
     * @return mixed
     */
    protected function _attachScopeFilter($data, $orders)
    {
        list($scopeName, $scopeId) = explode('.', $data['scopeId']);
        switch ($scopeName) {
            case 'website':
                $storeIds = array();
                $website = $this->_storeManager->getWebsite($scopeId);
                foreach ($website->getStores() as $store) {
                    $storeIds[] = $store->getId();
                }
                return $orders->addFieldToFilter('store_id', array('in' => $storeIds));
            case 'store':
                return $orders->addFieldToFilter('store_id', array('eq' => $scopeId));
        }
        return $orders;
    }

    /**
     * Gets a new order collection to filter down
     *
     * @return Iterator
     */
    abstract protected function _collection();
}
