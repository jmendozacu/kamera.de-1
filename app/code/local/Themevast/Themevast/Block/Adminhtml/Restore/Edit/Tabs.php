<?php

class Themevast_Themevast_Block_Adminhtml_Restore_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('themevast_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('themevast')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('themevast')->__('Setting Template'),
          'title'     => Mage::helper('themevast')->__('Setting Template'),
          'content'   => $this->getLayout()->createBlock('themevast/adminhtml_restore_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}

