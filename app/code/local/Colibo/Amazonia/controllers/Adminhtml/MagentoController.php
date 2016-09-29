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


    /**
     * Add Products to Jobs.
     * ---------------------
     */
    public function jobAction()
    {
        try {

            $records = 0;

            /** Get Request Query */
            $json = $this->getRequest()->getParam('json');
            $products = json_decode($json, true);
            $products = !empty($products) ? $products : [];

            /** JSON Validation */
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception(json_last_error_msg());
            }

            /** Init Resources */
            $resource = Mage::getSingleton('core/resource');
            $dbWrite = $resource->getConnection('core_write');
            $table = $resource->getTableName('colibo_products_jobs');

            /** Process Products */
            foreach ($products as $amazonAsin => $magentoData) {

                /***sq
                 * Save Products Jobs
                 * ------------------
                 */
                $query = "REPLACE INTO " . $table . " SET "
                    . " amazon_asin = :amazonAsin, "
                    . " magento_data = :magentoData";

                $binds = [
                    'amazonAsin' => $amazonAsin,
                    'magentoData' => json_encode($magentoData)
                ];

                try {
                    $dbWrite->query($query, $binds);
                    $records++;
                } catch (\Exception $e) {
                    continue;
                }
            }

            $response = [
                'status' => true,
                'data' => $records
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