<?php

require_once(Mage::getBaseDir() . '/vendor/autoload.php');

use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\ConsoleOutput;
use ApaiIO\Configuration\GenericConfiguration;
use Sunra\PhpSimple\HtmlDomParser;
use ApaiIO\Operations\Lookup;
use ApaiIO\Operations\Search;
use GuzzleHttp\Client;
use ApaiIO\ApaiIO;


/**
 * Class Colibo_Amazonia_Model_Sync
 */
class Colibo_Amazonia_Model_Import
{

    /**
     * @var ConsoleOutput $output
     */
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
    public function import()
    {

        /** Init Resources */
        $resource = Mage::getSingleton('core/resource');
        $dbWrite = $resource->getConnection('core_write');
        $table = $resource->getTableName('colibo_products_jobs');

        /**
         * Get Products Jobs
         * ------------------
         */
        $query = "SELECT * FROM " . $table . " LIMIT 0, 10;";
        $jobs = $dbWrite->query($query)->fetchAll();

        foreach ($jobs as $job) {

            $asin = $job['amazon_asin'];
            $isExists = Mage::getModel('catalog/product')->loadByAttribute('sku', $asin);

            if ($isExists || $this->createProduct($job)) {
                $query = "DELETE FROM " . $table . " WHERE amazon_asin = '" . $asin . "';";
                $dbWrite->query($query);
            }
        }
    }


    /**
     * Create Magento Product.
     * ------------------------
     * @param array $row
     * @return mixed
     */
    public function createProduct($row)
    {
        /** Validation */
        if (empty($row['amazon_asin']) || empty($row['magento_data'])) {
            $this->output->writeln('<error>Empty Magento Data:</error>');
            dump($row);
            return false;
        }

        /** Get Job Data */
        $asin = trim($row['amazon_asin']);
        $magentoData = json_decode($row['magento_data'], true);
        $attributeSetId = !empty($magentoData['attribute_set_id']) ? $magentoData['attribute_set_id'] : 4;
        $categoryId = !empty($magentoData['category_id']) ? $magentoData['category_id'] : 2;

        /** Get Amazon Data */
        $attributesGroups = $this->getAmazonData($asin);
        if (empty($attributesGroups)) {
            $this->output->writeln('<error>Empty Amazon Data: ' . $asin . '</error>');
            return false;
        }

        /** Get ComputerUniverse Data */
        if (!empty($attributesGroups['text']['ean'])) {
            $computerUniverseData = $this->getComputerUniverseData($attributesGroups['text']['ean']);
            $attributesGroups = array_merge_recursive($attributesGroups, $computerUniverseData);
        }

        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('catalog/product');

        /** @noinspection PhpUndefinedMethodInspection */
        $product
            ->setAttributeSetId($attributeSetId)
            ->setWebsiteIds([1])
            ->setTaxClassId(1)
            ->setStoreId(0)
            ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
            ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ->setTypeId(Mage_Catalog_Model_Product_Type::DEFAULT_TYPE)
            ->setCategoryIds(!empty($categoryId) ? [$categoryId] : []);

        /** Set Attributes */
        foreach ($attributesGroups as $group => $attributes) {
            foreach ($attributes as $code => $value) {

                if ($group == 'select') {
                    $value = $this->getAttributeValueId($code, $value);
                }

                if ($value !== false) {
                    $product->setData($code, $value);
                }
            }
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $product->setStockData([
            'use_config_manage_stock' => true,
            'manage_stock' => true,
            'is_in_stock' => true,
            'qty' => 1
        ]);

        /** Collect image Gallery */
        $amazonAttributes = json_decode($attributesGroups['text']['amazon_attributes'], true);
        $imageSet = !empty($amazonAttributes['ImageSets']['ImageSet']) ? $amazonAttributes['ImageSets']['ImageSet'] : [];
        $this->setImages($product, Mage::helper('amazonia')->xml2array($imageSet));

        $product->save();
        $result = $product->getId();

        $this->output->writeln('<info>#id: ' . $product->getId() . '. Product created: ' . $product->getSku() . '</info>');
        return $result;
    }


    /**
     * Get ComputerUniverse Product.
     * -----------------------------
     * @param string $ean
     * @return mixed
     */
    public function getComputerUniverseData($ean)
    {
        try {

            $grabber = new Colibo_Amazonia_Model_Grab();
            return $grabber->grab($ean);

        } catch (\Exception $e) {
            $this->output->writeln('<error>Grabber Error: ' . $ean . ' - ' . $e->getMessage() . '</error>');
            return [];
        }
    }


    /**
     * Get Amazon Product.
     * -------------------
     * @param $asin
     * @return mixed
     */
    public function getAmazonData($asin)
    {
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

        try {

            /** Build Lookup Request */
            $apaiIO = new ApaiIO($conf);
            $lookup = new Lookup();
            $lookup->setItemId($asin);
            $lookup->setResponseGroup([
                'Accessories',
                'EditorialReview',
                'ItemAttributes',
                'Images',
                'Large',
                'ItemIds',
                'OfferSummary',
                'Offers',
                'OfferFull',
                'PromotionSummary',
                'Reviews',
                'SalesRank',
                'Similarities',
                'Tracks',
                'Variations',
                'VariationSummary'
            ]);
            $formattedResponse = $apaiIO->runOperation($lookup);

        } catch (\Exception $e) {
            $this->output->writeln('<error>ApaiIO Lookup Error: ' . $asin . ' - ' . $e->getMessage() . '</error>');
            return false;
        }

        /** Validate Response */
        $errors = $formattedResponse->Items->Request->Errors->Error ?: null;
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->output->writeln('<error>' . $error->Message . ' (code: ' . $error->Code . ')</error>');
            }
            return false;
        }

        /** Process Found Products */
        $items = $formattedResponse->Items->Item;
        $item = is_array($items) ? current($items) : $items;
        $item = Mage::helper('amazonia')->xml2array($item);


        /** Collect Attributes */
        $attributes = !empty($item['ItemAttributes']) ? $item['ItemAttributes'] : [];

        $data = [

            'select' => [
                'manufacturer' => !empty($attributes['Brand']) ? $attributes['Brand'] : false,
                'color' => !empty($attributes['Color']) ? $attributes['Color'] : false,
                //'product_type' => ''
            ],

            'text' => [

                'sku' => !empty($item['ASIN']) ? $item['ASIN'] : false,

                'model' => !empty($attributes['Model']) ? $attributes['Model'] : false,
                'ean' => !empty($attributes['EAN']) ? $attributes['EAN'] : false,

                'weight' => !empty($attributes['ItemDimensions']['Weight'])
                    ? number_format(($attributes['ItemDimensions']['Weight'] / 1000), 2, ',', '') : false,

                'name' => !empty($attributes['Title']) ? $attributes['Title'] : false,
                'description' => !empty($item['EditorialReviews']['EditorialReview']['Content'])
                    ? $item['EditorialReviews']['EditorialReview']['Content'] : false,
                'short_description' => !empty($data['Feature'])
                    ? json_encode($data['Feature']) : false,

                'price' => !empty($item['OfferSummary']['LowestNewPrice']['Amount'])
                    ? number_format(($item['OfferSummary']['LowestNewPrice']['Amount'] / 100), 2, ',', '') : false,
                'price_used' => !empty($item['OfferSummary']['LowestUsedPrice']['Amount'])
                    ? number_format(($item['OfferSummary']['LowestUsedPrice']['Amount'] / 100), 2, ',', '') : false,

                'amazon_attributes' => json_encode($item),

                'msrp' => 0.00,
                'rating' => 0.00,
            ]
        ];

        return $data;
    }


    /***
     * Set Images.
     * -----------
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $imageSet
     * @return $this
     * @throws Exception
     */
    protected function setImages(&$product, $imageSet)
    {
        /** Fix Single Image Gallery */
        if (empty($imageSet[0])) {
            $imageSet = array($imageSet);
        }

        $isPrimary = false;
        foreach ($imageSet as $image) {

            $imageUrl = false;
            foreach ($image as $code => $imageAttribute) {

                if ($code == '@attributes' && !empty($imageAttribute['Category'])) {
                    $isPrimary = ($imageAttribute['Category'] == 'primary') ? true : false;
                }

                /** Get Last(Highest) Resolution Image */
                if (!empty($image[$code]['URL'])) {
                    $imageUrl = $image[$code]['URL'];
                }
            }

            $splFile = new SplFileInfo($imageUrl);
            $imagePath = Mage::getBaseDir('upload') . '/' . $splFile->getFilename();

            /** Download Image */
            $curlInfo = $this->downloadImage($imageUrl, $imagePath);

            /** Image Validation */
            if ($curlInfo['http_code'] != 200) {
                break;
            }

            /** Save Image and Assign to Product */
            if (file_exists($imagePath) && filesize($imagePath) > 1024) {
                $imageOptions = $isPrimary ? ['image', 'small_image', 'thumbnail'] : null;
                $product->addImageToMediaGallery($imagePath, $imageOptions, true, false);
            }
        }
    }


    /**
     * Download Image.
     * ---------------
     *
     * @param $url
     * @param $path
     * @return array
     */
    protected function downloadImage($url, $path)
    {
        $handle = fopen($path, 'w+');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FILE, $handle);
        curl_exec($ch);

        $curlInfo = curl_getinfo($ch);
        curl_close($ch);
        fclose($handle);

        return $curlInfo;
    }


    /**
     * Add Attribute Value.
     * --------------------
     *
     * @param $attributeCode
     * @param $attributeValue
     * @return bool
     */
    protected function addAttributeValue($attributeCode, $attributeValue)
    {

        $attributeValue = mb_strtolower($attributeValue, 'UTF-8');

        /** @var Mage_Core_Model_Resource $resourceModel */
        $resourceModel = Mage::getSingleton('core/resource');
        $conn = $resourceModel->getConnection('core_write');

        /** Insert New Option */
        $conn->query(
            "INSERT INTO eav_attribute_option (attribute_id, sort_order)
			 VALUES ((SELECT attribute_id FROM eav_attribute WHERE attribute_code = '" . $attributeCode . "'), 0);"
        );

        /** Get New Option Id */
        /** @noinspection PhpUndefinedMethodInspection */
        $optionId = $conn->lastInsertId();

        /** Insert New Option Value */
        $conn->query(
            "INSERT INTO eav_attribute_option_value (option_id, store_id, value)
			 VALUES('" . $optionId . "', 0, '" . $attributeValue . "');"
        );

        return $optionId;
    }


    /***
     * Get Attribute Value Id.
     * -----------------------
     *
     * @param $attributeCode
     * @param $attributeValue
     * @return bool
     */
    protected function getAttributeValueId($attributeCode, $attributeValue)
    {
        $attributeValue = mb_strtolower($attributeValue, 'UTF-8');

        /** @var Mage_Core_Model_Resource $resourceModel */
        $resourceModel = Mage::getSingleton('core/resource');
        $conn = $resourceModel->getConnection('core_read');

        $valueId = $conn->fetchOne("
			SELECT option_id FROM eav_attribute_option AS eao
			LEFT JOIN eav_attribute_option_value AS eaov USING (option_id)
			LEFT JOIN eav_attribute AS ea USING (attribute_id)
			WHERE ea.attribute_code = '" . $attributeCode . "' AND eaov.value = '" . $attributeValue . "';
		");

        return $valueId ?: $this->addAttributeValue($attributeCode, $attributeValue);
    }

}