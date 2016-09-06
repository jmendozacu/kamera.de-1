<?php
class Themevast_Bestsellerslider_Block_Bestsellerslider extends Mage_Catalog_Block_Product_Abstract
{

	protected $_config = array();

	protected function _construct()
	{
		if(!$this->_config) $this->_config = Mage::getStoreConfig('bestsellerslider/general'); 
	}

	public function getConfig($cfg = null)
	{
		if (isset($this->_config[$cfg]) ) return $this->_config[$cfg];
		return ; // return $this->_config;
	}

	public function getColumnCount()
	{
		
		$slide = $this->getConfig('slide');
		$rows  = $this->getConfig('rows');
		if($slide && $rows >1) $column = $rows;
		else $column = $this->getConfig('qty');
		return $column;
	}

    protected function getProductCollection()
    {
    	$storeId = Mage::app()->getStore()->getId();
    	$attributes = Mage::getSingleton('catalog/config')->getProductAttributes();
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table_prefixx = Mage::getConfig()->getTablePrefix(); 
        $res = $write->query("select max(qo) as des_qty,`product_id`,`parent_item_id` FROM (select sum(`qty_ordered`) AS qo,`product_id`,created_at,store_id,`parent_item_id` from ".$table_prefixx."sales_flat_order_item Group By `product_id`) AS t1 where  parent_item_id is null Group By `product_id` order By des_qty desc"); 
        
        while ($row = $res->fetch()) $maxQty[]=$row['product_id'];
        $collection = Mage::getModel('catalog/product')
                        ->getCollection()
                        ->addAttributeToSelect($attributes)
                        ->addAttributeToFilter('entity_id', array('in' => $maxQty))
						->addStoreFilter($storeId);;

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
        
        $collection->setPageSize($this->getConfig('qty'))->setCurPage(1);
        Mage::getModel('review/review')->appendSummary($collection);     
        //var_dump($collection->getAllIds());                   
        return $collection;
    }

    public function setBxslider()
    {
  		$options = array(
  			'auto',
  			'speed',
  			'controls',
  			'pager',
  			'maxSlides',
  			'slideWidth',
  		);
  		$script = '';
  		foreach ($options as $opt) {
  			$cfg  =  $this->getConfig($opt);
  			$script    .= "$opt: $cfg, ";
  		}

  		$options2 = array(
  			'mode'=>'vertical',
  		);
  		foreach ($options2 as $key => $value) {
  			$cfg  =  $this->getConfig($value);
  			if($cfg) $script    .= "$key: '$value', ";
  		}

        return $script;

    }

}

