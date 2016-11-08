<?php

class Colibo_Productfilters_Block_Filters extends Mage_Catalog_Block_Layer_View
{
    /**
     *Array of filter params
     *
     * @var array
     */
    public $config;

    /**
     * Constructor
     */
    public function __construct(array $args)
    {
        parent::__construct($args);
        $this->config = [
            'filters' => [
                'price' => [
                    'type' => 'slider',
                    'delimiter' => '-',
                ],
            ]
        ];
    }

    /**
     * Get maximum value of attribute
     *
     * @param   string $attr
     * @return  Mixed
     */
    public function getMaxAttributeVal($attr = 'price')
    {
        return Mage::getResourceModel('catalog/product_collection')
            ->addCategoryFilter(Mage::registry('current_category'))
            ->getMaxAttributeValue($attr);
    }

    /**
     * Get filters for current category
     *
     * @return array
     */
    public function getFilters()
    {
        $filters = array();
        if ($categoryFilter = $this->_getCategoryFilter()) {
            $filters[] = $categoryFilter;
        }

        $filterableAttributes = $this->_getFilterableAttributes();
        foreach ($filterableAttributes as $attribute) {
            $attributeFilter = $this->getChild($attribute->getAttributeCode() . '_filter');
            $attributeFilter->setAttributeCode($attribute->getAttributeCode());
            $filters[] = $attributeFilter;
        }

        /** @var Mage_Catalog_Block_Layer_Filter_Attribute $filter */
        foreach ($filters as $filter) {
            $items = $filter->getItems();
            $filterCode = $filter->getAttributeCode();

            if ($items || isset($this->config['filters'][$filterCode])) {

                $delimiter = isset($this->config['filters'][$filterCode]['delimiter']) ? $this->config['filters'][$filterCode]['delimiter'] : '-';

                if (isset($this->config['filters'][$filterCode])) {
                    switch ($this->config['filters'][$filterCode]['type']) {
                        case 'slider':
                            $filter->setShowOnUse(TRUE);
                            $filter->setDelimiter($delimiter);
                            $filter->setMaxAttributeVal($this->getMaxAttributeVal($filterCode));
                            $filterParam = explode('-', $this->getRequest()->getParam($filterCode));
                            if (count($filterParam) == 2) {
                                $filter->setDataMin($filterParam[0]);
                                $filter->setDataMax($filterParam[1]);
                            } else {
                                $filter->setDataMin(0);
                                $filter->setDataMax($this->getMaxAttributeVal($filterCode));
                            }
                            $filter->setTemplate('catalog/layer/filter/slider.phtml');
                            break;
                        case 'dropdown':
                            $filter->setTemplate('catalog/layer/filter/dropdown.phtml');
                            break;
                        default:
                            break;
                    }
                } else {
                    $filter->setTemplate('catalog/layer/filter/dropdown.phtml');
                }
            }
        }
        return $filters;
    }
}