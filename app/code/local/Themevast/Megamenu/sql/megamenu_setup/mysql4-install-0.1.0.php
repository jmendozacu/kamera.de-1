<?php

$installer = $this;

$installer->startSetup();

$installer->addAttribute('catalog_category', 'cat_label', array(
	'group'				=> 'General Information',
	'label'				=> 'Category Label',
	'note'				=> "Labels have to be defined in menu settings",
	'type'				=> 'varchar',
	'input'				=> 'select',
	'source'			=> 'megamenu/config_source_category_label',
	'visible'			=> true,
	'required'			=> false,
	'backend'			=> '',
	'frontend'			=> '',
	'searchable'		=> false,
	'filterable'		=> false,
	'comparable'		=> false,
	'user_defined'		=> true,
	'visible_on_front'	=> true,
	'wysiwyg_enabled'	=> false,
	'is_html_allowed_on_front'	=> false,
	'global'			=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));

$installer->endSetup(); 