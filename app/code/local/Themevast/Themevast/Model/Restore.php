<?php
class Themevast_Themevast_Model_Restore extends Mage_Core_Model_Abstract {

    public function _construct() {
	
        parent::_construct();
        $this->_init('themevast/restore');
    }

    public function saveStaticBlock($store = NULL) {
        $staticData = Mage::helper('themevast/data')->getStaticBlockData();
        foreach ($staticData as $block) {
            $block['stores'] = $store;
            if (!Mage::helper('themevast/data')->haveBlockBefore($block['identifier'])) {
                Mage::getModel('cms/block')->setData($block)->save();
            } else {
                Mage::getModel('cms/block')->load($block['identifier'])->setStores($store)->save();
            }
        }
    }

    public function saveCmsPage($store = NULL) {
        $cmsPageData = Mage::helper('themevast/data')->getCmsPageData();
        foreach ($cmsPageData as $block) {
            $block['stores'] = $store;
            if (!Mage::helper('themevast/data')->haveBlockPageBefore($block['identifier'])) {
                Mage::getModel('cms/page')->setData($block)->save();
            } else {
                Mage::getModel('cms/page')->load($block['identifier'])->setStores($store)->save();
            }
        }
    }

    public function saveTemplateConfig($store) {
        $scope = $store ? 'stores' : 'default';
        $package = Mage::getStoreConfig('themevast/general/package');
        $theme = Mage::getStoreConfig('themevast/general/theme');
        $home = Mage::getStoreConfig('themevast/general/cms_home_page');
		Mage::getConfig()->saveConfig('design/package/name', $package, $scope, $store);
        Mage::getConfig()->saveConfig('design/theme/default', $theme, $scope, $store);
        Mage::getConfig()->saveConfig('web/default/cms_home_page', $home, $scope, $store);
    }
    
    public function backupTemplateConfig($store) {
        $oldConfigData = Mage::helper('themevast/data')->getOldConfigData(); 
        $scope = ($store ? 'stores' : 'default');
        Mage::getConfig()->saveConfig('design/theme/default', $oldConfigData[0][0], $scope, $store);
        Mage::getConfig()->saveConfig('web/default/cms_home_page', $oldConfigData[0][1], $scope, $store);
		Mage::getConfig()->saveConfig('design/theme/default', $oldConfigData[0][0], $scope, 0);
        Mage::getConfig()->saveConfig('web/default/cms_home_page', $oldConfigData[0][1], $scope, 0);
    }

    public function deleteCmsPageBlock($key = NULL, $stores = NULL) {
        $model = Mage::getModel('cms/page');
        $model->load($key);
        $storesOld = $model->getStoreId();
        $storeNew = array();
        foreach ($storesOld as $storeId) {
            if (!in_array($storeId, $stores)) {
                $storeNew[] = $storeId;
            }
        }

        if (in_array(0, $stores)) {
            $model->delete();
        } else {
            $model->setStores($storeNew)->save();
        }
    }

    public function deleteStaticBlock($key = NULL, $stores = NULL) {
        $model = Mage::getModel('cms/block');
        $model->load($key);
        $storesOld = $model->getStoreId();
        $storeNew = array();
        foreach ($storesOld as $storeId) {
            if (!in_array($storeId, $stores)) {
                $storeNew[] = $storeId;
            }
        }

        if (in_array(0, $stores)) {
            $model->delete();
        } else {
            $model->setStores($storeNew)->save();
        }
    }
    
     public function _insert($table = NULL, $fields = NULL) {
        
        try {
            $connection = Mage::getSingleton('core/resource')
                    ->getConnection('core_write');
            $connection->beginTransaction();
            $connection->insert($table, $fields);
            $connection->commit();
            
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

}

