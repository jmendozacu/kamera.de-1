<?php

class Colibo_Colorvariants_Adminhtml_ProductsController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Initializes ParentASIN attribute of existing products
     */
    public function updateAttributesAction()
    {
        set_time_limit(0);
        $collection = Mage::getModel('catalog/product')->getCollection()

            ->addAttributeToSelect('*');
        foreach($collection as $product)
        {
            $amazonAttributes = json_decode($product->getAmazonAttributes(),true);
            if(isset($amazonAttributes['ParentASIN']))
            {
                $product->setParentAsin($amazonAttributes['ParentASIN'])->save();
                echo $product->getId()."<br/>";
            }
        }
    }
}
?>