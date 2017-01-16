<?php

require_once(Mage::getBaseDir() . '/vendor/autoload.php');

use Affilinet\ProductData\AffilinetClient;
use Affilinet\ProductData\Exceptions\AffilinetProductWebserviceException;
use Affilinet\ProductData\Requests\ProductsRequest;


/**
 * Class Colibo_AffiliNet_Helper_Data
 */
class Colibo_AffiliNet_Helper_Data extends Mage_Core_Helper_Abstract
{

    const PRICES_PAGE_SIZE = 50;


    /**
     * Get Shops Price Comparisons
     * ----------------------------
     *
     * @param string $ean
     * @return array
     */
    public function getShopsPriceComparisons($ean = '')
    {

        try {

            /** Get Product EAN */
            $ean = trim($ean);

            $config = [
                'publisher_id' => Mage::getStoreConfig('affilinet_api/price_comparison/publisher_id'),
                'product_webservice_password' => Mage::getStoreConfig('affilinet_api/price_comparison/product_webservice_password'),
            ];

            /** Init Affili API */
            $affilinet = new AffilinetClient($config);
            $search = new ProductsRequest($affilinet);

            /** Search Product by EAN */
            $raw = $search
                ->addProductImage()
                ->addAllShopLogos()
                ->addFilterQuery('EAN', $ean)
                ->pageSize(self::PRICES_PAGE_SIZE)
                ->send();

            /** Check Results */
            if (count($raw->getProducts())) {

                $this->loadLayout();
                $block = $this->getLayout()->getBlock('affilinet');

                if ($block) {

                    /** Pass Data and Render Block */
                    $block->setData('products', $raw->getProducts());

                    $response = [
                        'status' => true,
                        'html' => $block->renderView()
                    ];

                } else {

                    /** Product Not Found */
                    $response = [
                        'status' => false,
                        'message' => 'Magento: can\'t load block by name: affilinet'
                    ];
                }

            } else {

                /** Product Not Found */
                $response = [
                    'status' => false,
                    'message' => 'AffiliNet: products not found by EAN - ' . $ean
                ];
            }


        } catch (AffilinetProductWebserviceException $e) {
            $response = [
                'status' => false,
                'message' => $e->getMessage()
            ];
        } catch (Exception $e) {
            $response = [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }

        return $response;
    }

}