<?php

require_once(Mage::getBaseDir() . '/vendor/autoload.php');

use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Search;
use ApaiIO\ApaiIO;


/**
 * Class Colibo_Amazonia_Adminhtml_AmazonController
 */
class Colibo_Amazonia_Adminhtml_AmazonController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Search Products by Query.
     * -------------------------
     */
    public function searchAction()
    {
        try {

            $this->loadLayout();

            /** Get Request */
            $mode = trim($this->getRequest()->getParam('mode', null));
            $page = trim($this->getRequest()->getParam('page', 1));
            $keywords = trim($this->getRequest()->getParam('keywords'));
            $merchant = trim($this->getRequest()->getParam('merchant', null));
            $category = trim($this->getRequest()->getParam('category'));
            $condition = trim($this->getRequest()->getParam('condition', null));
            list($minPrice, $maxPrice) = explode(';', trim($this->getRequest()->getParam('price')));

            /** Prepare Params */
            $params = [
                'page' => $page,
                'keywords' => $keywords,
                'merchant' => $merchant,
                'category' => $category,
                'condition' => $condition,
                'price_min' => $minPrice,
                'price_max' => $maxPrice,
            ];

            if (empty($keywords)) {
                throw new \Exception('Sir.. Enter keywords or direct ASIN ..');
            }

            /** Amazon API*/
            $results = $this->importAction($params);

            /** Get Magento Lists */
            $categories = Mage::helper('amazonia')->getCategories();
            $attributeSets = Mage::helper('amazonia')->getAttributeSets();

            /** Set Product List */
            $listBlock = Mage::app()->getLayout()->getBlock('amazon_products_list');
            $listBlock
                ->setResults($results['data'])
                ->setAttributeSets($attributeSets)
                ->setCategories($categories);

            /** Set Amazon Pagination */
            $paginationBlock = Mage::app()->getLayout()->getBlock('amazon_pagination');
            $paginationBlock
                ->setPagination(!empty($results['pages']) ? $results['pages'] : [])
                ->setPageTitle('Search results: ' . $keywords);

            /** Render HTML */
            if (!empty($mode) && $mode == 'next') {

                $html = [
                    'list' => $listBlock->toHtml(),
                    'pagination' => $paginationBlock->toHtml()
                ];

            } else {

                $searchBlock = Mage::app()->getLayout()->getBlock('amazon_products_search');
                $searchBlock
                    ->setAttributeSets($attributeSets)
                    ->setCategories($categories);

                $html = [
                    'search' => $searchBlock->toHtml()
                ];
            }

            $response = [
                'status' => true,
                'data' => $results['data'],
                'html' => $html,
                'keywords' => $keywords,
                'types' => Mage::helper('amazonia')->getProductTypes(),
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
     * Import Amazon Products by ASINs.
     * --------------------------------
     * @param array $params
     * @return array
     * @throws Exception
     */
    protected function importAction($params)
    {
        /** Set Amazon Config */
        $amazonConfig = Mage::helper('amazonia')->getAmazonConfig();

        $data = [];

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

        /** Build Lookup Request */
        $apaiIO = new ApaiIO($conf);
        $search = new Search();

        $search->setResponseGroup(array(
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
        ));


        /** Set Params */
        $search->setCategory($params['category']);
        $search->setKeywords($params['keywords']);

        if (!empty($params['condition'])) {
            $search->setCondition($params['condition']);
        }

        if (!empty($params['merchant'])) {
            $search->setMerchantId($params['merchant']);
        }

        $search->setMinimumPrice(floatval($params['price_min']));
        $search->setMaximumPrice(floatval($params['price_max']));

        $search->setPage($params['page']);

        /** Send Request */
        $formattedResponse = $apaiIO->runOperation($search);


        /** Validate Response */
        /** @var SimpleXMLElement[] $errors */
        $errors = $formattedResponse->Items->Request->Errors->Error ?: null;
        if (!empty($errors)) {

            $message = '';

            /** @var SimpleXMLElement $error */
            foreach ($errors as $error) {
                $message .= $error->Message . " (code: " . $error->Code . ")\n";
            }

            throw new \Exception($message);
        }

        /** Pagination */
        $last = (int)$formattedResponse->Items->TotalPages;

        /** Process Found Products */
        /** @var SimpleXMLElement[] $items */
        $items = $formattedResponse->Items->Item;
        foreach ($items as $item) {

            $item = Mage::helper('amazonia')->xml2array($item);

            /** Check Exists Product */
            if (Mage::getModel('catalog/product')->loadByAttribute('sku', $item['ASIN'])) {
                $data[$item['ASIN']] = [
                    'message' => 'Product was found, but already exists into Magento catalog.'
                ];
            }

            $data[$item['ASIN']] = [
                'data' => $item
            ];
        }

        return [
            'data' => $data,
            'pages' => [
                'current' => $params['page'],
                'last' => $last
            ]
        ];
    }

}