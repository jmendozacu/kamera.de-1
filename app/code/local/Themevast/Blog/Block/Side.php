<?php

class Themevast_Blog_Block_Side extends Themevast_Blog_Block_Abstract
{
    public function getRecent()
    {
        // widget declaration
        if ($this->getBlogWidgetRecentCount()) {
            $size = $this->getBlogWidgetRecentCount();
        } else {
            // standard output
            $size = self::$_helper->getRecentPage();
        }

        if ($size) {
            $collection = clone self::$_collection;
            $collection->setPageSize($size);

            foreach ($collection as $item) {
                $item->setAddress($this->getBlogUrl($item->getIdentifier()));
            }
            return $collection;
        }
        return false;
    }

    public function getCategories()
    {
        $collection = Mage::getModel('blog/cat')
            ->getCollection()
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->setOrder('sort_order', 'asc')
        ;
        foreach ($collection as $item) {
            $item->setAddress($this->getBlogUrl(array(self::$_catUriParam, $item->getIdentifier())));
        }
        return $collection;
    }

    public function getContentBlogSidebar($_description, $count) {
       $short_desc = substr($_description, 0, $count);
       if(substr($short_desc, 0, strrpos($short_desc, ' '))!='') {
            $short_desc = substr($short_desc, 0, strrpos($short_desc, ' '));
            $short_desc = $short_desc.'...';
        }
       return $short_desc;
    }

    protected function _beforeToHtml()
    {
        return $this;
    }
}
