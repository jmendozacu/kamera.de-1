<?php

class Colibo_Similarproducts_Block_Productlist extends Mage_Catalog_Block_Product_View
{
    public function getSimilarProducts()
    {
        $product = $this->getProduct();
        $amazonAttributes = json_decode($product->getAmazonAttributes());
        $amazonProducts = (isset($amazonAttributes->SimilarProducts->SimilarProduct)) ? $amazonAttributes->SimilarProducts->SimilarProduct : [];

        $productsSku = [];
        foreach ($amazonProducts as $amazonProduct) {
            $productsSku[] = $amazonProduct->ASIN;
        }

        $productCollection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('sku', array('in' => $productsSku))
            ->setPageSize(5)
            ->setCurPage(1);

        return $productCollection;
    }
}