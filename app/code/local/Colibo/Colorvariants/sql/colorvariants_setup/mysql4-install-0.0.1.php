<?php

$installer = $this;
$installer->startSetup ();

$installer->addAttribute (Mage_Catalog_Model_Product::ENTITY, "parent_asin", array (
    "group" => "Amazon",
    "type" => "varchar",
    "backend" => "",
    "frontend" => "",
    "label" => "Parent ASIN",
    "input" => "text",
    "class" => "",
    "global" => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    "visible" => true,
    "required" => false,
    "default" => "",
    "searchable" => true,
    "filterable" => false,
    "comparable" => true,
    "unique" => false,
    "note" => "",
    "frontend_label" => "Parent ASIN",
    "is_global" => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    "sort_order" => 2,
    "is_wysiwyg_enabled" => false,
    "visible_on_front" => true,
    "is_html_allowed_on_front" => false,
));

$installer->endSetup ();