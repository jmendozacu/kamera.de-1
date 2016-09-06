<?php

class Themevast_Megamenu_Model_Config_Source_Category_Effect
{

    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('SlideDown')),
            array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('FadeIn')),
            array('value' => 2, 'label'=>Mage::helper('adminhtml')->__('Show')),
        );
    }

}

