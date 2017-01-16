<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
try {
    $installer->run("
      ALTER TABLE {$this->getTable('blog/blog')}  
      ADD COLUMN `products` TEXT NULL AFTER `short_content`;
    ");
} catch (Exception $e) {
    Mage::logException($e);
}

$installer->endSetup();