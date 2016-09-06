<?php
class Themevast_Imageslider_Block_Adminhtml_Imageslider extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_imageslider';
    $this->_blockGroup = 'imageslider';
    $this->_headerText = Mage::helper('imageslider')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('imageslider')->__('Add Item');
    parent::__construct();
  }
}