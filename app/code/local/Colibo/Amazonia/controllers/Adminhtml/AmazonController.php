<?php

require_once(Mage::getBaseDir() . '/vendor/autoload.php');

use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Lookup;
use ApaiIO\Operations\Search;
use GuzzleHttp\Client;
use ApaiIO\ApaiIO;


/**
 * Class Colibo_Amazonia_Adminhtml_AmazonController
 */
class Colibo_Amazonia_Adminhtml_AmazonController extends Mage_Adminhtml_Controller_Action
{

    const AWS_LAST_PAGE_PATTERN = '//*[@id="pagn"]//*[@class="pagnDisabled"]/text()';
    const AWS_CURRENT_PAGE_PATTERN = '//*[@id="pagn"]//*[@class="pagnCur"]/text()';
    const AWS_LAST_PAGE_PATTERN_RESERVE = '//*[@id="pagn"]//*[@class="pagnLink"][last()]//a/text()';
    const AWS_TITLE = '//title/text()';


    /**
     * Search Products by Query.
     * -------------------------
     */
    public function searchAction()
    {
        try {

            $data = [];
            $this->loadLayout();

            /** Get Request Mode*/
            $mode = trim($this->getRequest()->getParam('mode'));

            /** Get Request Query */
            $query = trim($this->getRequest()->getParam('query'));
            if (empty($query)) {
                throw new \Exception('Sir.. Enter direct ASIN or Amazon Url with products listing..');
            }

            /** Detect Search Mode */
            if (preg_match('/https:\/\/www.amazon.de/i', $query)) {

                if (!empty($mode) && $mode == 'next') {
                    parse_str($query, $amazonUrlChunks);
                    if (!empty($amazonUrlChunks['page'])) {
                        $page = $amazonUrlChunks['page'];
                        $query = str_replace('page=' . $page, 'page=' . ++$page, $query);
                    }
                }

                $grabData = $this->grabAction($query);
                $asins = $grabData['asins'];
            } else {
                $asins = explode(' ', $query);
            }


            /** Amazon API limit: 10 ASIN per Request */
            foreach ($asins as $asin) {
                $data = array_merge($data, $this->importAction($asin));
            }

            /** Get Magento Lists */
            $categories = Mage::helper('amazonia')->getCategories();
            $attributeSets = Mage::helper('amazonia')->getAttributeSets();

            /** Set Product List */
            $listBlock = Mage::app()->getLayout()->getBlock('amazon_products_list');
            $listBlock
                ->setResults($data)
                ->setAttributeSets($attributeSets)
                ->setCategories($categories);

            /** Set Amazon Pagination */
            $paginationBlock = Mage::app()->getLayout()->getBlock('amazon_pagination');
            $paginationBlock
                ->setPagination(!empty($grabData['pages']) ? $grabData['pages'] : [])
                ->setPageTitle(!empty($grabData['page_title']) ? $grabData['page_title'] : 'Search results');

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
                'data' => $data,
                'html' => $html,
                'query' => $query,
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
     * @param string $asin
     * @return array
     * @throws Exception
     */
    protected
    function importAction($asin)
    {
        /** Check Exists Product */
        /** @noinspection PhpUndefinedMethodInspection */
        if (Mage::getModel('catalog/product')->loadByAttribute('sku', $asin)) {
            return [$asin => ['message' => 'Product was found, but already exists into Magento catalog.']];
        }

        /** Set Amazon Config */
        $amazonConfig = Mage::helper('amazonia')->getAmazonConfig();

        $data = [];
        try {

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
            $lookup = new Lookup();
            $lookup->setItemId($asin);
            $lookup->setResponseGroup(array(
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

            /** Send Request */
            $formattedResponse = $apaiIO->runOperation($lookup);

        } catch (\Exception $e) {
            return [$asin => ['error' => $e->getMessage()]];
        }

        /** Validate Response */
        /** @var SimpleXMLElement[] $errors */
        $errors = $formattedResponse->Items->Request->Errors->Error ?: null;
        if (!empty($errors)) {

            $message = '';

            /** @var SimpleXMLElement $error */
            foreach ($errors as $error) {
                $message .= $error->Message . " (code: " . $error->Code . ")\n";
            }

            return [$asin => ['error' => $message]];
        }

        /** Process Found Products */
        /** @var SimpleXMLElement[] $items */
        $items = $formattedResponse->Items->Item;
        foreach ($items as $item) {
            $item = Mage::helper('amazonia')->xml2array($item);
            $data[$item['ASIN']] = ['data' => $item];
        }

        return $data;
    }


    /**
     * Grab ASINs by URL.
     * ------------------
     * @param string $url
     * @return array
     * @throws Exception
     */
    protected
    function grabAction($url)
    {
        $asins = [];

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
        $links = $xpath->query("//a/@href");

        if (empty($links)) {
            throw new Exception('Search results: no products found');
        }

        foreach ($links as $link) {

            $url = trim($link->nodeValue);
            if (strpos($url, '/dp/')) {

                /** Parse Valid ASINs */
                $pattern = "/\\/dp\\/([A-Za-z0-9]*)\\//";
                if (preg_match($pattern, $url, $matches)) {
                    $asin = next($matches);
                } else {
                    $chunks = explode('dp/', $url);
                    $asin = end($chunks);
                }

                /** Remove Trash Items */
                if (mb_strlen($asin, 'UTF-8') > 10) continue;
                $asins[] = $asin;
            }
        }

        /** Pagination: current page */
        $currentPageNode = $xpath->query(self::AWS_CURRENT_PAGE_PATTERN);
        $currentPage = count($currentPageNode) ? $currentPageNode->item(0)->nodeValue : false;

        /** Pagination: last page */
        $lastPageNode = $xpath->query(self::AWS_LAST_PAGE_PATTERN);
        $lastPage = count($lastPageNode) ? $lastPageNode->item(0)->nodeValue : false;

        if (!$lastPage) {
            $lastPageNode = $xpath->query(self::AWS_LAST_PAGE_PATTERN_RESERVE);
            $lastPage = count($lastPageNode) ? $lastPageNode->item(0)->nodeValue : false;
        }

        /** Title */
        $titleNode = $xpath->query(self::AWS_TITLE);
        $pageTitle = count($titleNode) ? $titleNode->item(0)->nodeValue : false;
        $pageTitle = str_replace('Amazon.de:', '', $pageTitle);

        /** Process Found Products */
        return [
            'asins' => array_values(array_unique($asins)),
            'page_title' => trim($pageTitle),
            'pages' => [
                'current' => $currentPage,
                'last' => $lastPage < $currentPage ? $currentPage : $lastPage
            ]
        ];
    }

}