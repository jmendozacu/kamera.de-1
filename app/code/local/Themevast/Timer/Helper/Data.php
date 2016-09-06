<?php
class Themevast_Timer_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_config = array();

    public function getConfig($cfg = null)
    {
        if (!$this->_config) $this->_config = Mage::getStoreConfig('timer/general'); 
        if (isset($this->_config[$cfg]) ) return $this->_config[$cfg];
        return $this->_config;
    }

    public function getTimer($product, $num)
    {
        return $this->getLayout()
        			->createBlock('timer/timer')
        			->setNum($num)
					->setProduct($product)
					->setTemplate('themevast/timer/timer.phtml')
					->toHtml();
    }

}
