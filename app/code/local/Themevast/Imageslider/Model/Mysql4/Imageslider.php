<?php
class Themevast_Imageslider_Model_Mysql4_Imageslider extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('imageslider/imageslider', 'imageslider_id');
    }
}