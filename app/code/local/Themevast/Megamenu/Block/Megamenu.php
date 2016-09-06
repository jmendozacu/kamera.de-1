<?php
class Themevast_Megamenu_Block_Megamenu extends Mage_Catalog_Block_Navigation
{
    // protected $config        = array();
    protected $catMega       = 'megamenu_catid_%d';
    protected $catMegaRight  = 'megamenu_catid_%d_right';
    protected $megamenuExtra = 'megamenu_extra';
    protected $megamenuLink  = 'megamenu_link';

    // protected function _construct()
    // {
    //     parent::_construct();

    //     $this->config = Mage::helper('megamenu')->getGeneralCfg();
    // }

    public function drawMegamenuHome()
    {
        $html ='';
        $showhome = Mage::getStoreConfig('megamenu/general/showhome');
        if($showhome){
            $active = '';
            $homePage = Mage::getStoreConfig('web/default/cms_home_page');
            if(Mage::getSingleton('cms/page')->getIdentifier() == $homePage 
                && Mage::app()->getFrontController()->getRequest()->getRouteName() == 'cms') {
                $active = ' act';  
            }

            $html .= '<div id="megamenu_home" class="megamenu' . $active . '">';
                $html .= '<div class="level-top">';
                $html .= '<a href="'.Mage::helper('core/url')->getHomeUrl().'"><span>' .$this->__('Home'). '</span></a>';
                $html .= '</div>';
                
            $html .= '</div>';
        } 
        return $html;       
    }

    public function drawMegamenuMain()
    {
        $html ='';
        $cats = Mage::helper('catalog/category')->getStoreCategories();
        if(count($cats)){
            $item = 1;
            foreach ($cats as $cat){
                $html .= $this->drawMegamenuItem($cat,0,false,$item);
                $item++;
            }
        }
        return $html;    
    }

    public function drawMegamenuExtra()
    {
        $blockExtra = '';
        $collection = Mage::getModel('cms/block')->getCollection()
                    ->addFieldToFilter('identifier', array('like'=>$this->megamenuExtra.'%'))
                    ->addFieldToFilter('is_active', 1);
        foreach($collection as $block){
            $blockExtra .= $this->drawMegamenuBlock($block->getIdentifier());
        }
        return $blockExtra;
    }

    public function drawMegamenuLink()
    {
        $html = '';
        $blockLink = $this->getLayout()->createBlock('cms/block')->setBlockId($this->megamenuLink)->toHtml();
        if ($blockLink){
            $html .= '<div id="megamenu_link" class="megamenu"><div class="level-top">' .$blockLink. '</div></div>';
        }
        return $html;
    }

    public function drawMegamenuLinks()
    {
        $blockLink = '';
        $collection = Mage::getModel('cms/block')->getCollection()
                    ->addFieldToFilter('identifier', array('like'=>$this->megamenuLink.'%'))
                    ->addFieldToFilter('is_active', 1);
        foreach($collection as $block){
            $blockLink .= $this->drawMegamenuBlock($block->getIdentifier());
        }
        return $blockLink;
    }

    public function drawMegamenuItem($category, $level = 0, $last = false, $item)
    {
        $html = '';
        if (!$category->getIsActive()) return $html;
        $id = $category->getId();

        // --- Static Block ---
        $blockId = sprintf($this->catMega , $id); // --- static block key
        $blockHtml = Mage::app()->getLayout()->createBlock('cms/block')->setBlockId($blockId)->toHtml();
        /*check block right*/
        $blockIdRight = sprintf($this->catMegaRight, $id); // --- static block key

        $blockHtmlRight = Mage::app()->getLayout()->createBlock('cms/block')->setBlockId($blockIdRight)->toHtml();
        if($blockHtmlRight) $blockHtml = $blockHtmlRight;

        // --- Sub Categories ---
        $activeChildren = $this->getActiveChildren($category, $level);

        // --- class for active category ---
        $active = ($this->isCategoryActive($category)) ? ' act' : '';

        // --- Dropdown functions for show ---
        $dropDown = ($blockHtml || count($activeChildren));

        if ($dropDown) $html .= '<div id="megamenu_catid_' . $id . '" class="megamenu' . $active . ' nav-' .$item. '">';
        else $html .= '<div id="megamenu_catid_' . $id . '" class="megamenu' . $active . ' nav-' .$item. ' megamenu_no_child">';

        // --- Top Menu Item ---
        $name = $this->escapeHtml($category->getName());
        $name = str_replace('&nbsp;', '', $name);
        $html .= '<div class="level-top">';
        $html .= '<a href="'.$this->getCategoryUrl($category).'"><span>' . $name . '</span><i class="fa fa-angle-down"></i></a>';
        $label  = Mage::getModel("catalog/category")->load($category->getId())->getCatLabel();
        if($label) $html .= '<span class="'.$label.'">'.$this->__($label).'</span>'; 
        $html .= '</div>';       
        // --- Add Dropdown block (hidden) ---
        if ($dropDown){
            // --- Dropdown function for hide ---
            $html .= '<div id="dropdown' . $id . '" class="dropdown">';
                // --- draw Sub Categories ---
                if (count($activeChildren)){
                    $html .= '<div class="block1" id="block1' . $id . '">';
                        $html .= $this->drawColumns($activeChildren, $id);
                        if ($blockHtml && $blockHtmlRight){
                            $html .= '<div class="column blockright last">' .$blockHtml. '</div>';
                        }
                        $html .= '<div class="clearBoth"></div>';
                    $html .= '</div>';
                }
                // --- draw Custom User Block ---
                if ($blockHtml && !$blockHtmlRight){
                    $html .= '<div class="block2" id="block2' . $id . '">' .$blockHtml. '</div>';
                }
            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    }

    public function drawColumns($children, $id)
    {
        $html = '';
        // --- explode by columns ---
        $columns = (int)Mage::getStoreConfig('megamenu/general/count');
        if ($columns < 1) $columns = 1;
        $chunks = $this->explodeByColumns($children, $columns);
        $columChunk = count($chunks);
        // --- draw columns ---
        $classSpecial = '';
        $keyLast = 0;
        foreach ($chunks as $key => $value){
            if(count($value)) $keyLast++;
        }
        $blockHtml = '';
        $blockId = sprintf($this->catMega, $id); // --- static block key
        $blockHtml = Mage::app()->getLayout()->createBlock('cms/block')->setBlockId($blockId)->toHtml();
        /*Check blog right*/
        $blockIdRight = sprintf($this->catMegaRight, $id); // --- static block key
        $blockHtmlRight = Mage::app()->getLayout()->createBlock('cms/block')->setBlockId($blockIdRight)->toHtml();
        if($blockHtmlRight) $blockHtml = $blockHtmlRight;
        foreach ($chunks as $key => $value)
        {
            if (!count($value)) continue;
            if($key == $keyLast - 1){
                $classSpecial = ($blockHtmlRight && $blockHtml)? '':' last';
            }elseif($key == 0){
                $classSpecial = ' first';
            }else{
                $classSpecial = '';
            }
            $html .= '<div class="column'. $classSpecial . ' col' . ($key+1) . '">' .$this->drawMenuItem($value, 1, $columChunk). '</div>';
        }
        return $html;
    }

    protected function getActiveChildren($parent, $level)
    {
        $activeChildren = array();
        // --- check level ---
        $maxLevel = (int)Mage::getStoreConfig('megamenu/general/max_level');
        if ($maxLevel > 0)
        {
            if ($level >= ($maxLevel - 1)) return $activeChildren;
        }
        // --- / check level ---
        if (Mage::helper('catalog/category_flat')->isEnabled()){
            $children = $parent->getChildrenNodes();
            $childrenCount = count($children);
        }else {
            $children = $parent->getChildren();
            $childrenCount = $children->count();
        }
        $hasChildren = $children && $childrenCount;
        if ($hasChildren){
            foreach ($children as $child){
                if ($child->getIsActive()) array_push($activeChildren, $child);
            }
        }
        return $activeChildren;
    }

    private function explodeByColumns($target, $num)
    {
        $countChildren = 0;
        foreach ($target as $cat => $childCat){
            $activeChildCat = $this->getActiveChildren($childCat, 0);
            if($activeChildCat) $countChildren++;
        }
        if($countChildren == 0) $num = 1;

        $count = count($target);
        if ($count) $target = array_chunk($target, ceil($count / $num));
        
        $target = array_pad($target, $num, array());
         
        if ((int)Mage::getStoreConfig('megamenu/general/integrate') && count($target)){
            // --- combine consistently numerically small column ---
            // --- 1. calc length of each column ---
            $max = 0; $columnsLength = array();
            foreach ($target as $key => $child){
                $count = 0;
                $this->_countChild($child, 1, $count);
                
                if ($max < $count) $max = $count;
                $columnsLength[$key] = $count;
            }
            
            // --- 2. merge small columns with next ---
            $xColumns = array(); $column = array(); $cnt = 0;
            $xColumnsLength = array(); $k = 0;
            
            foreach ($columnsLength as $key => $count){
                $cnt+= $count;
                if ($cnt > $max && count($column))
                {
                    $xColumns[$k] = $column;
                    $xColumnsLength[$k] = $cnt - $count;
                    $k++; $column = array(); $cnt = $count;
                }
                $column = array_merge($column, $target[$key]);
            }
            $xColumns[$k] = $column;
            $xColumnsLength[$k] = $cnt - $count;
            // --- 3. integrate columns of one element ---
            $target = $xColumns; $xColumns = array(); $nextKey = -1;
            if ($max > 1 && count($target) > 1){
                foreach($target as $key => $column){
                    if ($key == $nextKey) continue;
                    if ($xColumnsLength[$key] == 1){
                        // --- merge with next column ---
                        $nextKey = $key + 1;
                        if (isset($target[$nextKey]) && count($target[$nextKey]))
                        {
                            $xColumns[] = array_merge($column, $target[$nextKey]);
                            continue;
                        }
                    }
                    $xColumns[] = $column;
                }
                $target = $xColumns;
            }
        }
        return $target;
    }

    private function _countChild($children, $level, &$count)
    {
        foreach ($children as $child){
            if ($child->getIsActive()){
                $count++; $activeChildren = $this->getActiveChildren($child, $level);
                if (count($activeChildren) > 0) $this->_countChild($activeChildren, $level + 1, $count); 
            }
        }
    }

    public function drawMenuItem($children, $level = 1, $columChunk=null)
    {
        $html = '<div class="itemMenu level' . $level . '">';
        $keyCurrent = $this->getCurrentCategory()->getId();
        $countChildren = 0;
        $ClassNoChildren = '';
        foreach ($children as $child){
            $activeChildCat = $this->getActiveChildren($child, 0);
            if($activeChildCat) $countChildren++;
        }
        if($countChildren == 0 && $columChunk == 1){ 
            $ClassNoChildren = ' nochild'; 
        }
        $catsid = Mage::getStoreConfig('megamenu/general/catids');
        $arr_catsid = array();
        if($catsid){    
            if(stristr($catsid, ',') === FALSE) $arr_catsid =  array(0 => $catsid);
            else $arr_catsid = explode(",", $catsid);
        }

        foreach ($children as $child){
            if ($child->getIsActive()){
                $active = '';
                if ($this->isCategoryActive($child)){
                    $active = ' actParent';
                    if ($child->getId() == $keyCurrent) $active = ' act';
                }
                
                // --- format category name ---
                $name = $this->escapeHtml($child->getName());
                $name = str_replace(' ', '&nbsp;', $name);
                if( in_array($child->getId(),$arr_catsid) ){
                    $html .= '<h4 class="itemMenuName level' . $level . $active . $ClassNoChildren . '"><span>' . $name . '</span></h4>';
                }else{
                    $html .= '<a class="itemMenuName level' . $level . $active . $ClassNoChildren . '" href="' . $this->getCategoryUrl($child) . '"><span>' . $name . '</span></a>';
                }
                $activeChildren = $this->getActiveChildren($child, $level);
                if (count($activeChildren) > 0){
                    $html .= '<div class="itemSubMenu level' . $level . '">' .$this->drawMenuItem($activeChildren, $level + 1). '</div>';
                }
            }
        }
        $html .= '</div>';
        return $html;
    }
    
    public function drawMegamenuBlock($blockId)
    {

        $html = '';
        $block = Mage::getModel('cms/block')->setStoreId(Mage::app()->getStore()->getId())->load($blockId);

        $blockHtml = Mage::app()->getLayout()->createBlock('cms/block')->setBlockId($blockId)->toHtml();
        $dropDown = $blockHtml;
        if ($dropDown) $html .= '<div id="'. $blockId . '" class="megamenu">';
        else $html .= '<div id="' . $blockId . '" class="megamenu">';

        $name = $block->getTitle();
        $name = str_replace(' ', '&nbsp;', $name);
        $html .= '<div class="level-top"><span class="block-title">' . $name . '<i class="fa fa-angle-down"></i></span></div>';

        // --- Add Dropdown block (hidden) ---
        if ($dropDown){
            $html .= '<div id="dropdown' . $blockId . '" class="dropdown cmsblock" style=" width: 904px;">';
            if ($blockHtml) $html .= '<div class="block2" id="block2' . $blockId . '">' .$blockHtml. '</div>';
            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    }
}

