<?php
/**
 * This file was generated by the ConvertToLegacy class in bronto-legacy.
 * The purpose of the conversion was to maintain PSR-0 compliance while
 * the main development focuses on modern styles found in PSR-4.
 *
 * For the original:
 * @see src/Bronto/Magento/Connector/Event/QueuableSource.php
 */

class Brontosoftware_Magento_Connector_Event_QueuableSource implements Brontosoftware_Magento_Connector_Event_SourceInterface
{
    protected $_source;
    protected $_context;

    /**
     * @param Brontosoftware_Magento_Connector_Event_SourceInterface $source
     * @param Brontosoftware_Magento_Connector_Event_ContextProviderInterface $context
     */
    public function __construct(
        Brontosoftware_Magento_Connector_Event_SourceInterface $source,
        Brontosoftware_Magento_Connector_Event_ContextProviderInterface $context = null)
    {
        $this->_source = $source;
        $this->_context = $context;
    }

    /**
     * @see parent
     */
    public function action($object)
    {
        return $this->_source->action($object);
    }

    /**
     * @see parent
     */
    public function getEventType()
    {
        return $this->_source->getEventType();
    }

    /**
     * @see parent
     */
    public function transform($object)
    {
        if (!is_null($this->_context)) {
            $queue = array(
                'id' => $object->getId(),
                'storeId' => $object->getStoreId(),
                'area' => 'frontend'
            );
            $context = $this->_context->create($object);
            return array(
                'context' => array(
                    'event' => array(
                        $this->getEventType() => $queue + $context
                    )
                )
            );
        } else {
            return $this->_source->transform($object);
        }
    }
}
