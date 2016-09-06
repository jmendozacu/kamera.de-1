<?php

class Themevast_Megamenu_Model_Config_Source_Category extends Varien_Object
{
 
	const PREFIX_ROOT = '+'; 	

    const REPEATER = '_';
 
    const PREFIX_END = '';
 
    protected $_options = array();
 
    /**
     * @param int $parentId
     * @param int $recursionLevel
     *
     * @return array
     */
    public function toOptionArray($parentId=null , $recursionLevel=null)
    {
        $recursionLevel = (int)$recursionLevel;
        $parentId       = (int)$parentId;
 
        $category = Mage::getModel('catalog/category');
        /* @var $category Mage_Catalog_Model_Category */
        $storeCategories = $category->getCategories($parentId, $recursionLevel, TRUE, FALSE, TRUE);
 
        foreach ($storeCategories as $node) {
            /* @var $node Varien_Data_Tree_Node */
 
            $this->_options[] = array(
 
                'label' => self::PREFIX_ROOT .$node->getName(),
                'value' => $node->getEntityId()
            );
            if ($node->hasChildren()) {
                $this->_getChildOptions($node->getChildren());
            }
 
        }
 
        return $this->_options;
    }
 
    /**
     * @param Varien_Data_Tree_Node_Collection $nodeCollection
     */
    protected function _getChildOptions(Varien_Data_Tree_Node_Collection $nodeCollection)
    {
 
        foreach ($nodeCollection as $node) {
            /* @var $node Varien_Data_Tree_Node */
            $prefix = str_repeat(self::REPEATER, $node->getLevel() * 1) . self::PREFIX_END;
 
            $this->_options[] = array(
 
                'label' => $prefix . $node->getName(),
                'value' => $node->getEntityId()
            );
 
            if ($node->hasChildren()) {
                $this->_getChildOptions($node->getChildren());
            }
        }
    }
 

}
