<?php
class Themevast_Themevast_Model_System_Config_Source_Themecolor
{
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    public function getAllOptions($withEmpty = true)
    {
        $options = array(
            array('value'=>'color/home1_red.css', 'label'=>Mage::helper('adminhtml')->__('Home1 Red')),
            array('value'=>'color/home1_orange.css', 'label'=>Mage::helper('adminhtml')->__('Home1 Orange')),
            array('value'=>'color/home1_green.css', 'label'=>Mage::helper('adminhtml')->__('Home1 Green')),
            array('value'=>'color/home2_red.css', 'label'=>Mage::helper('adminhtml')->__('Home2 Red')),
            array('value'=>'color/home2_orange.css', 'label'=>Mage::helper('adminhtml')->__('Home2 Orange')),
            array('value'=>'color/home2_green.css', 'label'=>Mage::helper('adminhtml')->__('Home2 Green')),
             
        );
        $label = $options ? Mage::helper('core')->__('-- Please Select --') : Mage::helper('core')->__('-- One Color --');
        if ($withEmpty) {
            array_unshift($options, array(
                'value' => '',
                'label' => $label
            ));
        }
        return $options;
    }

}
