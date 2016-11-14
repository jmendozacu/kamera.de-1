<?php
class Themevast_Randomslider_Block_Randomslider extends Mage_Catalog_Block_Product_Abstract
{

	protected $_config = array();

    private $filterCategory;
    private $filterMaxPrice;
    private $filterSpecial;


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

        if ($this->filterCategory) {
            /** @var Mage_Catalog_Model_Category $category */
            $category = Mage::getModel('catalog/category')->setStoreId(Mage::app()->getStore()->getId())->load($this->filterCategory);
            $collection->addCategoryFilter($category);
        }

        if ($this->filterMaxPrice) {
            $collection->addFieldToFilter('price', array('lt' => $this->filterMaxPrice));
            $collection->addFieldToFilter('price', array('gt' => 0));
        }

        if ($this->filterSpecial) {
            switch ($this->filterSpecial) {
                case 'top_sellable':
                    $collection->setOrder('ordered_qty', 'desc');
                    break;
                case 'latest':
                    $collection->addAttributeToSort('created_at', 'desc');
                    break;
                default:
                    break;
            }
        }

        if (!$this->filterSpecial) {
            $collection->getSelect()->order('rand()');
        }
        $collection->setPageSize($this->getConfig('qty'))->setCurPage(1);
        Mage::getModel('review/review')->appendSummary($collection);
        return $collection;
    }

    /**
     * Sets slider options
     *
     * @var string
     */
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

    /**
     * Sets category filter
     *
     * @var Themevast_Randomslider_Block_Randomslider
     */
    public function setFilterCategory($categoryId)
    {
        $this->filterCategory = $categoryId;
        return $this;
    }

    /**
     * Sets max price filter
     *
     * @var Themevast_Randomslider_Block_Randomslider
     */
    public function setFilterMaxPrice($maxPrice)
    {
        $this->filterMaxPrice = $maxPrice;
        return $this;
    }

    /**
     * Sets special filter
     *
     * @var Themevast_Randomslider_Block_Randomslider
     */
    public function setFilterSpecial($name)
    {
        $this->filterSpecial = $name;
        return $this;
    }

}

