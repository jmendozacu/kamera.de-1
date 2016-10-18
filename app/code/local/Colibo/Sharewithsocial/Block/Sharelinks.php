<?php

class Colibo_Sharewithsocial_Block_Sharelinks extends Mage_Core_Block_Template
{
    public function getUrl()
    {
        return Mage::helper('core/url')->getCurrentUrl();
    }
}