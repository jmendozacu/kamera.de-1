<?php

require_once(Mage::getBaseDir() . '/vendor/autoload.php');

use ApaiIO\ApaiIO;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Lookup;
use GuzzleHttp\Client;
use Symfony\Component\Console\Output\ConsoleOutput;


/**
 * Class Colibo_Amazonia_Model_Sync
 */
class Colibo_Amazonia_Model_Sync
{

    const CHUNK_SIZE = 1000;


    /** @var  array $asins */
    protected $asins;

    /** @var ConsoleOutput $output */
    private $output;


    /**
     * Colibo_Amazonia_Model_Import constructor.
     * -----------------------------------------
     * @param ConsoleOutput $output
     */
    public function __construct(ConsoleOutput $output)
    {
        $this->output = $output;
    }


    /**
     * Product Sync.
     * ------------
     */
    public function sync()
    {
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect(array('sku', 'updated_at'), 'inner');

        $collection->getSelect()
            ->order('updated_at', 'ASC')
            ->limit(self::CHUNK_SIZE);

        Mage::getSingleton('core/resource_iterator')->walk($collection->getSelect(), array(array($this, 'productWalker')));

        $amazonData = $this->getAmazonData();
        $this->updateProducts($amazonData);
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

        foreach (array_keys($this->asins) as $asin) {

            try {

                /** Build Lookup Request */
                $apaiIO = new ApaiIO($conf);
                $lookup = new Lookup();
                $lookup->setItemId($asin);
                $lookup->setResponseGroup(['OfferSummary', 'Reviews']);
                $formattedResponse = $apaiIO->runOperation($lookup);

                /** Validate Response */
                $errors = $formattedResponse->Items->Request->Errors->Error ?: null;
                if (!empty($errors)) {
                    foreach ($errors as $error) {

                        $this->output->writeln('<error>' . $error->Message . ' (code: ' . $error->Code . ')</error>');

                        /** Update Failed Product: updated_at field */
                        if ($error->Code == 'AWS.InvalidParameterValue') {
                            $response[$asin] = ['data' => []];
                        }

                    }
                    continue;
                }

                /** Process Found Products */
                $items = $formattedResponse->Items->Item;
                foreach ($items as $item) {
                    $item = Mage::helper('amazonia')->xml2array($item);
                    $response[$item['ASIN']] = ['data' => $item];
                }

            } catch (\GuzzleHttp\Exception\ServerException $e) {
                continue;
            } catch (\Exception $e) {
                $this->output->writeln('<error>ApaiIO Lookup Error: ' . $asin . ' - ' . $e->getMessage() . '</error>');
                continue;
            }
        }

        return $response;
    }


    /***
     * Update Magento Products.
     * ------------------------
     * @param array $amazonData
     */
    public function updateProducts($amazonData)
    {
        $storeId = 0;
        $action = Mage::getModel('catalog/resource_product_action');

        foreach ($amazonData as $asin => $product) {

            $attributes = [];
            $offers = !empty($product['data']['OfferSummary']) ? $product['data']['OfferSummary'] : [];

            /** Update New Price */
            if (!empty($offers['LowestNewPrice']['Amount'])) {
                $attributes['price'] = number_format(($offers['LowestNewPrice']['Amount'] / 100), 2, ',', '');
            } else {
                $attributes['price'] = 0.00;
            }
            $attributes['offers_number_new'] = !empty($offers['TotalNew']) ? (int)$offers['TotalNew'] : 0;


            /** Update Used Price */
            if (!empty($offers['LowestUsedPrice']['Amount'])) {
                $attributes['price_used'] = number_format(($offers['LowestUsedPrice']['Amount'] / 100), 2, ',', '');
            } else {
                $attributes['price_used'] = 0.00;
            }
            $attributes['offers_number_used'] = !empty($offers['TotalUsed']) ? (int)$offers['TotalUsed'] : 0;


            /** Update Rating */
            $frameUrl = !empty($product['data']['CustomerReviews']['IFrameURL'])
                ? $product['data']['CustomerReviews']['IFrameURL'] : false;
            $attributes['reviews_url'] = preg_replace('/http[s]?:/i', '', $frameUrl);


            $rating = $this->parseProductRating($frameUrl);
            if (!empty($rating)) {
                $attributes['rating'] = $rating;
            };

            try {

                $action->updateAttributes([$this->asins[$asin]], $attributes, $storeId);

                /** Init Resources */
                $resource = Mage::getSingleton('core/resource');
                $dbWrite = $resource->getConnection('core_write');
                $table = $resource->getTableName('catalog_product_entity');

                /** Update Products Change Date */
                $query = "UPDATE " . $table . " SET updated_at = :updated_at WHERE sku = :sku;";
                $binds = [
                    ':updated_at' => Varien_Date::now(),
                    ':sku' => $asin
                ];

                $dbWrite->query($query, $binds);


            } catch (\Exception $e) {
                $this->output->writeln('<error>Update attributes: ' . $asin . ' - ' . $e->getMessage() . '</error>');
                continue;
            }

            $this->output->writeln('<info>' . $asin . ": " . implode(' / ', $attributes) . '</info>');
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

        try {
            /** Get Page Content */
            $client = new Client();
            $response = $client->request('GET', $url);
            $plainHtml = $response->getBody()->getContents();
        } catch (\Exception $e) {
            $this->output->writeln('<error>CURL Error: ' . $url . ' - ' . $e->getMessage() . '</error>');
            return false;
        }

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