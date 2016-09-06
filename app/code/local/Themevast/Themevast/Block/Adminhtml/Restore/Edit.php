<?php

class Themevast_Themevast_Block_Adminhtml_Restore_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->removeButton('back');
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'themevast';
        $this->_controller = 'adminhtml_restore';
        
        $this->_updateButton('save', 'label', Mage::helper('themevast')->__('Submit'));
        $this->_updateButton('delete', 'label', Mage::helper('themevast')->__('Delete Item'));
		
    
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('themevast_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'themevast_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'themevast_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('themevast_data') && Mage::registry('themevast_data')->getId() ) {
            return Mage::helper('themevast')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('themevast_data')->getTitle()));
        } else {
            return Mage::helper('themevast')->__('');
        }
    }
}
