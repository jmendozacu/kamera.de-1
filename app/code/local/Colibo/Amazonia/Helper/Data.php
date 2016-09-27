<?php

/**
 * Class Colibo_Amazonia_Helper_Data
 */
class Colibo_Amazonia_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Generate HTML Tree from Array.
     * -----------------------------
     * @param $data
     * @return string
     */
    public function generateArrayTree($data)
    {
        $tree = '<ul>';
        foreach ($data as $code => $item) {
            $tree .= '<li>';
            if (!is_array($item) && preg_match('/http/i', $item)) {
                $item = '<a href="' . $item . '" target="_blank">Link</a>';
            }
            $tree .= !is_int($code) ? $code : '';
            if (is_array($item)) {
                $tree .= $this->generateArrayTree($item);
            } else {
                $tree .= (!is_int($code) ? ': ' : '') . nl2br($item);
            }
            $tree .= '</li>';
        }
        $tree .= '</ul>';
        return $tree;
    }


    /**
     * SimpleXmlElement to Array.
     * --------------------------
     * @param SimpleXMLElement $xml
     * @param array $out
     * @return array
     */
    public function xml2array($xml, $out = array())
    {
        foreach ((array)$xml as $index => $node) {
            $out[$index] = (is_object($node)) ? $this->xml2array($node) : $node;
        }

        return $out;
    }


    /**
     * SimpleXmlElement to Array.
     * --------------------------
     * @return array
     */
    public function getProductTypes()
    {
        /** Init Resources */
        $resource = Mage::getSingleton('core/resource');
        $dbRead = $resource->getConnection('core_read');
        $table = $resource->getTableName('colibo_product_types');

        /***
         * Get Total Types Mapping
         * ---------------------------
         */
        $query = "SELECT * FROM " . $table;
        $data = $dbRead->query($query)->fetchAll();

        return !empty($data) ? $data : [];
    }


    /**
     * Get Categories List.
     * --------------------
     */
    public function getCategories()
    {
        $categories = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSort('path', 'asc')
            ->addFieldToFilter('is_active', array('eq' => '1'))
            ->addFieldToFilter('level', array('gt' => '1'))
            ->load();

        return $categories;
    }


    /**
     * Get Attribute Set List.
     * -----------------------
     */
    public function getAttributeSets()
    {
        $entityType = Mage::getModel('catalog/product')->getResource()->getTypeId();
        $attributeSets = Mage::getResourceModel('eav/entity_attribute_set_collection')->setEntityTypeFilter($entityType);

        return $attributeSets;
    }


    /**
     *
     * Get Amazon Config.
     * ------------------
     * @return array
     */
    public function getAmazonConfig()
    {
        return [
            'access_key_id' => Mage::getStoreConfig('amazon_api/products_import/access_key_id'),
            'secret_access_key' => Mage::getStoreConfig('amazon_api/products_import/secret_access_key'),
            'partner_tag' => Mage::getStoreConfig('amazon_api/products_import/partner_tag'),
            'country_code' => substr(Mage::getStoreConfig('general/country/default'), 0, 2)
        ];
    }
}