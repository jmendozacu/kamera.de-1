<?php

class Themevast_Megamenu_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getGeneralCfg($cfg=null) 
    {
        $config = Mage::getStoreConfig('megamenu/general');
        if(isset($config[$cfg])) return $config[$cfg];
        return $config;
    }	

}
