<?php
class Themevast_Imageslider_Block_Slider extends Mage_Core_Block_Template
{
	protected $_config = array();    
	public function getSlider()
    {
    	$resource = Mage::getSingleton('core/resource');
		$read= $resource->getConnection('core_read');
		$slideTable = $resource->getTableName('imageslider');	
		$select = $read->select()
		   ->from($slideTable,array('imageslider_id','title','link','description','image', 'effect', 'order', 'stores','status'))
           // ->where('find_in_set(0, stores) OR find_in_set(?, stores)', Mage::app()->getStore()->getId())
           ->where('find_in_set(0, stores) OR find_in_set("", stores) OR find_in_set(?, stores)', Mage::app()->getStore()->getId())
		   ->where('status=?',1)->order('order');
		$slide = $read->fetchAll($select);		
		return $slide;
    }
	public function getConfig($cfg) 
	{
		if(!$this->_config) $this->_config = Mage::getStoreConfig('imageslider/general');
		if (isset($this->_config[$cfg])) return $this->_config[$cfg];
	}
}

