<?php
class Themevast_Imageslider_Block_Adminhtml_Imageslider_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('imageslider_form', array('legend'=>Mage::helper('imageslider')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('imageslider')->__('Title'),
          'required'  => false,
          'name'      => 'title',
      ));
	  
      $fieldset->addField('link', 'text', array(
          'label'     => Mage::helper('imageslider')->__('Link'),
          'required'  => false,
          'name'      => 'link',
      ));

      $fieldset->addField('image', 'image', array(
          'label'     => Mage::helper('imageslider')->__('Image'),
          'required'  => true,
          'name'      => 'image',
	     ));
      $fieldset->addField('description', 'editor', array(
          'name'      => 'description',
          'label'     => Mage::helper('imageslider')->__('Description'),
          'title'     => Mage::helper('imageslider')->__('Description'),
          'style'     => 'width:273px; height:200px;',
          'wysiwyg'   => false,
          'required'  => false,
      ));
      if (!Mage::app()->isSingleStoreMode()) {
          $fieldset->addField('stores', 'multiselect', array(
              'name' => 'stores[]',
              'label' => $this->__('Store View'),
              'required' => true,
              'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
          ));
      }    
      $fieldset->addField('effect', 'select', array(
            'label'     => Mage::helper('imageslider')->__('Effect Caption'),
            'name'      => 'effect',
            'values'    => array(
                array('value' => '', 'label' => Mage::helper('imageslider')->__('Select Effect')),
                array('value' => 'LeftToRight', 'label' => Mage::helper('imageslider')->__('Left To Right')),
                array('value' => 'TopToBottom', 'label' => Mage::helper('imageslider')->__('Top To Bottom')),
                array('value' => 'RightToLeft', 'label' => Mage::helper('imageslider')->__('Right To Left')),
                array('value' => 'BottomToTop', 'label' => Mage::helper('imageslider')->__('Bottom To Top')),
            ),
            'after_element_html' => '<p class="nm"><small>Select Effect Caption</small></p>',
        ));
      $fieldset->addField('order', 'text', array(
          'label'     => Mage::helper('imageslider')->__('Order'),
          'required'  => false,
          'name'      => 'order',
      ));
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('imageslider')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('imageslider')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('imageslider')->__('Disabled'),
              ),
          ),
      ));    
      if ( Mage::getSingleton('adminhtml/session')->getSliderData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getSliderData());
          Mage::getSingleton('adminhtml/session')->setSliderData(null);
      } elseif ( Mage::registry('imageslider_data') ) {
          $form->setValues(Mage::registry('imageslider_data')->getData());
      }
      return parent::_prepareForm();
  }
}

