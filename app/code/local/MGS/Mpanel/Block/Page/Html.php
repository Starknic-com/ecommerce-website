<?php
class MGS_Mpanel_Block_Page_Html extends Mage_Page_Block_Html
{
	/**
     * Add CSS class to page body tag
     *
     * @param string $className
     * @return Mage_Page_Block_Html
     */
    public function addBodyClass($className)
    {
        $className = preg_replace('#[^a-z0-9]+#', '-', strtolower($className));
		
		$helper = Mage::helper('mpanel');
		$setting = $helper->getThemeSettings();
		
		if($helper->acceptToUsePanel()){
			$className .= ' page-builder';
        }
		
		if($setting['layout']=='boxed'){ 
			$className.=' boxed';
		}
		
		if($setting['layout_style']=='dark'){
			$className.=' dark';
		}
		
		$className.=' '.$setting['page_width'];
		
		$className = $this->getBodyClass() . ' ' . $className;
		$arrClass = explode(" ", $className);
		
		$arrClass = array_unique($arrClass);
		$className = implode(" ", $arrClass);
		
		$this->setBodyClass($className);
        return $this;
    }
}