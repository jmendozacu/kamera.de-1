<?php
class Themevast_Newproduct_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $title = $this->__('Newproduct');
        $this->loadLayout();
        $this->getLayout()->getBlock("head")->setTitle($title);
        $this->renderLayout();
    }

}
