<?php

require_once(Mage::getBaseDir() . '/vendor/autoload.php');

use ApaiIO\ApaiIO;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Lookup;
use ApaiIO\Operations\Search;


/**
 * Class Colibo_Amazonia_Adminhtml_MagentoController
 */
class Colibo_Amazonia_Adminhtml_MagentoController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Amazon Import Formular.
     * -----------------------
     */
    public function indexAction()
    {

        $this->loadLayout();

        /** Set Options */
        $this->_title($this->__("Products Import"));
        $this->_setActiveMenu('amazonia/products');


        /** Set Data */
        $block = Mage::app()->getLayout()->getBlock('amazon_products_import');
        if ($block) {

            /** Set Categories List */
            $categories = Mage::helper('amazonia')->getCategories();
            $block->setCategories($categories);

            /** Set AttributeSet List */
            $attributeSets = Mage::helper('amazonia')->getAttributeSets();
            $block->setAttributeSets($attributeSets);

            /** Set Amazon Config */
            $amazonConfig = Mage::helper('amazonia')->getAmazonConfig();
            $block->setAmazonConfig($amazonConfig);

            /** Check Amazon Connection */
            $conf = new GenericConfiguration();
            $client = new \GuzzleHttp\Client();
            $request = new \ApaiIO\Request\GuzzleRequest($client);

            try {

                $conf
                    ->setCountry($amazonConfig['country_code'])
                    ->setAccessKey($amazonConfig['access_key_id'])
                    ->setSecretKey($amazonConfig['secret_access_key'])
                    ->setAssociateTag($amazonConfig['partner_tag'])
                    ->setRequest($request);

                $apaiIO = new ApaiIO($conf);

                $search = new Search();
                $apaiIO->runOperation($search);


            } catch (\Exception $e) {

                /** @noinspection PhpUndefinedMethodInspection */
                $block->setAmazonMessage($e->getMessage());
            }
        }

        $this->renderLayout();
    }


    /**
     * Create Magento Product.
     * -----------------------
     * @param SimpleXMLElement $item
     * @param int $categoryId
     * @return array
     * @throws Exception
     */
    protected function createProduct($item, $categoryId)
    {
        $item = $this->xml2array($item);

        /** Prepare Amazon Data */
        $asin = !empty($item['ASIN']) ? $item['ASIN'] : '';
        $detailPageUrl = !empty($item['DetailPageURL']) ? $item['DetailPageURL'] : '';
        $itemLinks = !empty($item['ItemLinks']['ItemLink']) ? $item['ItemLinks']['ItemLink'] : [];
        $salesRank = !empty($item['SalesRank']) ? $item['SalesRank'] : '';
        $similarProducts = !empty($item['SimilarProducts']) ? $item['SimilarProducts'] : [];
        $offers = !empty($item['Offers']) ? $item['Offers'] : [];
        $offerSummary = !empty($item['OfferSummary']) ? $item['OfferSummary'] : [];
        $accessories = !empty($item['Accessories']) ? $item['Accessories'] : [];
        $itemAttributes = !empty($item['ItemAttributes']) ? $item['ItemAttributes'] : [];
        $overview = !empty($item['EditorialReviews']['EditorialReview']['Content']) ? $item['EditorialReviews']['EditorialReview']['Content'] : '';
        $title = !empty($item['ItemAttributes']['Title']) ? $item['ItemAttributes']['Title'] : '';

        $amazonAttributes = [
            'ASIN' => $asin,
            'DetailPageURL' => $detailPageUrl,
            'ItemLinks' => $itemLinks,
            'SalesRank' => $salesRank,
            'SimilarProducts' => $similarProducts,
            'Offers' => $offers,
            'OfferSummary' => $offerSummary,
            'Accessories' => $accessories
        ];

        foreach ($itemAttributes as $code => $attribute) {
            $amazonAttributes[$code] = $attribute;
        }

        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('catalog/product');

        /** @noinspection PhpUndefinedMethodInspection */
        $product
            ->setSku($asin)
            ->setName($title)
            ->setDescription(!empty($overview) ? $overview : $title)
            ->setShortDescription(!empty($overview) ? $overview : $title)
            ->setPrice(0.00)
            ->setAttributeSetId(4)
            ->setWebsiteIds([1])
            ->setTaxClassId(0)
            ->setWeight(0.00)
            ->setStoreId(0)
            ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
            ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ->setTypeId(Mage_Catalog_Model_Product_Type::DEFAULT_TYPE)
            ->setCategoryIds(!empty($categoryId) ? [$categoryId] : [])
            ->setAmazonAttributes(json_encode($amazonAttributes));

        /** @noinspection PhpUndefinedMethodInspection */
        $product->setStockData([
            'use_config_manage_stock' => true,
            'manage_stock' => true,
            'is_in_stock' => true,
            'qty' => 1
        ]);

        /** Collect image Gallery */
        $imageSet = !empty($item['ImageSets']['ImageSet']) ? $item['ImageSets']['ImageSet'] : [];
        $this->setImages($product, $this->xml2array($imageSet));

        $product->save();
        $result = $product->getId();

        return $result;
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
     * Get Magento Attribute Set and Category.
     * ---------------------------------------
     *
     */
    public function typeAction()
    {
        try {

            /** Get Amazon Product Type */
            $amazonProductType = trim($this->getRequest()->getParam('amazon_product_type'));
            $attributeSetId = trim($this->getRequest()->getParam('attribute_set_id'));
            $categoryId = trim($this->getRequest()->getParam('category_id'));

            if (empty($categoryId) || empty($attributeSetId)) {
                throw new \Exception('Check selected values..!');
            }

            /** Init Resources */
            $resource = Mage::getSingleton('core/resource');
            $dbWrite = $resource->getConnection('core_write');
            $table = $resource->getTableName('colibo_product_types');

            /***
             * Save Selected Types Mapping
             * ---------------------------
             */
            $query = "REPLACE INTO " . $table . " SET 
            amazon_product_type = :amazonProductType,
            attribute_set_id = :attributeSetId,
            category_id = :categoryId";

            $binds = [
                'amazonProductType' => $amazonProductType,
                'attributeSetId' => $attributeSetId,
                'categoryId' => $categoryId
            ];

            $dbWrite->query($query, $binds);

            $response = [
                'status' => true,
                'types' => Mage::helper('amazonia')->getProductTypes()
            ];

        } catch (\Exception $e) {

            $response = [
                'status' => false,
                'notify' => [
                    'title' => "Error Code: " . $e->getCode(),
                    'message' => strip_tags($e->getMessage()),
                    'trace' => $e->getFile() . ":" . $e->getLine()
                ]
            ];
        }

        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
        $this->getResponse()->setBody(json_encode($response));
    }

}