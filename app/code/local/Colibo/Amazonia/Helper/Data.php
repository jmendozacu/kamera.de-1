<?php

/**
 * Class Colibo_Amazonia_Helper_Data
 */
class Colibo_Amazonia_Helper_Data extends Mage_Core_Helper_Abstract
{

    private $searchIndexes = [

        'Featured' => [
            'Photo' => 'Kamera & Foto',
            'PCHardware' => 'Computer & Zubehör',
            'Software' => 'Software',
        ],

        'Other' => [
            'UnboxVideo' => 'Amazon Instant Video',
            'Pantry' => 'Amazon Pantry',
            'MobileApps' => 'Apps & Spiele',
            'Automotive' => 'Auto & Motorrad',
            'Baby' => 'Baby',
            'Tools' => 'Baumarkt',
            'Beauty' => 'Beauty',
            'Apparel' => 'Bekleidung',
            'Lighting' => 'Beleuchtung',
            'Books' => 'Bücher',
            'OfficeProducts' => 'Bürobedarf & Schreibwaren',
            'PCHardware' => 'Computer & Zubehör',
            'DVD' => 'DVD & Blu-ray',
            'HealthPersonalCare' => 'Drogerie & Körperpflege',
            'Appliances' => 'Elektro-Großgeräte',
            'Electronics' => 'Elektronik & Foto',
            'ForeignBooks' => 'Fremdsprachige Bücher',
            'VideoGames' => 'Games',
            'HomeGarden' => 'Garten',
            'GiftCards' => 'Geschenkgutscheine',
            'PetSupplies' => 'Haustier',
            'Photo' => 'Kamera & Foto',
            'KindleStore' => 'Kindle-Shop',
            'Classical' => 'Klassik',
            'Luggage' => 'Koffer, Rucksäcke & Taschen',
            'Kitchen' => 'Küche & Haushalt',
            'Grocery' => 'Lebensmittel & Getränke',
            'Music' => 'Musik-CDs & Vinyl',
            'MP3Downloads' => 'Musik-Downloads',
            'MusicalInstruments' => 'Musikinstrumente & DJ-Equipment',
            'Jewelry' => 'Schmuck',
            'Shoes' => 'Schuhe & Handtaschen',
            'Software' => 'Software',
            'Toys' => 'Spielzeug',
            'SportingGoods' => 'Sport & Freizeit',
            'Industrial' => 'Technik & Wissenschaft',
            'Watches' => 'Uhren',
            'Magazines' => 'Zeitschriften',
            'Blended' => 'Blended'
        ]
    ];


    /**
     * Get Amazon Search Indexes.
     * --------------------------
     *
     * @return array
     */
    public function getSearchIndexes()
    {
        return $this->searchIndexes;
    }


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