<?php

require_once(Mage::getBaseDir() . '/vendor/autoload.php');

use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\BrowseNodeLookup;
use ApaiIO\Operations\Search;
use ApaiIO\ApaiIO;


/**
 * Class Colibo_Amazonia_Adminhtml_AmazonController
 */
class Colibo_Amazonia_Adminhtml_AmazonController extends Mage_Adminhtml_Controller_Action
{


    /**
     * Prepare Amazon Nodes.
     * --------------------
     */
    public function prepareAmazonNodes()
    {
        $results = [
            'id' => '/',
            'icon' => 'folder',
            'state' => [
                'opened' => true,
                'disabled' => true,
            ],
            'text' => 'Amazon.de'
        ];

        $amazonRootNodes = Mage::helper('amazonia')->getAmazonRootNodes();
        foreach ($amazonRootNodes as $nodeId => $amazonRootNode) {
            foreach ($amazonRootNode as $searchIndex => $localeName) {

                $results['children'][] = [
                    'id' => $nodeId,
                    'icon' => 'folder',
                    'state' => [
                        'opened' => false,
                        'disabled' => false,
                    ],
                    'text' => $localeName,
                    'children' => true
                ];
            }
        }

        return $results;
    }


    /**
     * Get Amazon Nodes.
     * -----------------
     */
    public function nodeAction()
    {

        try {

            /** Get Request */
            $response = [];
            $nodeId = trim($this->getRequest()->getParam('id', null));

            if ($nodeId == '#') {

                /** Get Amazon Root Nodes */
                $response = $this->prepareAmazonNodes();

            } else {

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

                /** Build Lookup Request */
                $apaiIO = new ApaiIO($conf);
                $browseNodeLookup = new BrowseNodeLookup();
                $browseNodeLookup->setNodeId((int)$nodeId);

                $formattedResponse = $apaiIO->runOperation($browseNodeLookup);

                /** Validate Response */
                /** @var SimpleXMLElement[] $errors */
                $childrenNodes = $formattedResponse->BrowseNodes->BrowseNode->Children->BrowseNode ?: [];
                foreach ($childrenNodes as $childrenNode) {

                    /** Validation */
                    if (empty($childrenNode->BrowseNodeId) || empty($childrenNode->Name)) {
                        throw new \Exception('error..');
                    }

                    $response[] = [
                        'id' => !empty($childrenNode->BrowseNodeId) ? current($childrenNode->BrowseNodeId) : 0,
                        'icon' => 'folder',
                        'state' => [
                            'opened' => false,
                            'disabled' => false,
                        ],
                        'text' => !empty($childrenNode->Name) ? current($childrenNode->Name) : 'ERROR',
                        'children' => true
                    ];
                }
            }

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
     * Search Products by Query.
     * -------------------------
     */
    public function searchAction()
    {

        try {

            $this->loadLayout();

            /** Get Request */
            $page = trim($this->getRequest()->getParam('page', 1));
            $mode = trim($this->getRequest()->getParam('mode', null));
            $sort = trim($this->getRequest()->getParam('sort', null));
            $node = trim($this->getRequest()->getParam('node', null));
            $brand = trim($this->getRequest()->getParam('brand', null));
            $category = trim($this->getRequest()->getParam('category'));
            $keywords = trim($this->getRequest()->getParam('keywords'));
            $merchant = trim($this->getRequest()->getParam('merchant', null));
            $condition = trim($this->getRequest()->getParam('condition', null));
            $minPrice = $this->getRequest()->getParam('min_price');
            $maxPrice = $this->getRequest()->getParam('max_price');

            /** Prepare Params */
            $params = [
                'page' => $page,
                'node' => $node,
                'sort' => $sort,
                'brand' => $brand,
                'keywords' => $keywords,
                'category' => $category,
                'merchant' => $merchant,
                'condition' => $condition,
                'price_min' => $minPrice,
                'price_max' => $maxPrice,
            ];

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
                    'title' => $e->getCode() != 911 ? "Error Code: " . $e->getCode() : 'Amazon.de',
                    'message' => $e->getCode() == 503 ? 'API Limit. Wait a moment and try again.' : strip_tags($e->getMessage()),
                    'trace' => $e->getCode() != 911 ? $e->getFile() . ":" . $e->getLine() : null
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
        /** Init Resources */
        $resource = Mage::getSingleton('core/resource');
        $dbWrite = $resource->getConnection('core_write');
        $table = $resource->getTableName('colibo_products_jobs');

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

        if (!empty($params['sort'])) {
            $search->setSort($params['sort']);
        }

        if (!empty($params['brand'])) {
            $search->setBrand($params['brand']);
        }

        if (!empty($params['node'])) {
            $search->setBrowseNode(intval($params['node']));
        }

        if (!empty($params['condition'])) {
            $search->setCondition($params['condition']);
        }

        if (!empty($params['merchant'])) {
            $search->setMerchantId($params['merchant']);
        }

        $search->setKeywords($params['keywords']);
        $search->setMinimumPrice(floatval($params['price_min'] * 100));
        $search->setMaximumPrice(floatval($params['price_max'] * 100));
        $search->setCategory($params['category']);
        $search->setPage($params['page']);

        /** Send Request */
        $formattedResponse = $apaiIO->runOperation($search);


        /** Validate Response */
        /** @var SimpleXMLElement[] $errors */
        $errors = $formattedResponse->Items->Request->Errors->Error ?: null;
        if (!empty($errors)) {

            $message = '';
            $code = 0;

            /** @var SimpleXMLElement $error */
            foreach ($errors as $error) {

                switch ($error->Code) {
                    case 'AWS.ECommerceService.NoExactMatches':
                        $message .= "Your search did not match any products\n";
                        $code = 911;
                        break;

                    default:
                        $message .= $error->Message . " (code: " . $error->Code . ")\n";
                        break;
                }

            }

            throw new \Exception($message, $code);
        }

        /** Pagination */
        $last = (int)$formattedResponse->Items->TotalPages;

        /** Process Found Products */
        /** @var SimpleXMLElement[] $items */
        $items = $formattedResponse->Items->Item;
        foreach ($items as $item) {

            $item = Mage::helper('amazonia')->xml2array($item);

            /** Check Exists Job */
            $query = "SELECT * FROM " . $table . " WHERE amazon_asin = '" . $item['ASIN'] . "' LIMIT 0, 1;";
            $existJobs = $dbWrite->query($query)->fetchAll();

            if (count($existJobs)) {
                $data[$item['ASIN']]['message']['warning'] = 'Product was added to queue for import.';
            } else if (Mage::getModel('catalog/product')->loadByAttribute('sku', $item['ASIN'])) {

                /** Check Exists Product */
                $data[$item['ASIN']]['message']['success'] = 'Product already imported to Magento.';
            }

            $data[$item['ASIN']]['data'] = $item;
        }


        return ['data' => $data,
            'pages' => [
                'current' => $params['page'],
                'last' => $last
            ]
        ];
    }

}