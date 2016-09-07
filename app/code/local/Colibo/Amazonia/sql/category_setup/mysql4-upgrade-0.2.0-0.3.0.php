<?php

$installer = $this;
$installer->startSetup ();

$installer->updateAttribute('catalog_category', 'full_description', 'is_wysiwyg_enabled', 1);
$installer->updateAttribute('catalog_category', 'full_description', 'is_html_allowed_on_front', 1);

$installer->endSetup ();