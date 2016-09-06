<?php
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->addAttribute('catalog_product', featured, array(
		'group'             => 'General',
		'type'              => 'int',
		'backend'           => '',
		'frontend'          => '',
		'label'             => 'Featured product',
		'note'              => 'is Featured',
		'input'             => 'boolean',
		'class'             => '',
		'source'            => '',
		'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
		'visible'           => true,
		'required'          => false,
		'user_defined'      => false,
		'default'           => '0',
		'searchable'        => false,
		'filterable'        => false,
		'comparable'        => false,
		'visible_on_front'  => false,
		'unique'            => false,
		'apply_to'          => 'simple,configurable,virtual,bundle,downloadable',
		'is_configurable'   => false,
		'used_in_product_listing' => true
	));
