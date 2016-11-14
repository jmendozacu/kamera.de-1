<?php

class Colibo_Colorvariants_Block_Colors extends Mage_Core_Block_Template
{
    /**
     * Get collection of products different color variants
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getVariants()
    {
        $product = $this->getProduct();
        $products = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('*')
            ->addFieldToFilter([
                ['attribute' => 'parent_asin', 'eq' => $product->getParentAsin()],
            ])
            ->addFieldToFilter([
                ['attribute' => 'entity_id', 'neq' => $product->getId()],
            ]);
        return $products;
    }

    /**
     * Retrieve current product model
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if (!Mage::registry('product') && $this->getProductId()) {
            $product = Mage::getModel('catalog/product')->load($this->getProductId());
            Mage::register('product', $product);
        }
        return Mage::registry('product');
    }
}