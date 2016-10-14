<?php

class Colibo_Similarproducts_Block_Productlist extends Mage_Catalog_Block_Product_View
{
    public function getSimilarProducts()
    {
        $product = $this->getProduct();
        $amazonAttributes = json_decode(Mage::getResourceModel('catalog/product')->getAttributeRawValue($product->getId(), 'amazon_attributes'));
        $amazonProducts = $amazonAttributes->SimilarProducts->SimilarProduct;

        $products = [];

        while(count($products) < 5 && ($amazonProduct = array_pop($amazonProducts)) !== null)
        {

            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $amazonProduct->ASIN);
            if($product !== false)
            {
                $products[] = $product;
            }
        }

        return $products;
    }
}