<?php
if (!class_exists('Themevast_Blog_Block_Product_ToolbarCommon')) {
    if (Mage::helper('blog')->isMobileInstalled()) {
        class Themevast_Blog_Block_Product_ToolbarCommon extends AW_Mobile_Block_Catalog_Product_List_Toolbar
        {
        }
    } else {
        class Themevast_Blog_Block_Product_ToolbarCommon extends Mage_Catalog_Block_Product_List_Toolbar
        {
        }
    }
}