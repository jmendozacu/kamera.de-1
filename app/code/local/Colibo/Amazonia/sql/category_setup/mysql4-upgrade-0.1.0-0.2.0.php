<?php

$installer = $this;
$installer->startSetup ();

$installer->addAttribute ("catalog_category", "full_description", array (
    "group" => "General Information",
    "type" => "text",
    "backend" => "",
    "frontend" => "",
    "label" => "Full Description",
    "input" => "textarea",
    "class" => "",
    "global" => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    "visible" => true,
    "required" => false,
    "user_defined" => false,
    "default" => "",
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,
    "visible_on_front" => false,
    "unique" => false,
    "note" => "",
    "frontend_label" => "Full Description",
    "is_global" => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    "sort_order" => 4
));

$installer->endSetup ();