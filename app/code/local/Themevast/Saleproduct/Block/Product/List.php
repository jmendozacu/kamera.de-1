<?php
class Themevast_Saleproduct_Block_Product_List extends Mage_Catalog_Block_Product_List
{
    protected function _getProductCollection()
    { 
        $todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        $collection = Mage::getResourceModel('catalog/product_collection')
                                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                                ->addAttributeToFilter('special_from_date', array('or'=> array(
                                    0 => array('date' => true, 'to' => $todayDate),
                                    1 => array('is' => new Zend_Db_Expr('null')))
                                ), 'left')
                                ->addAttributeToFilter('special_to_date', array('or'=> array(
                                    0 => array('date' => true, 'from' => $todayDate),
                                    1 => array('is' => new Zend_Db_Expr('null')))
                                ), 'left')
                                ->addAttributeToFilter(
                                    array(
                                        array('attribute' => 'special_from_date', 'is'=>new Zend_Db_Expr('not null')),
                                        array('attribute' => 'special_to_date', 'is'=>new Zend_Db_Expr('not null'))
                                        )
                                  )
                                ->addAttributeToSort('special_to_date','desc')
                                ->addTaxPercents()
                                ->addStoreFilter()
                                ->setOrder($this->getRequest()->getParam('order'), $this->getRequest()->getParam('dir')); 
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);
        $limit = (int)$this->getRequest()->getParam('limit') ? (int)$this->getRequest()->getParam('limit') : (int)$this->getToolbarBlock()->getDefaultPerPageValue();
        $collection->setPageSize($limit)->setCurPage($this->getRequest()->getParam('p'));
        Mage::getModel('review/review')->appendSummary($collection);
        //$collection->load();
        return $collection;
    }

}
