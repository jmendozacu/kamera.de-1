<?php

class Themevast_Brandlogo_Block_Adminhtml_Brandlogo_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('brandlogo_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('brandlogo')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('brandlogo')->__('Item Information'),
          'title'     => Mage::helper('brandlogo')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('brandlogo/adminhtml_brandlogo_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}