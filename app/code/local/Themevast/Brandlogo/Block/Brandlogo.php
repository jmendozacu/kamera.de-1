<?php
class Themevast_Brandlogo_Block_Brandlogo extends Mage_Core_Block_Template
{
	public function getBrandlogo()
    {
	    $store = Mage::app()->getStore()->getStoreId();
	    $brands = Mage::getModel('brandlogo/brandlogo')
					->getCollection()
					->addFieldToFilter('stores',array(array('like' => '%0%'),array('like' => "%$store%")))
					->addFieldToFilter('status', 1);
	    return $brands;			
    }
	public function getConfig($cfg) 
	{
		$config = Mage::getStoreConfig('brandlogo');
		if (isset($config['brandlogo'][$cfg]) ) {
			$value = $config['brandlogo'][$cfg];
			return $value;
		}
	}
}

