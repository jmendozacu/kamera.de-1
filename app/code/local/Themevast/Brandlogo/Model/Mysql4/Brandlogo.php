<?php

class Themevast_Brandlogo_Model_Mysql4_Brandlogo extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the brandlogo_id refers to the key field in your database table.
        $this->_init('brandlogo/brandlogo', 'brandlogo_id');
    }
}