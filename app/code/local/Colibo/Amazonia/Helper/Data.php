<?php

/**
 * Class Colibo_Amazonia_Helper_Data
 */
class Colibo_Amazonia_Helper_Data extends Mage_Core_Helper_Abstract
{

    private $amazonRootNodes = [
        'Featured' => [
            571860 => ['Photo' => 'Kamera & Foto'],
        ],
        'General' => [
            3010076031 => ['UnboxVideo' => 'Amazon Instant Video'],
            1661650031 => ['MobileApps' => 'Apps & Spiele'],
            79899031 => ['Automotive' => 'Auto & Motorrad'],
            357577011 => ['Baby' => 'Baby'],
            80085031 => ['Tools' => 'Baumarkt'],
            64257031 => ['Beauty' => 'Beauty'],
            78689031 => ['Apparel' => 'Bekleidung'],
            213084031 => ['Lighting' => 'Beleuchtung'],
            541686 => ['Books' => 'Bücher'],
            192417031 => ['OfficeProducts' => 'Bürobedarf & Schreibwaren'],
            569604 => ['PCHardware' => 'Computer & Zubehör'],
            547664 => ['DVD' => 'DVD & Blu-ray'],
            931573031 => ['Appliances' => 'Elektro-Großgeräte'],
            54071011 => ['ForeignBooks' => 'Fremdsprachige Bücher'],
            541708 => ['VideoGames' => 'Games'],
            10925241 => ['HomeGarden' => 'Garten'],
            1571257031 => ['GiftCards' => 'Geschenkgutscheine'],
            427727031 => ['PetSupplies' => 'Haustier'],
            571860 => ['Photo' => 'Kamera & Foto'],
            530485031 => ['KindleStore' => 'Kindle-Shop'],
            2454119031 => ['Luggage' => 'Koffer], Rucksäcke & Taschen'],
            3169011 => ['Kitchen' => 'Küche & Haushalt'],
            344162031 => ['Grocery' => 'Lebensmittel & Getränke'],
            542676 => ['Music' => 'Musik-CDs & Vinyl'],
            180529031 => ['MP3Downloads' => 'Musik-Downloads'],
            340850031 => ['MusicalInstruments' => 'Musikinstrumente & DJ-Equipment'],
            327473011 => ['Jewelry' => 'Schmuck'],
            362995011 => ['Shoes' => 'Schuhe & Handtaschen'],
            542064 => ['Software' => 'Software'],
            12950661 => ['Toys' => 'Spielzeug'],
            16435121 => ['SportingGoods' => 'Sport & Freizeit'],
            5866099031 => ['Industrial' => 'Technik & Wissenschaft'],
            193708031 => ['Watches' => 'Uhren'],
            1161660 => ['Magazines' => 'Zeitschriften']
        ]
    ];


    /**
     * Get Amazon Root Nodes.
     * ----------------------
     *
     * @return array
     */
    public function getAmazonRootNodes()
    {

        $results = [];
        foreach ($this->amazonRootNodes as $group) {
            foreach ($group as $nodeId => $searchIndexPair) {
                $results[$nodeId] = $searchIndexPair;
            }
        }

        return $results;
    }


    /**
     * Get Amazon Search Index.
     * ------------------------
     *
     * @return array
     */
    public function getSearchIndexes()
    {
        return $this->amazonRootNodes;
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