<?php
/**
 * This file was generated by the ConvertToLegacy class in bronto-legacy.
 * The purpose of the conversion was to maintain PSR-0 compliance while
 * the main development focuses on modern styles found in PSR-4.
 *
 * For the original:
 * @see src/Bronto/Magento/Core/Catalog/ProductCategoryResolverInterface.php
 */

interface Brontosoftware_Magento_Core_Catalog_ProductCategoryResolverInterface
{
    /**
     * Gets the display information on a product category
     *
     * @param mixed $product
     * @param string $resolver
     * @return string
     */
    public function getCategory($product, $resolver = 'single');
}
