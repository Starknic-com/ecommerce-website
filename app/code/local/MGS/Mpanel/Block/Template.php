<?php

class MGS_Mpanel_Block_Template extends Mage_Core_Block_Template {

    protected $_activeLink = false;
    
    public function getBlock() {
        if ($this->getRequest()->getParam('category_id')) {
            $currentCategoryId = $this->getRequest()->getParam('category_id');
        } else {
            $layer = Mage::getSingleton('catalog/layer');
            $category = $layer->getCurrentCategory();
            $currentCategoryId = $category->getId();
        }
        $currentProductId = 0;
        $parts = parse_url(Mage::helper('core/url')->getCurrentUrl());
        $arr = explode('/', $parts['path']);
        $urlPath = end($arr);
        $collection = Mage::getModel('core/url_rewrite')->getCollection()
                ->addFieldToFilter('request_path', array('like' => '%' . $urlPath . '%'));
        foreach ($collection as $r) {
            if (strpos($parts['path'], $r->getData('request_path')) !== false) {
                $currentProductId = (int) $r->getProductId();
            }
        }
        if ($currentProductId) {
            $block = Mage::getModel('mpanel/layout')
                    ->getCollection()
                    ->addFieldToFilter('page_type', array('eq' => 'product'))
                    ->addFieldToFilter('indentifier', array('eq' => $currentProductId))
                    ->getFirstItem();
            if (!$block->getId()) {
                $block = Mage::getModel('mpanel/layout')
                        ->getCollection()
                        ->addFieldToFilter('page_type', array('eq' => 'product'))
                        ->addFieldToFilter('indentifier', array('eq' => 0))
                        ->getFirstItem();
            }
        } else {
            $block = Mage::getModel('mpanel/layout')
                    ->getCollection()
                    ->addFieldToFilter('page_type', array('eq' => 'category'))
                    ->addFieldToFilter('indentifier', array('eq' => $currentCategoryId))
                    ->getFirstItem();
            if (!$block->getId()) {
                $block = Mage::getModel('mpanel/layout')
                        ->getCollection()
                        ->addFieldToFilter('page_type', array('eq' => 'category'))
                        ->addFieldToFilter('indentifier', array('eq' => 0))
                        ->getFirstItem();
            }
        }
        return $block;
    }

    public function getBlockInCms() {
        if ($this->getRequest()->getParam('page_id')) {
            $pageId = $this->getRequest()->getParam('page_id');
        } else {
            $pageId = Mage::getBlockSingleton('cms/page')->getPage()->getId();
        }
        $block = Mage::getModel('mpanel/layout')
                ->getCollection()
                ->addFieldToFilter('page_type', array('eq' => 'cms'))
                ->addFieldToFilter('indentifier', array('eq' => $pageId))
                ->getFirstItem();
        if (!$block->getId()) {
            $block = Mage::getModel('mpanel/layout')
                    ->getCollection()
                    ->addFieldToFilter('page_type', array('eq' => 'cms'))
                    ->addFieldToFilter('indentifier', array('eq' => 0))
                    ->getFirstItem();
        }
        return $block;
    }
    
    public function getBlockInCustomer() {        
        $block = Mage::getModel('mpanel/layout')
                ->getCollection()
                ->addFieldToFilter('page_type', array('eq' => 'customer'))
                ->addFieldToFilter('indentifier', array('eq' => 0))
                ->getFirstItem();        
        return $block;
    }

    // Get block setting, return html attribute
    public function getBlockSetting($layout, $id, $class = NULL) {
        $storeId = Mage::app()->getStore()->getId();
        $block = Mage::getModel('mpanel/blocks')
                ->getCollection()
                ->addFieldToFilter('store_id', $storeId)
                ->addFieldToFilter('theme_name', $layout)
                ->addFieldToFilter('name', 'block' . $id)
                ->getFirstItem();
        $html = ' class="' . $class . ' ';
        if ($block->getId()) {
            if ($block->getClass() != '') {
                $html.= $block->getClass() . ' ';
            }

            if ($block->getParallax() & ($block->getBackgroundImage() != '')) {
                $html.= 'parallax';
            }

            $html.= '"';

            $html.= ' style="';

            if ($block->getBackground() != '') {
                $html.= 'background-color: ' . $block->getBackground() . ';';
            }

            if ($block->getBackgroundImage() != '') {
                $html.= 'background-image: url(\'' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'mpanel/background/' . $block->getBackgroundImage() . '\');';

                if (!$block->getParallax()) {
					if($block->getBackgroundRepeat()){
						$html.= 'background-repeat:repeat';
					}else{
						$html.= 'background-repeat:no-repeat';
					}
                }
            }



            if ($block->getPaddingTop() != '') {
                $html.= ' padding-top:' . $block->getPaddingTop() . 'px;';
            }

            if ($block->getPaddingBottom() != '') {
                $html.= ' padding-bottom:' . $block->getPaddingBottom() . 'px;';
            }
        }

        $html.= '"';

        if ($block->getId()) {
            if ($block->getParallax()) {
                $html.= ' data-stellar-vertical-offset="20" data-stellar-background-ratio="0.6"';
            }
        }


        return $html;
    }
    
    public function isActive($link)
    {
        if (empty($this->_activeLink)) {
            $this->_activeLink = $this->getAction()->getFullActionName('/');
        }
        if ($this->_completePath($link['path']) == $this->_activeLink) {
            return true;
        }
        return false;
    }

    protected function _completePath($path)
    {
        $path = rtrim($path, '/');
        switch (sizeof(explode('/', $path))) {
            case 1:
                $path .= '/index';
                // no break

            case 2:
                $path .= '/index';
        }
        return $path;
    }

}
