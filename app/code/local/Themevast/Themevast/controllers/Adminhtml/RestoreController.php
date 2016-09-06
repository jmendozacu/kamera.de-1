<?php
class Themevast_Themevast_Adminhtml_RestoreController extends Mage_Adminhtml_Controller_action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('themevast/items')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }

    public function indexAction() {
        $this->newAction();
    }

    public function editAction() {
		$this->loadLayout();
        $this->_setActiveMenu('themevast/items');

        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->_addContent($this->getLayout()->createBlock('themevast/adminhtml_restore_edit'))
                ->_addLeft($this->getLayout()->createBlock('themevast/adminhtml_restore_edit_tabs'));

        $this->renderLayout();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
            $action = trim($data['action']);
			$stores = array();
            $stores = $data['store_ids'];
			if(!$stores ) { $stores = array(0=>0); }
			try {

                if ($action == 'install') {
                    //install configuration 
					if($stores[0]==0)  {
						$storeConfigs = Mage::helper('themevast/data')->getAllStore();
					} else {
						$storeConfigs = $stores; 
					}
                    foreach ($storeConfigs as $store_id) {
                        Mage::getModel('themevast/restore')->saveTemplateConfig($store_id);
                    }
                   
                    Mage::getModel('themevast/restore/')->saveStaticBlock($stores);  //install static block 
                    
                    Mage::getModel('themevast/restore/')->saveCmsPage($stores); //install cms page

                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('themevast')->__('Template was successfully saved'));
                } else if ($action == 'uninstall') {
                    //uninstall configuration 
					if($stores[0]==0)  {
						$storeConfigs = Mage::helper('themevast/data')->getAllStore();
					} else {
						$storeConfigs = $stores; 
					}
					
                    foreach ($storeConfigs as $store_id) {
                        Mage::getModel('themevast/restore')-> backupTemplateConfig($store_id);
                    }
                    //uninstall static block
                    $identityFromStatic = Mage::helper('themevast')->getNodeDataFromStaticBlock();
                    foreach ($identityFromStatic as $keyStatic) {
                        Mage::getModel('themevast/restore/')->deleteStaticBlock($keyStatic, $stores);
                    }

                    //uninstall cms page block
                    $identityFromCmsPage = Mage::helper('themevast')->getNodeDataFromCmsPageBlock();
                    foreach ($identityFromCmsPage as $keyPage) {
                        Mage::getModel('themevast/restore/')->deleteCmsPageBlock($keyPage,$stores);
                    }
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('themevast')->__('Template was succesfully uninstalled'));
                }

                $this->_redirect('*/*/edit');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('themevast')->__('Unable to find item to save'));
                $this->_redirect('*/*/edit');
            }
        }
    }

}

