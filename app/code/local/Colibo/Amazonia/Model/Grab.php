<?php

require_once(Mage::getBaseDir() . '/vendor/autoload.php');

use Sunra\PhpSimple\HtmlDomParser;


/**
 * Class Colibo_Amazonia_Model_Grab
 */
class Colibo_Amazonia_Model_Grab
{

    /** @var array $config */
    private $config = [

        /** Basic */
        'domain' => 'https://www.computeruniverse.net/',

        /** Selectors: category page */
        'xpath_product' => '.productsTableList .productsTableRow',
        'xpath_product_link' => '.productsTableColOne > .colOneContent > a',
        'xpath_product_title' => 'h1.producttitle',
        'xpath_product_vendor' => '#outerItemScope .p_ir table img',
        'xpath_product_sku' => '#outerItemScope .p_ir .p_ilh span',

        /* Selectors: product page */
        'xpath_attribute' => '#outerItemScope table.techdata > tr',
        'xpath_attribute_group' => 'td.title',
        'xpath_attribute_key' => 'td[2]',
        'xpath_attribute_value' => 'td[3]',
    ];


    private $attributesMapping = [
        'product_type' => ['Produkttyp'],
        'color' => ['Farbe'],
        'weight' => ['Gewicht'],
        'guide_number' => ['Leitzahl'],
        'connection_type' => ['Verbindung', 'Befestigungstyp', 'Hot Shoe-Typ', 'Entwickelt für'],
        'has_wifi' => ['WLAN integriert', 'Drahtlosschnittstelle', 'Interfacetyp'],
        'battery_type' => ['Batterietyp', 'Stromversorgung'],
        'model_type' => ['Filtertyp', 'Kameratyp', 'Kartentyp', 'Typ'],
        'diameter' => ['Filterdurchmesser'],
        'compensation' => ['Vergütung'],
        'optical_zoom' => ['optischer Zoom', 'Optisches Zoom'],
        'display_size' => ['Displaygrösse'],
        'sensor' => ['Touchscreen'],
        'digital_zoom' => ['Digitalzoom'],
        'resolution' => ['Auflösung', 'Max. Videoauflösung', 'Videoauflösung'],
        'pivotally_display' => ['Display neig'],
        'camera_kit' => ['Objektiv im Lieferumfang'],
        'waterproof' => ['Wasserdicht', 'Wassertiefe'],
        'image_stabilizer' => ['Bildstabilisator'],
        'focal_length' => ['Brennweite'],
        'max_luminous' => ['Blendenöffnung'],
        'memory_size' => ['Kapazität'],
        'writing_speed' => ['Schreibgeschwindigkeit'],
        'reading_speed' => ['Lesegeschwindigkeit'],
        'objective_type' => ['Typ'],
        'material' => ['Material'],
        'max_height' => ['Max. Betriebshöhe'],
        'min_height' => ['Min Betriebshöhe'],
        'max_burden' => ['Max. unterstütztes Gewicht'],
        'tripot_head' => ['Stativkopf'],
        'fps' => ['Bilder pro Sekunde', 'Bildfequenz'],
        'technology' => ['Technologie'],
        'mah' => ['Kapazität'],
        'interchangeable_lens' => ['Objektivbefestigungstyp'],
        'suitable_for' => ['Entwickelt für'],
        'inner_width' => ['Breite'],
        'inner_height' => ['Höhe'],
        'inner_depth' => ['Tiefe'],
    ];


    /**
     * Product Sync.
     * ------------
     * @param $ean
     * @return array
     * @throws Exception
     */
    public function grab($ean)
    {
        $url = $this->getConfig('domain') . 'list.asp?searchname=' . $ean;

        $products = [];
        $dom = HtmlDomParser::file_get_html($url);
        $productTree = $dom->find($this->getConfig('xpath_product'));

        if (empty($productTree)) {
            throw new Exception('xPath: no products found: ' . $this->getConfig('xpath_product'));
        }

        /** @var simple_html_dom_node $productNode */
        foreach ($productTree as $productNode) {

            /** @var simple_html_dom_node[] $linkNodes */
            $linkNodes = $productNode->find($this->getConfig('xpath_product_link'));
            $linkNode = $this->getNode($linkNodes, 'xpath_product_link');

            $productUrl = $this->prepareLink($linkNode->getAttribute('href'));
            return $this->getAttributes($productUrl);
        }

        if (empty($products)) {
            throw new Exception('xPath: products found, but can\'t parse it');
        }
    }


    /**
     * Get Product Attributes.
     * ----------------------
     * @param $productUrl
     * @return array
     * @throws Exception
     */
    public function getAttributes($productUrl)
    {
        $group = null;
        $attributes = [];
        $computerUniverse = [];
        $dom = HtmlDomParser::file_get_html($productUrl);

        /** Get Attribute List */
        $attributeTree = $dom->find($this->getConfig('xpath_attribute'));
        if (empty($attributeTree)) {
            throw new Exception('xPath: no attributes found - ' . $this->getConfig('xpath_attribute'));
        }

        /** @var simple_html_dom_node $attributeNode */
        foreach ($attributeTree as $attributeNode) {

            /** Get Attribute Group */
            $groupNodes = $attributeNode->find($this->getConfig('xpath_attribute_group'));
            if (!empty($groupNodes)) {
                $group = $this->getNodeText($groupNodes, 'xpath_attribute_group');
                continue;
            }

            /** Get Attribute Key\Value Pair */
            $keyNodes = $attributeNode->find($this->getConfig('xpath_attribute_key'));
            $valueNodes = $attributeNode->find($this->getConfig('xpath_attribute_value'));

            $key = $this->getNodeText($keyNodes, 'xpath_attribute_key', true);
            $value = $this->getNodeText($valueNodes, 'xpath_attribute_value', true);

            if (empty($key) && empty($value)) {
                continue;
            }

            /** Find Magento Attribute by Mapping */
            foreach ($this->attributesMapping as $magentoCode => $keywords) {
                foreach ($keywords as $keyword) {

                    if (preg_match('/Abmessungen/u', $key)) {
                        /** Dimensions */
                        $dimensions = explode('x', $value);
                        if (count($dimensions) == 3) {
                            $attributes['select']['inner_width'] = trim($dimensions[0]);
                            $attributes['select']['inner_depth'] = trim($dimensions[1]);
                            $attributes['select']['inner_height'] = trim($dimensions[2]);
                        }
                    }

                    if (preg_match('/' . $keyword . '/u', $key)) {
                        /** Gotcha Magento Attribute */
                        $chunks = explode('(', $value);
                        $attributes['select'][$magentoCode] = is_array($chunks) ? trim(current($chunks)) : $chunks;
                        break 2;
                    }

                    if (preg_match('/Gewicht/u', $key)) {
                        /** Weight */
                        $attributes['text']['weight'] = (int)(number_format(($value / 1000), 2, ',', ''));
                    }

                    if (preg_match('/Produkttyp/u', $key)) {
                        /** Product Type */
                        $chunks = explode(' ', $value);
                        $attributes['select']['product_type'] = is_array($chunks) ? trim(end($chunks)) : $chunks;
                    }
                }
            }

            /** Collect ComputerUniverse Attributes */
            if (!empty($group)) {
                $computerUniverse[$group][$key] = $value;
            } else {
                $computerUniverse[$key] = $value;
            }
        }

        $attributes['text']['computeruniverse_attributes'] = json_encode($computerUniverse);

        return $attributes;
    }


    /**
     * Get Config Option.
     * ------------------
     * @param string $key
     * @return string
     * @throws Exception
     */
    public function getConfig($key = '')
    {
        if (empty($this->config[$key])) {
            throw new Exception('Config: key not exists or empty - ' . $key);
        }

        return $this->config[$key];
    }


    /**
     * Prepare Url.
     * ------------
     * @param string $link
     * @return string
     */
    public function prepareLink($link = '')
    {
        $domain = $this->getConfig('domain');
        $path = str_replace($domain, '', $link);

        $path = ltrim($path, '/');
        $domain = rtrim($domain, '/');

        return $domain . '/' . $path;
    }


    /**
     * Get Clear Node Text.
     * --------------------
     * @param simple_html_dom_node[] $htmlNodes
     * @param string $xPath
     * @param bool $ignoreEmpty
     * @return simple_html_dom_node $htmlNode
     * @throws Exception
     */
    public function getNode($htmlNodes, $xPath, $ignoreEmpty = false)
    {
        if (empty($htmlNodes) && !$ignoreEmpty) {
            throw new Exception('xPath: empty node - ' . $this->getConfig($xPath));
        }

        return is_array($htmlNodes) ? current($htmlNodes) : $htmlNodes;
    }


    /**
     * Get Clear Node Text.
     * --------------------
     * @param simple_html_dom_node[] $htmlNodes
     * @param string $xPath
     * @param bool $ignoreEmpty
     * @return string
     */
    public function getNodeText($htmlNodes, $xPath, $ignoreEmpty = false)
    {
        $htmlNode = $this->getNode($htmlNodes, $xPath, $ignoreEmpty);
        if (empty($htmlNode)) {
            return false;
        }

        $innerText = preg_replace('/<br(\s+)?\/?>/i', "\n", $htmlNode->innertext());
        $text = strip_tags($innerText);

        return trim($text);
    }

}