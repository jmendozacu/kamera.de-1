<?php

class Themevast_Brandlogo_Model_Brandlogo extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('brandlogo/brandlogo');
    }
}