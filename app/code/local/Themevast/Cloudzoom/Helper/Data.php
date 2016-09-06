<?php
class Themevast_Cloudzoom_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getGeneralCfg($cfg=null) 
    {
        $config = Mage::getStoreConfig('cloudzoom/general');
        if(isset($config[$cfg])) return $config[$cfg];
        return $config;
    }	
}