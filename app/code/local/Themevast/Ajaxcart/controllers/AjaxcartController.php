<?php
require_once "Mage/Checkout/controllers/CartController.php";
class Themevast_Ajaxcart_AjaxcartController extends Mage_Checkout_CartController
{
    public function addAction()
    {
        $cart   = $this->_getCart();
        $ajaxData = array();
        $productInfo = array();
        $params = $this->getRequest()->getParams();
         
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }
            
            $product = $this->_initProduct();
            if(isset($params['type_product']) && $params['type_product'] ==1) {
                $productInfo['type_product'] = $product->getTypeId();
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($productInfo));
                 return ;
            }
            $related = $this->getRequest()->getParam('related_product');
            
            /**
             * Check product availability
             */
            if (!$product) {
                	$ajaxData['status'] = 0;
                    $ajaxData['message'] = $this->__('Unable to find Product ID');
            }


            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()){
                    $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                   // $this->_getSession()->addSuccess($message);
                    $ajaxData['status'] = 1;
                    $this->loadLayout();
                    $sidebarCart = "";
                    $mini_cart = "";
                    $toplink = "";
                    if ($this->getLayout()->getBlock('cart_sidebar')) {
                        $sidebarCart = $this->getLayout()->getBlock('cart_sidebar')->toHtml();
                    }
                    if ($this->getLayout()->getBlock('cart_sidebar_mini')) {
                        $mini_cart = $this->getLayout()->getBlock('cart_sidebar_mini')->toHtml();
                    }
                    if ($this->getLayout()->getBlock('top.links')) {
                        $toplink = $this->getLayout()->getBlock('top.links')->toHtml();
                    }
                    $pimage = Mage::helper('catalog/image')->init($product, 'small_image')->resize(55,83);
                    $ajaxData['sidebar_cart'] = $sidebarCart;
                    $ajaxData['top_link'] = $toplink;
                    $ajaxData['mini_cart'] = $mini_cart;
                    $proInfo = '<div class ="ajaxcartReponse">
                                    <p class ="info">'.$this->__('Item was added to your cart'). '</p>
                                </div>';

                    $ajaxData['product_info'] = $proInfo;
                }
            }
          
        } catch (Mage_Core_Exception $e) {
            
            $msg = "";
            if ($this->_getSession()->getUseNotice(true)) {
                $msg = $e->getMessage();
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $msg .= $message . '<br/>';
                }
            }
            $ajaxData['status'] = 0;
            $ajaxData['message'] = $msg;
            $ajaxData['type_product_ajax'] = 1;
            
        } catch (Exception $e) {
            $ajaxData['status'] = 0;
            $ajaxData['message'] = $this->__('Cannot add the this product to shopping cart.');
        }
       $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($ajaxData));
    }
     /**
     * override Delete shoping cart item action
     */
    public function deleteAction() {
        $id = (int) $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->_getCart()->removeItem($id)
                        ->save();
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('Cannot remove the item.'));
                Mage::logException($e);
            }
        }
        $this->loadLayout();
        $sidebarCart = "";
        $mini_cart = "";
        $toplink = "";
        if($this->getLayout()->getBlock('cart_sidebar')) {
            $sidebarCart = $this->getLayout()->getBlock('cart_sidebar')->toHtml();
        }
        if($this->getLayout()->getBlock('cart_sidebar_mini')){
            $mini_cart =  $this->getLayout()->getBlock('cart_sidebar_mini')->toHtml();
        }
        if($this->getLayout()->getBlock('top.links')){
            $toplink = $this->getLayout()->getBlock('top.links')->toHtml();
        }

        $cartEmpty = '';
        if($this->getRequest()->getParam('type_del')=='cart' & $this->getRequest()->getParam('count_remove')==1) {
           $cartEmpty = Mage::helper('ajaxcart/data')->getEmptyCartHtml();
        }
        $ajaxData['status'] = 1;
        $ajaxData['top_link'] = $toplink;
        $ajaxData['sidebar_cart'] = $sidebarCart;
        $ajaxData['checkout_cart'] = $cartEmpty;
        $ajaxData['mini_cart'] = $mini_cart;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($ajaxData));
    }

    public function cartdeleteAction() { 
      
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                Mage::getSingleton('checkout/cart')->removeItem($id)
                        ->save();
            } catch (Exception $e) {
                Mage::getSingleton('checkout/session')->addError($this->__('Cannot remove item'));
            }
        }           
        $this->loadLayout();
        $ajaxData = array();
        $miniCart = $this->getLayout()->getBlock('cart_sidebar_mini');
        if($miniCart) {
            $miniCart = $miniCart->toHtml();
        }
        $formCart = $this->getLayout()->getBlockSingleton('checkout/cart')->setTemplate("checkout/cart.phtml")->toHtml();
        $ajaxData['form_cart']  = $formCart;
        $ajaxData['top_cart'] = $miniCart;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($ajaxData));
 
    }
    
    public function updateItemOptionsAction()
    {
        $ajaxData = array();
        $productInfo = array();
        $cart   = $this->_getCart();
        $id = (int) $this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();

        if (!isset($params['options'])) {
            $params['options'] = array();
        }
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $quoteItem = $cart->getQuote()->getItemById($id);
            if (!$quoteItem) {
                Mage::throwException($this->__('Quote item is not found.'));
            }

            $item = $cart->updateItem($id, new Varien_Object($params));
            if (is_string($item)) {
                Mage::throwException($item);
            }
            if ($item->getHasError()) {
                Mage::throwException($item->getMessage());
            }

            $related = $this->getRequest()->getParam('related_product');
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            Mage::dispatchEvent('checkout_cart_update_item_complete',
                array('item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );
            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()){
                    $message = $this->__('%s was updated in your shopping cart.', Mage::helper('core')->htmlEscape($item->getProduct()->getName()));
                   // $this->_getSession()->addSuccess($message);
                }
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError($message);
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
 
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot update the item.'));
            Mage::logException($e);
        }
        $this->loadLayout();
        if ($this->getLayout()->getBlock('cart_sidebar')) {
            $sidebarCart = $this->getLayout()->getBlock('cart_sidebar')->toHtml();
        }
        if ($this->getLayout()->getBlock('cart_sidebar_mini')) {
            $mini_cart = $this->getLayout()->getBlock('cart_sidebar_mini')->toHtml();
        }
        if ($this->getLayout()->getBlock('top.links')) {
            $toplink = $this->getLayout()->getBlock('top.links')->toHtml();
        }
        $ajaxData['status'] = 1;
        $ajaxData['top_link'] = $toplink;
        $ajaxData['sidebar_cart'] = $sidebarCart;
        $ajaxData['mini_cart'] = $mini_cart;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($ajaxData));
    }
     /**
     * override Delete shoping cart item action
     */ 
    
    public function updatePostAction() {

        $updateAction = (string) $this->getRequest()->getParam('update_cart_action');

        switch ($updateAction) {
            case 'empty_cart':
                $this->_emptyShoppingCart();
                break;
            case 'update_qty':
                $this->_updateShoppingCart();
                break;
            default:
                $this->_updateShoppingCart();
        }
        $this->loadLayout();
        $updateAction = $this->getRequest()->getParams();

        $toplink = $this->getLayout()->getBlock('top.links')->toHtml();
        $cartEmpty = Mage::helper('ajaxcart/data')->getEmptyCartHtml();
        $ajaxData['status'] = 1;
        $ajaxData['top_link'] = $toplink;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($ajaxData)); 
    }

}

