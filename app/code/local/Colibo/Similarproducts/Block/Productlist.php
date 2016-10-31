<?php

class Colibo_Similarproducts_Block_Productlist extends Mage_Catalog_Block_Product_View
{
    public function getSimilarProducts()
    {
        $product = $this->getProduct();
        $amazonAttributes = json_decode($product->getAmazonAttributes(),true);
        $amazonProducts = (isset($amazonAttributes['SimilarProducts']['SimilarProduct'])) ? $amazonAttributes['SimilarProducts']['SimilarProduct'] : [];

        $productSkus = array_column($amazonProducts,'ASIN');

        $productCollection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('sku', array('in' => $productSkus))
            ->setPageSize(5)
            ->setCurPage(1);

        return $productCollection;
    }
}