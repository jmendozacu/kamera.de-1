<?php

require_once(Mage::getBaseDir() . '/vendor/autoload.php');

use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Lookup;
use ApaiIO\Operations\Search;
use GuzzleHttp\Client;
use ApaiIO\ApaiIO;


/**
 * Class Colibo_Amazonia_Model_Sync
 */
class Colibo_Amazonia_Model_Sync
{

    /** @var  array $asins */
    protected $asins;


    /**
     * Product Sync.
     * ------------
     * @param bool $isMuted
     */
    public function sync($isMuted = true)
    {
        $collection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect(array('sku'), 'inner');
        Mage::getSingleton('core/resource_iterator')->walk($collection->getSelect(), array(array($this, 'productWalker')));

        $amazonData = $this->getAmazonData();
        $this->updateProducts($amazonData, $isMuted);

        if (!$isMuted) echo "\nDone!";
    }


    /***
     * Product Walker.
     * ---------------
     * @param array $product
     */
    public function productWalker($product)
    {
        $this->asins[$product['row']['sku']] = $product['row']['entity_id'];
    }


    /**
     * Get Amazon Product.
     * -------------------
     * @return array
     */
    public function getAmazonData()
    {
        $response = [];

        /** Set Amazon Config */
        $amazonConfig = Mage::helper('amazonia')->getAmazonConfig();

        /** Init ApaIO */
        $conf = new GenericConfiguration();
        $client = new \GuzzleHttp\Client();
        $request = new \ApaiIO\Request\GuzzleRequest($client);

        $conf
            ->setCountry($amazonConfig['country_code'])
            ->setAccessKey($amazonConfig['access_key_id'])
            ->setSecretKey($amazonConfig['secret_access_key'])
            ->setAssociateTag($amazonConfig['partner_tag'])
            ->setRequest($request)
            ->setResponseTransformer(new \ApaiIO\ResponseTransformer\XmlToSimpleXmlObject());

        foreach (array_chunk(array_keys($this->asins), 10) as $asins) {

            try {

                /** Build Lookup Request */
                $apaiIO = new ApaiIO($conf);
                $lookup = new Lookup();
                $lookup->setItemId($asins);
                $lookup->setResponseGroup(['OfferSummary', 'Reviews']);
                $formattedResponse = $apaiIO->runOperation($lookup);

            } catch (\Exception $e) {
                continue;
            }

            /** Validate Response */
            $errors = $formattedResponse->Items->Request->Errors->Error ?: null;
            if (!empty($errors)) {
                continue;
            }

            /** Process Found Products */
            $items = $formattedResponse->Items->Item;
            foreach ($items as $item) {
                $item = Mage::helper('amazonia')->xml2array($item);
                $response[$item['ASIN']] = ['data' => $item];
            }
        }

        return $response;
    }


    /***
     * Update Magento Products.
     * ------------------------
     * @param array $amazonData
     * @param bool $isMuted
     */
    public function updateProducts($amazonData, $isMuted = true)
    {
        $storeId = 0;
        $action = Mage::getModel('catalog/resource_product_action');

        foreach ($amazonData as $asin => $product) {

            $attributes = [];
            $offers = !empty($product['data']['OfferSummary']) ? $product['data']['OfferSummary'] : [];

            /** Update New Price */
            if (!empty($offers['LowestNewPrice']['Amount'])) {
                $attributes['price'] = $offers['LowestNewPrice']['Amount'];
            };

            /** Update Used Price */
            if (!empty($offers['LowestUsedPrice']['Amount'])) {
                $attributes['price_used'] = $offers['LowestUsedPrice']['Amount'];
            }

            /** Update Rating */
            $frameUrl = !empty($product['data']['CustomerReviews']['IFrameURL'])
                ? $product['data']['CustomerReviews']['IFrameURL'] : false;

            $rating = $this->parseProductRating($frameUrl);
            if (!empty($rating)) {
                $attributes['rating'] = $rating;
            };

            $action->updateAttributes([$this->asins[$asin]], $attributes, $storeId);
            if (!$isMuted) echo $asin . ": " . implode(', ', $attributes) . "\n";
        }
    }


    /***
     * Parse Amazon Product Rating.
     * ----------------------------
     * @param $url
     * @return mixed
     */
    public function parseProductRating($url)
    {
        if (empty($url)) return false;

        /** Turn off errors */
        libxml_use_internal_errors(true);

        /** Get Page Content */
        $client = new Client();
        $response = $client->request('GET', $url);
        $plainHtml = $response->getBody()->getContents();

        /** Get Page Links */
        $dom = new \DOMDocument;
        $dom->loadHTML($plainHtml);
        $xpath = new \DOMXPath($dom);

        $ratingImages = $xpath->query("//*[contains(@class, 'asinReviewsSummary')]//a//img/@title");
        if (empty($ratingImages)) return false;

        foreach ($ratingImages as $ratingImage) {

            $title = $ratingImage->nodeValue;
            preg_match('/\d.\d/iu', $title, $results);

            $rating = !empty($results[0]) ? $results[0] : false;
            return $rating;
        }

        return false;
    }
}