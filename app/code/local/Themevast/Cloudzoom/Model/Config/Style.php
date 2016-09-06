<?php
class Themevast_Cloudzoom_Model_Config_Style
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'horizontal', 'label'=>Mage::helper('adminhtml')->__('Horizontal')),
            array('value'=>'vertical', 'label'=>Mage::helper('adminhtml')->__('Vertical')),
        );
    }

}
