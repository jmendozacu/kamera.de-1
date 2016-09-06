<?php

class Themevast_Relatedslider_Helper_Data extends Mage_Core_Helper_Abstract
{

    protected $_config = array();

    public function getConfig($cfg = null)
    {
        if (!$this->_config) $this->_config = Mage::getStoreConfig('relatedslider/general'); 
        if (isset($this->_config[$cfg]) ) return $this->_config[$cfg];
    }

    public function setBxslider()
    {
        $options = array(
            'auto',
            'speed',
            'controls',
            'pager',
            'maxSlides',
            'slideWidth',
        );
        $script = '';
        foreach ($options as $opt) {
            $cfg  =  $this->getConfig($opt);
            $script    .= "$opt: $cfg, ";
        }

        $options2 = array(
            'mode'=>'vertical',
        );
        foreach ($options2 as $key => $value) {
            $cfg  =  $this->getConfig($value);
            if($cfg) $script    .= "$key: '$value', ";
        }

        return $script;

    }

}
