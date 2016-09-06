<?php

class Themevast_Brandlogo_Block_Adminhtml_Brandlogo_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('brandlogo_form', array('legend'=>Mage::helper('brandlogo')->__('Item information')));
		
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('brandlogo')->__('Title'),
          'required'  => false,
          'name'      => 'title',
      ));
	  
  	  $fieldset->addField('link', 'text', array(
            'label'     => Mage::helper('brandlogo')->__('Link'),
            'required'  => false,
            'name'      => 'link',
        ));

        $fieldset->addField('image', 'image', array(
            'label'     => Mage::helper('brandlogo')->__('Image'),
            'required'  => true,
            'name'      => 'image',
  	  ));

      $fieldset->addField('description', 'editor', array(
          'name'      => 'description',
          'label'     => Mage::helper('brandlogo')->__('Description'),
          'title'     => Mage::helper('brandlogo')->__('Description'),
          'style'     => 'width:275px; height:200px;',
          'wysiwyg'   => false,
          'required'  => false,
      ));

      //if (!Mage::app()->isSingleStoreMode()) {
          $fieldset->addField('stores', 'multiselect', array(
              'name' => 'stores[]',
              'label' => $this->__('Store View'),
              'required' => TRUE,
              'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(FALSE, TRUE),
          ));
      //}

      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('brandlogo')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('brandlogo')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('brandlogo')->__('Disabled'),
              ),
          ),
      ));

     
      if ( Mage::getSingleton('adminhtml/session')->getBrandlogoData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getBrandlogoData());
          Mage::getSingleton('adminhtml/session')->setBrandlogoData(null);
      } elseif ( Mage::registry('brandlogo_data') ) {
          $form->setValues(Mage::registry('brandlogo_data')->getData());
      }
      return parent::_prepareForm();
  }
}

