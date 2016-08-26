<?php

/**
 * Class Colibo_Amazonia_Helper_Data
 */
class Colibo_Amazonia_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * @param $data
     * @return string
     */
    public function generateArrayTree($data)
    {
        $tree = '<ul>';
        foreach ($data as $code => $item) {
            $tree .= '<li>';
            if (!is_array($item) && preg_match('/http/i', $item)) {
                $item = '<a href="' . $item . '" target="_blank">'.$item.'</a>';
            }
            $tree .= !is_int($code) ? $code : '';
            if (is_array($item)) {
                $tree .= $this->generateArrayTree($item);
            } else {
                $tree .= (!is_int($code) ? ': ' : '') . $item;
            }
            $tree .= '</li>';
        }
        $tree .= '</ul>';
        return $tree;
    }
}