<?php
class Themevast_Randomslider_Block_Randomslider extends Mage_Catalog_Block_Product_Abstract
{

	protected $_config = array();

	protected function _construct()
	{
		if(!$this->_config) $this->_config = Mage::getStoreConfig('randomslider/general'); 
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
        $collection = Mage::getResourceModel('catalog/product_collection')
                            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
							->addFieldToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
                            ->addMinimalPrice()
                            ->addTaxPercents()
                            ->addStoreFilter();
        $collection->getSelect()->order('rand()');
        $collection->setPageSize($this->getConfig('qty'))->setCurPage(1);		
        Mage::getModel('review/review')->appendSummary($collection);                       
        return $collection;
    }

    /**
     * get collection of spicified category products
     *
     * @var Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function getCategoryProductCollection($categoryId, $maxPrice = false)
    {
        /** @var Mage_Catalog_Model_Category $category */
        $category = Mage::getModel('catalog/category')->setStoreId($store_id)->load($categoryId);

        /** @var Mage_Catalog_Model_Resource_Product_Collection $c */
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addFieldToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
            ->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED))
            ->addTaxPercents()
            ->addCategoryFilter($category);


        if ($maxPrice) {
            $collection->addFieldToFilter('price', array('lt' => $maxPrice));
            $collection->addFieldToFilter('price', array('gt' => 0));
        }

        $collection->getSelect()->order('rand()');
        $collection->setPageSize($this->getConfig('qty'))->setCurPage(1);

        return $collection;
    }

    /**
     * get collection of top sellable products
     *
     * @var Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function getTopSellerProductCollection()
    {
        $collection = Mage::getResourceModel('reports/product_collection')
            ->addOrderedQty()
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addFieldToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
            ->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED))
            ->addTaxPercents()
            ->setOrder('ordered_qty', 'desc');
        $collection->getSelect()->order('rand()');
        $collection->setPageSize($this->getConfig('qty'))->setCurPage(1);
        return $collection;
    }

    /**
     * get collection latest products
     *
     * @var Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function getLatestProductCollection()
    {
        $collection = Mage::getResourceModel('reports/product_collection')
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addFieldToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
            ->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED))
            ->addTaxPercents()
            ->addAttributeToSort('created_at', 'desc');
        $collection->setPageSize($this->getConfig('qty'))->setCurPage(1);
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

