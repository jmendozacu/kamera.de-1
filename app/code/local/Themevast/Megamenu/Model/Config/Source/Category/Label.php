<?php
class Themevast_Megamenu_Model_Config_Source_Category_Label extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{	
	public function getAllOptions()
	{
        return array(
            array('value'=>'', 'label'=>Mage::helper('adminhtml')->__('None')),
            array('value'=>'hot', 'label'=>Mage::helper('adminhtml')->__('Hot')),
            array('value'=>'new', 'label'=>Mage::helper('adminhtml')->__('New')),
        );
    }
}
