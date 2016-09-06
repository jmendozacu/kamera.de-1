<?php
class Themevast_Themevast_Helper_Labels extends Mage_Core_Helper_Abstract
{	
	protected $newcfg;
	protected $salecfg;
	protected $percent;
	protected $connect = false;
	public function getLabels($product)
	{
		$html  = '';
		if(!$this->connect){
			$this->newcfg  = Mage::getStoreConfig('themevast/general/new_label');
			$this->salecfg  = Mage::getStoreConfig('themevast/general/sale_label');
			$this->percent  = Mage::getStoreConfig('themevast/general/sale_percent');
			$this->connect = true;
		}
		$isNew = $this->newcfg ? $this->isNew($product) : '';

		$isSale = $this->salecfg ? $this->isOnSale($product) : '';
		if($isNew){
			$html .= '<span class="icon-new">' . $this->__('New') . '</span>';			
		}
		if($isSale){
			$price = $product->getPrice();
			$finalPrice = $product->getFinalPrice();
			$label = $this->percent ? floor(($finalPrice/$price)*100 - 100).' %' : $this->__('Sale');

			$html .= '<span class="icon-sale">' . $label . '</span>';
		}
		
		return $html;
	}

	public function isNew($product)
	{
		return $this->_nowIsBetween($product->getData('news_from_date'), $product->getData('news_to_date'));
	}

	public function isOnSale($product)
	{
		$specialPrice = number_format($product->getFinalPrice(), 2);
		$regularPrice = number_format($product->getPrice(), 2);
		
		if ($specialPrice != $regularPrice)
			return $this->_nowIsBetween($product->getData('special_from_date'), $product->getData('special_to_date'));
		else
			return false;
	}
	
	protected function _nowIsBetween($fromDate, $toDate)
	{
		if ($fromDate)
		{
			$fromDate = strtotime($fromDate);
			$toDate = strtotime($toDate);
			$now = strtotime(date("Y-m-d H:i:s"));
			
			if ($toDate)
			{
				if ($fromDate <= $now && $now <= $toDate)
					return true;
			}
			else
			{
				if ($fromDate <= $now)
					return true;
			}
		}
		
		return false;
	}
}
